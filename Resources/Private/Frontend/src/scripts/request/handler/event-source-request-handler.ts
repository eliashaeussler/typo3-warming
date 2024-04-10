/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2024 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

import Modal from '@typo3/backend/modal.js';

import {ProgressModal} from '@eliashaeussler/typo3-warming/backend/modal/progress-modal';
import {RequestHandler} from '@eliashaeussler/typo3-warming/request/handler/request-handler';
import {WarmupProgress} from '@eliashaeussler/typo3-warming/request/warmup-progress';
import {WarmupState} from '@eliashaeussler/typo3-warming/enums/warmup-state';
import {UrlHelper} from '@eliashaeussler/typo3-warming/helper/url-helper';

/**
 * Cache warmup request handler using the {@link EventSource} API.
 *
 * This class represents a request handler for cache warmup requests, handled
 * by the {@link EventSource} API. It is the preferred way to handle such requests
 * since it is able to display the current request progress. To achieve this, a
 * new {@link ProgressModal} is created and the updated progress is
 * applied to the modal each time the server sends an event to the client.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
export class EventSourceRequestHandler implements RequestHandler {
  private progressModal!: ProgressModal;
  private source!: EventSource;
  private progress!: WarmupProgress;

  public startRequestWithQueryParams(
    queryParams: URLSearchParams,
    retryFunction: () => Promise<WarmupProgress>,
  ): Promise<WarmupProgress> {
    this.progress = new WarmupProgress(queryParams.get('requestId'));
    this.progressModal = ProgressModal.createModal(this.progress);
    this.source = new EventSource(this.getUrl(queryParams).toString(), {withCredentials: true});

    return new Promise<WarmupProgress>((resolve, reject): void => {
      // Abort cache warmup if progress modal is closed
      this.progressModal.getModal().addEventListener('typo3-modal-hide', (): void => {
        this.abortWarmup();
        resolve(this.progress);
      });

      this.source.addEventListener(
        'warmupProgress',
        (event: MessageEvent): void => this.updateProgress(event),
        false,
      );
      this.source.addEventListener(
        'warmupFinished',
        (event: MessageEvent): void => {
          this.finishWarmup(event as MessageEvent, retryFunction);

          resolve(this.progress);
        },
        false,
      );
      this.source.addEventListener('message', (): void => this.reject(reject), false);
      this.source.addEventListener('error', (): void => this.reject(reject), false);
    });
  }

  public getUrl(queryParams: URLSearchParams): URL {
    const url: URL = new URL(TYPO3.settings.ajaxUrls.tx_warming_cache_warmup, window.location.origin);

    return UrlHelper.mergeUrlWithQueryParams(url, queryParams);
  }

  /**
   * Test whether the client supports this handler.
   *
   * @returns {boolean} `true` if the client has support for the {@link EventSource} API, `false` otherwise
   */
  public static isSupported(): boolean {
    return !!window.EventSource;
  }

  /**
   * Close currently opened {@link EventSource}.
   *
   * @returns {boolean} `true` whether the source was closed successfully, `false` otherwise
   * @private
   */
  private closeSource(): boolean {
    this.source.close();

    return EventSource.CLOSED === this.source.readyState;
  }

  /**
   * Update current warmup progress by parsing data of given event.
   *
   * @param event {MessageEvent} The server-sent event containing updated warmup progress
   * @private
   */
  private updateProgress(event: MessageEvent): void {
    const data = JSON.parse(event.data);
    this.progress.update(data);

    // Pass updated progress to progress modal
    this.progressModal.progress = this.progress;
  }

  /**
   * Finish warmup request and update progress modal with warmup result data.
   *
   * @param event {MessageEvent} The server-sent event containing result of finished warmup
   * @param retryFunction {() => Promise<WarmupProgress>} Function to retry cache warmup
   * @private
   */
  private finishWarmup(event: MessageEvent, retryFunction: () => Promise<WarmupProgress>): void {
    const data = JSON.parse(event.data);
    this.progress.update(data);

    // Ensure event source is closed to stop future retries
    this.closeSource();

    // Pass updated progress to progress modal
    this.progressModal.progress = this.progress;

    // Finish progress within modal
    this.progressModal.finishProgress(this.progress, retryFunction);
  }

  /**
   * Abort current cache warmup request.
   *
   * @private
   */
  private abortWarmup(): void {
    this.closeSource();
    this.progress.update({
      state: WarmupState.Aborted,
    });
  }

  /**
   * Interrupt current warmup request and reject Promise.
   *
   * @param reject {function(): void} Function to reject the Promise, will be called automatically
   * @private
   */
  private reject(reject: () => void): void {
    this.closeSource();
    Modal.dismiss();

    reject();
  }
}
