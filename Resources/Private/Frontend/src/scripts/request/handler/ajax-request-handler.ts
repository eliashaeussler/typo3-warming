/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2025 Elias Häußler <elias@haeussler.dev>
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

import AjaxRequest from '@typo3/core/ajax/ajax-request.js';
import type AjaxResponse from '@typo3/core/ajax/ajax-response.js';
import Modal from '@typo3/backend/modal.js';

import {ProgressModal} from '@eliashaeussler/typo3-warming/backend/modal/progress-modal';
import {RequestHandler} from '@eliashaeussler/typo3-warming/request/handler/request-handler';
import {UrlHelper} from '@eliashaeussler/typo3-warming/helper/url-helper';
import {WarmupProgress, WarmupProgressDataObject} from '@eliashaeussler/typo3-warming/request/warmup-progress';
import {WarmupState} from "@eliashaeussler/typo3-warming/enums/warmup-state";

/**
 * Cache warmup request handler using AJAX requests.
 *
 * This class represents a request handler for cache warmup requests, handled
 * by an AJAX request. It should only be used if the preferred request handler,
 * {@link EventSourceRequestHandler}, is not available since it is not able to
 * display the current progress of a concrete warmup request.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
export class AjaxRequestHandler implements RequestHandler {
  private progressModal!: ProgressModal;
  private request!: typeof AjaxRequest;
  private progress!: WarmupProgress;

  public startRequestWithQueryParams(
    queryParams: URLSearchParams,
    retryFunction: () => Promise<WarmupProgress>,
  ): Promise<WarmupProgress>  {
    this.progress = new WarmupProgress(queryParams.get('requestId'));
    this.progressModal = ProgressModal.createModal(this.progress);
    this.request = new AjaxRequest(this.getUrl(queryParams).toString());

    // Abort cache warmup if progress modal is closed
    this.progressModal.getModal().addEventListener('typo3-modal-hide', (): void => {
      this.abortWarmup();
    });

    return this.request
      .post({})
      .then(
        async (response: typeof AjaxResponse): Promise<WarmupProgress> => {
          const data = await response.resolve();

          this.finishWarmup(data, retryFunction);

          return this.progress;
        },
      )
      .catch(
        async (response: typeof AjaxResponse): Promise<WarmupProgress> => {
          this.reject();

          await response.resolve();

          return this.progress;
        },
      );
  }

  public getUrl(queryParams: URLSearchParams): URL {
    const url: URL = new URL(TYPO3.settings.ajaxUrls.tx_warming_cache_warmup_legacy, window.location.origin);

    return UrlHelper.mergeUrlWithQueryParams(url, queryParams);
  }

  /**
   * Cancel current ajax request.
   *
   * @private
   */
  private cancelRequest(): void {
    this.request.abort();
  }

  /**
   * Finish warmup request and update progress modal with warmup result data.
   *
   * @param data {WarmupProgressDataObject} Result data of finished warmup
   * @param retryFunction {() => Promise<WarmupProgress>} Function to retry cache warmup
   * @private
   */
  private finishWarmup(data: WarmupProgressDataObject, retryFunction: () => Promise<WarmupProgress>): void {
    this.progress.update(data);
    this.progressModal.progress = this.progress;

    // Cancel request if not already done
    this.cancelRequest();

    // Finish progress within modal
    this.progressModal.finishProgress(this.progress, retryFunction);
  }

  /**
   * Abort current cache warmup request.
   *
   * @private
   */
  private abortWarmup(): void {
    this.cancelRequest();
    this.progress.update({
      state: WarmupState.Aborted,
    });
  }

  /**
   * Interrupt current warmup request.
   *
   * @private
   */
  private reject(): void {
    this.cancelRequest();
    Modal.dismiss();
  }
}
