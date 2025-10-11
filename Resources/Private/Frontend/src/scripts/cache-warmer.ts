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

import ImmediateAction from '@typo3/backend/action-button/immediate-action.js';
import Notification from '@typo3/backend/notification.js';

import {AjaxRequestHandler} from '@eliashaeussler/typo3-warming/request/handler/ajax-request-handler';
import {EventSourceRequestHandler} from '@eliashaeussler/typo3-warming/request/handler/event-source-request-handler';
import {LanguageKeys} from '@eliashaeussler/typo3-warming/enums/language-keys';
import {ReportModal} from '@eliashaeussler/typo3-warming/backend/modal/report-modal';
import {RequestHandler} from '@eliashaeussler/typo3-warming/request/handler/request-handler';
import {StringHelper} from '@eliashaeussler/typo3-warming/helper/string-helper';
import {WarmupProgress} from '@eliashaeussler/typo3-warming/request/warmup-progress';
import {WarmupState} from '@eliashaeussler/typo3-warming/enums/warmup-state';

/**
 * Action for use within notifications.
 */
export type NotificationAction = {
  label: string,
  action: typeof ImmediateAction,
}

/**
 * Request object for cache warmup of sites.
 */
export type SiteWarmupRequest = {[key: string]: (number|null)[]};

/**
 * Request object for cache warmup of pages.
 */
export type PageWarmupRequest = {[key: number]: (number|null)[]};

/**
 * Optional configuration for cache warmup.
 */
export type WarmingConfiguration = {
  limit?: number;
  strategy?: string;
};

type NotificationOpenEvent = CustomEvent<void> & {
  target: HTMLElement,
};

