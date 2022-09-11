'use strict'

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

import RequestHandlerInterface from './RequestHandlerInterface';
import Util from '../Util';
import WarmupProgress from '../WarmupProgress';
import WarmupState from "../Enums/WarmupState";

// Modules
import CacheWarmupProgressModal from '../../modules/Backend/Modal/CacheWarmupProgressModal';
import CacheWarmupReportModal from '../../modules/Backend/Modal/CacheWarmupReportModal';

/**
 * Cache warmup request handler using the {@link EventSource} API.
 *
 * This class represents an request handler for cache warmup requests, handled
 * by the {@link EventSource} API. It is the preferred way to handle such requests
 * since it is able to display the current request progress. To achieve this, a
 * new {@link CacheWarmupProgressModal} is created and the updated progress is
 * applied to the modal each time the server sends an event to the client.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
export default class EventSourceRequestHandler implements RequestHandlerInterface {
  private source!: EventSource;
  private progress!: WarmupProgress;

  public startRequestWithQueryParams(queryParams: URLSearchParams): Promise<WarmupProgress> {
    CacheWarmupProgressModal.createModal();

    this.source = new EventSource(this.getUrl(queryParams).toString(), {withCredentials: true});
    this.progress = new WarmupProgress();

    return new Promise<WarmupProgress>((resolve, reject): void => {
      // Abort cache warmup of progress modal is closed
      CacheWarmupProgressModal.getModal().on('hide.bs.modal', (): void => {
        this.abortWarmup();
        resolve(this.progress);
      });

      this.source.addEventListener(
        'warmupProgress',
        (event): void => this.updateProgress(event as MessageEvent),
        false
      );
      this.source.addEventListener(
        'warmupFinished',
        (event): void => {
          CacheWarmupProgressModal.getModal().find('.modal-footer').show();
          this.finishWarmup(event as MessageEvent);
          resolve(this.progress);
        },
        false
      );
      this.source.addEventListener('message', (): void => this.reject(reject), false);
      this.source.addEventListener('error', (): void => this.reject(reject), false);
    });
  }

  public getUrl(queryParams: URLSearchParams): URL {
    const url = new URL(TYPO3.settings.ajaxUrls.tx_warming_cache_warmup, window.location.origin);

    return Util.mergeUrlWithQueryParams(url, queryParams);
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

    // Pass update upgraded progress to progress modal
    CacheWarmupProgressModal.updateProgress(this.progress);
  }

  /**
   * Finish warmup request and update progress modal with warmup result data.
   *
   * @param event {MessageEvent} The server-sent event containing result of finished warmup
   * @private
   */
  private finishWarmup(event: MessageEvent): void {
    const data = JSON.parse(event.data);
    this.progress.update(data);

    // Ensure event source is closed to stop future retries
    this.closeSource();

    // Build report modal on click on "open report" button
    CacheWarmupProgressModal.getReportButton()
      .removeClass('hidden')
      .off('button.clicked')
      .on('button.clicked', (): void => CacheWarmupReportModal.createModal(this.progress));
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
    CacheWarmupProgressModal.dismiss();
    reject();
  }
}