/**
 * Perform cache warmup from TYPO3 backend.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
export class CacheWarmer {
  private readonly handler: RequestHandler;

  constructor() {
    this.handler = this.initializeRequestHandler();
  }

  /**
   * Trigger cache warmup for given sites and pages.
   *
   * Starts cache warmup using the configured {@link RequestHandler}. Once
   * cache warmup is finished, a notification is shown. If it fails, an error
   * notification is shown instead.
   *
   * @param sites {SiteWarmupRequest} Collection of site <> language combinations to be warmed up
   * @param pages {PageWarmupRequest} Collection of page <> language combinations to be warmed up
   * @param configuration {WarmingConfiguration} Optional configuration used for cache warmup
   */
  public warmupCache(
    sites: SiteWarmupRequest,
    pages: PageWarmupRequest,
    configuration: WarmingConfiguration = {},
  ): Promise<WarmupProgress> {
    const queryParams: URLSearchParams = this.buildQueryParams(sites, pages, configuration);
    const retryFunction = () => this.warmupCache(sites, pages, configuration);

    return this.handler.startRequestWithQueryParams(queryParams, retryFunction)
      .then(
        // Success
        (progress: WarmupProgress): WarmupProgress => {
          let action: NotificationAction;

          // Add option to restart cache warmup if it has been aborted
          if (progress.state === WarmupState.Aborted) {
            action = {
              label: TYPO3.lang[LanguageKeys.notificationActionRetry],
              action: new ImmediateAction(retryFunction),
            };
          }

          CacheWarmer.showNotification(progress, retryFunction, action);

          return progress;
        },

        // Error
        (progress: WarmupProgress): WarmupProgress => {
          CacheWarmer.errorNotification();

          return progress;
        },
      );
  }

  /**
   * Create and return a supported request handler.
   *
   * @returns {RequestHandler} An instantiated request handler that supports the request type of this warmup request
   * @private
   */
  private initializeRequestHandler(): RequestHandler {
    if (EventSourceRequestHandler.isSupported()) {
      return new EventSourceRequestHandler();
    }

    return new AjaxRequestHandler();
  }

  /**
   * Return set of query params to be used fo cache warmup requests.
   *
   * @param sites {SiteWarmupRequest} Collection of site <> language combinations to be warmed up
   * @param pages {PageWarmupRequest} Collection of page <> language combinations to be warmed up
   * @param configuration {WarmingConfiguration} Optional configuration used for cache warmup
   * @returns {URLSearchParams} Set of query params to be used for cache warmup requests
   * @private
   */
  private buildQueryParams(
    sites: SiteWarmupRequest,
    pages: PageWarmupRequest,
    configuration: WarmingConfiguration = {},
  ): URLSearchParams {
    const queryParams: URLSearchParams = new URLSearchParams({
      requestId: StringHelper.generateUniqueId(),
    });

    let siteCount = 0;
    let pageCount = 0;

    for (const [site, languages] of Object.entries(sites)) {
      const index = siteCount++;

      queryParams.set(`sites[${index}][site]`, site);

      languages.forEach(
        (language: number, languageIndex: number) => queryParams.set(
          `sites[${index}][languageIds][${languageIndex}]`,
          (language ?? 0).toString(),
        ),
      );
    }

    for (const [page, languages] of Object.entries(pages)) {
      const index = pageCount++;

      queryParams.set(`pages[${index}][page]`, page.toString());

      languages.forEach(
        (language: number, languageIndex: number) => queryParams.set(
          `pages[${index}][languageIds][${languageIndex}]`,
          (language ?? 0).toString(),
        ),
      );
    }

    for (const [key, value] of Object.entries(configuration)) {
      queryParams.set(`configuration[${key}]`, value.toString());
    }

    return queryParams;
  }

  /**
   * Show notification for given cache warmup progress.
   *
   * @param progress {WarmupProgress} Progress of the cache warmup a notification is built for
   * @param retryFunction {() => Promise<WarmupProgress>} Function to retry cache warmup
   * @param additionalAction {NotificationAction|null} Additional action to be used for the generated notification
   * @private
   */
  private static showNotification(
    progress: WarmupProgress,
    retryFunction: () => Promise<WarmupProgress>,
    additionalAction?: NotificationAction,
  ): void {
    let {title, message} = progress.response;

    // Create action to open full report as modal
    const reportAction: NotificationAction = {
      label: TYPO3.lang[LanguageKeys.notificationShowReport],
      action: new ImmediateAction((): void => {
        ReportModal.createModal(progress, retryFunction);
      }),
    };

    // Define modal actions
    const actions: NotificationAction[] = [reportAction];
    if (additionalAction) {
      actions.push(additionalAction);
    }

    document.addEventListener('typo3-notification-open', CacheWarmer.manipulateNextNotification);

    // Show notification
    switch (progress.state) {
      case WarmupState.Failed:
        Notification.error(title, message, 0, actions);
        break;
      case WarmupState.Warning:
        Notification.warning(title, message, 0, actions);
        break;
      case WarmupState.Success:
        Notification.success(title, message, 15, actions);
        break;
      case WarmupState.Aborted:
        title = TYPO3.lang[LanguageKeys.notificationAbortedTitle];
        message = TYPO3.lang[LanguageKeys.notificationAbortedMessage];
        Notification.info(title, message, 15, actions);
        break;
      case WarmupState.Unknown:
        Notification.notice(title, message, 15);
        break;
      default:
        CacheWarmer.errorNotification();
        break;
    }
  }

  /**
   * Show error notification on erroneous cache warmup.
   *
   * @private
   */
  private static errorNotification(): void {
    Notification.error(
      TYPO3.lang[LanguageKeys.notificationErrorTitle],
      TYPO3.lang[LanguageKeys.notificationErrorMessage],
    );
  }

  /**
   * Manipulate a single notification by applying custom styles.
   *
   * @param event {NotificationOpenEvent} Event triggered once a notification message is rendered
   * @private
   */
  private static manipulateNextNotification(event: NotificationOpenEvent): void {
    // Apply custom styling to notification message
    event.target.classList.add('tx-warming-notification')

    // Make sure event listener is only triggered once
    document.removeEventListener('typo3-notification-open', CacheWarmer.manipulateNextNotification);
  }
}
