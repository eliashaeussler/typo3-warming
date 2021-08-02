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

import 'select2';
import * as clipboard from 'clipboard-polyfill/text';

import IconIdentifiers from '../../../lib/Enums/IconIdentifiers';
import LanguageKeys from '../../../lib/Enums/LanguageKeys';
import WarmupProgress from '../../../lib/WarmupProgress';
import WarmupRequest from '../../../lib/WarmupRequest';
import WarmupRequestMode from '../../../lib/Enums/WarmupRequestMode';
import WarmupRequestType from '../../../lib/Enums/WarmupRequestType';
import WarmupState from '../../../lib/Enums/WarmupState';

// Modules
import $ from 'jquery';
import Icons from 'TYPO3/CMS/Backend/Icons';
import Notification from 'TYPO3/CMS/Backend/Notification';
import Viewport from 'TYPO3/CMS/Backend/Viewport';
import AjaxRequest from 'TYPO3/CMS/Core/Ajax/AjaxRequest';
import AjaxResponse from 'TYPO3/CMS/Core/Ajax/AjaxResponse';
import CacheWarmupProgressModal from '../Modal/CacheWarmupProgressModal';
import CacheWarmupReportModal from '../Modal/CacheWarmupReportModal';

/**
 * Selectors for several components within cache warmup menu.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
enum CacheWarmupMenuSelectors {
  container = '#eliashaeussler-typo3warming-backend-toolbaritems-cachewarmuptoolbaritem',
  dropdownTable = '.dropdown-table',
  menuItem = 'a.toolbar-cache-warmup-action',
  toolbarIcon = '.toolbar-item-icon .t3js-icon',
  languageSelect = '.tx-warming-language-select',
  languageSelectWrapper = '.tx-warming-language-select-wrapper',
  languageSelectDropdown = '.tx-warming-language-select-dropdown',
  useragentCopy = 'button.toolbar-cache-warmup-useragent-copy-action',
  useragentCopyIcon = '.t3js-icon',
  useragentCopyText = '.toolbar-cache-warmup-useragent-copy-text',
}

/**
 * AMD module that handles cache warmup from the Backend toolbar.
 *
 * Module: TYPO3/CMS/Warming/Backend/Toolbar/CacheWarmupMenu
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
export class CacheWarmupMenu {
  private notificationDuration = 15;

  constructor() {
    Viewport.Topbar.Toolbar.registerEvent((): void => this.fetchSites());

    // Copy user agent to clipboard in case the copy button is clicked
    $(CacheWarmupMenuSelectors.container).on('click', CacheWarmupMenuSelectors.useragentCopy, (event: JQuery.TriggeredEvent): void => {
      event.preventDefault();
      event.stopImmediatePropagation();

      const userAgent = $(event.currentTarget).attr('data-text');
      if (userAgent) {
        this.copyUserAgentToClipboard(userAgent);
      }
    });
  }

  /**
   * Trigger cache warmup for given page in given mode.
   *
   * Creates a new object of {@link WarmupRequest} for the given page id and warmup
   * request mode and starts cache warmup. In the meantime, the toolbar icon is
   * replaced by a spinner indicating that a cache warmup is in progress. Once the
   * cache warmup is finished, a notification is shown. If it fails, an error
   * notification is shown instead.
   *
   * @param pageId {number} Root page ID whose caches should be warmed up
   * @param mode {WarmupRequestMode} Requested warmup request mode
   * @param languageId {number|null} Optional language ID of a specific site language whose caches should be warmed up
   */
  public warmupCache(pageId: number, mode: WarmupRequestMode = WarmupRequestMode.Site, languageId: number | null = null): void {
    const $toolbarItemIcon = $(CacheWarmupMenuSelectors.toolbarIcon, CacheWarmupMenuSelectors.container);
    const $existingIcon = $toolbarItemIcon.clone();

    // Close dropdown menu
    $(CacheWarmupMenuSelectors.container).removeClass('open');

    // Show spinner during cache warmup
    Icons.getIcon(IconIdentifiers.spinner, Icons.sizes.small).then((spinner: string): void => {
      $toolbarItemIcon.replaceWith(spinner);
    });

    const request = new WarmupRequest(pageId, mode, languageId);
    request.runWarmup()
      .then(
        // Success
        (data: WarmupProgress): void => {
          this.showNotification(data);

          // Apply trigger function to "retry" button of progress modal
          if (WarmupRequestType.EventSource === request.requestType) {
            CacheWarmupProgressModal.getRetryButton()
              .removeClass('hidden')
              .off('button.clicked')
              .on('button.clicked', (): void => this.warmupCache(pageId, mode, languageId));
          }
        },
        // Error
        (): void => CacheWarmupMenu.errorNotification(),
      )
      .finally((): void => {
        $(CacheWarmupMenuSelectors.toolbarIcon, CacheWarmupMenuSelectors.container).replaceWith($existingIcon);
      });
  }

  /**
   * Fetch sites that are available for cache warmup.
   *
   * Creates an AJAX request to fetch all available sites that are ready for
   * cache warmup and replaces the table in the toolbar menu item with the
   * fetched content.
   *
   * @private
   */
  private fetchSites(): void {
    const $toolbarItemIcon = $(CacheWarmupMenuSelectors.toolbarIcon, CacheWarmupMenuSelectors.container);
    const $existingIcon = $toolbarItemIcon.clone();

    // Close dropdown menu
    $(CacheWarmupMenuSelectors.container).removeClass('open');

    // Show spinner during cache warmup
    Icons.getIcon(IconIdentifiers.spinner, Icons.sizes.small).then((spinner: string): void => {
      $toolbarItemIcon.replaceWith(spinner);
    });

    // Fetch rendered sites
    (new AjaxRequest(TYPO3.settings.ajaxUrls.tx_warming_fetch_sites))
      .get()
      .then(
        async (response: typeof AjaxResponse): Promise<void> => {
          // Replace placeholder with real data
          const data = await response.resolve();
          $(CacheWarmupMenuSelectors.dropdownTable, CacheWarmupMenuSelectors.container).html(data);

          // Initialize events for inserted DOM elements
          this.initializeEvents();
        }
      )
      .finally((): void => {
        $(CacheWarmupMenuSelectors.toolbarIcon, CacheWarmupMenuSelectors.container).replaceWith($existingIcon);
      });
  }

  /**
   * Initialize DOM events for several components in the cache warmup menu.
   *
   * @private
   */
  private initializeEvents(): void {
    // Trigger cache warmup in case a menu item is clicked
    $(CacheWarmupMenuSelectors.container).on('click', CacheWarmupMenuSelectors.menuItem, (event: JQuery.TriggeredEvent): void => {
      event.preventDefault();
      const pageId = $(event.currentTarget).attr('data-page-id');
      if (pageId) {
        this.warmupCache(Number(pageId));
      }
    });

    const $languageSelectWrapper = $(CacheWarmupMenuSelectors.languageSelectWrapper, CacheWarmupMenuSelectors.container);
    const $languageSelect = $(CacheWarmupMenuSelectors.languageSelect, $languageSelectWrapper);

    // Initialize select2 element for language selections
    $languageSelect.select2({
      placeholder: {
        // Use first select option with value="null" (disabled) as placeholder
        id: 'null',
        text: TYPO3.lang[LanguageKeys.toolbarSitemapPlaceholder],
      },
      // Disable search form
      minimumResultsForSearch: Infinity,
      width: '100%',
      dropdownCssClass: 'tx-warming-language-select-dropdown',
      dropdownAutoWidth: true,
      templateResult: (state: Select2.LoadingData): JQuery | null => {
        // Only valid language options are supported
        if (!state.id) {
          return null;
        }

        // Build option element from icon (flag) and text
        const $element = $(state.element as unknown as string);
        const $flag = $('<span class="tx-warming-language-select-option-flag">').append($element.data('icon'));
        const $content = $('<span class="tx-warming-language-select-option-text">').append(
          $('<strong>').text(state.text),
          $('<br>')
        );

        // Add sitemap URL or error message to option element
        if ($element.data('missing')) {
          $content.append(TYPO3.lang[LanguageKeys.toolbarSitemapMissing]);
        } else {
          $content.append($element.data('sitemap-url'));
        }

        // Create and return final option element
        return $('<span class="tx-warming-language-select-option">').append($flag, $content);
      }
    });

    // Prevent Bootstrap from closing toolbar item (= dropdown)
    // when language select menu is opened or interacted with
    $languageSelectWrapper.on('click', '.select2', (event: JQuery.TriggeredEvent): void => {
      event.preventDefault();
      event.stopImmediatePropagation();
    });
    $languageSelect.on('select2:open', (): void => {
      const $dropdown = $(CacheWarmupMenuSelectors.languageSelectDropdown);
      $dropdown.off('click', 'li');
      $dropdown.on('click', 'li', (event: JQuery.TriggeredEvent): void => {
        event.preventDefault();
        event.stopImmediatePropagation();
      });
    });

    // Trigger warmup for a concrete language
    $languageSelect.on('change', (event: JQuery.TriggeredEvent): void => {
      const $selectedOption = $(event.target).find(':selected');
      const pageId = $selectedOption.data('page-id');
      const languageId = $selectedOption.val();

      if (pageId && 'string' === typeof languageId) {
        // Trigger cache warmup for page and language
        this.warmupCache(Number(pageId), WarmupRequestMode.Site, Number(languageId));

        // Reset language selection to placeholder option
        $(event.target).val('null').trigger('change');
      }
    });
  }

  /**
   * Copy given User-Agent header to clipboard.
   *
   * @param userAgent {string} User-Agent header to be copied to clipboard
   * @private
   */
  private copyUserAgentToClipboard(userAgent: string): void {
    const $copyIcon = $(CacheWarmupMenuSelectors.useragentCopyIcon, CacheWarmupMenuSelectors.useragentCopy);
    const $existingIcon = $copyIcon.clone();

    // Show spinner when copying user agent
    Icons.getIcon(IconIdentifiers.spinner, Icons.sizes.small).then((spinner: string): void => {
      $copyIcon.replaceWith(spinner);
    });

    // Copy user agent to clipboard
    Promise.all([
      (navigator.clipboard ?? clipboard).writeText(userAgent),
      Icons.getIcon(IconIdentifiers.check, Icons.sizes.small),
    ])
      .then(
        async ([, icon]): Promise<void> => {
          const existingText = $(CacheWarmupMenuSelectors.useragentCopyText).text();
          $(CacheWarmupMenuSelectors.useragentCopyText).text(TYPO3.lang[LanguageKeys.toolbarCopySuccessful]);
          $(CacheWarmupMenuSelectors.useragentCopyIcon, CacheWarmupMenuSelectors.useragentCopy).replaceWith(icon);

          // Restore copy button after 3 seconds
          window.setTimeout((): void => {
            $(CacheWarmupMenuSelectors.useragentCopyIcon, CacheWarmupMenuSelectors.useragentCopy).replaceWith($existingIcon);
            $(CacheWarmupMenuSelectors.useragentCopyText).text(existingText);
            $(CacheWarmupMenuSelectors.useragentCopy).trigger('blur');
          }, 3000);
        },
        (): void => {
          $(CacheWarmupMenuSelectors.useragentCopyIcon, CacheWarmupMenuSelectors.useragentCopy).replaceWith($existingIcon);
        }
      );
  }

  /**
   * Show notification for given cache warmup progress.
   *
   * @param progress {WarmupProgress} Progress of the cache warmup a notification is built for
   * @private
   */
  private showNotification(progress: WarmupProgress): void {
    const {title, message} = progress.response;

    // Create action to open full report as modal
    const modalAction = CacheWarmupReportModal.createModalAction(progress);

    // Show notification
    switch (progress.state) {
      case WarmupState.Failed:
        Notification.error(title, message, this.notificationDuration, [modalAction]);
        break;
      case WarmupState.Warning:
        Notification.warning(title, message, this.notificationDuration, [modalAction]);
        break;
      case WarmupState.Success:
        Notification.success(title, message, this.notificationDuration, [modalAction]);
        break;
      case WarmupState.Unknown:
        Notification.notice(title, message, this.notificationDuration);
        break;
      default:
        CacheWarmupMenu.errorNotification();
        break;
    }
  }

  /**
   * Show error notification on erroneous cache warmup.
   *
   * @private
   */
  private static errorNotification(): void {
    Notification.error(TYPO3.lang[LanguageKeys.notificationErrorTitle], TYPO3.lang[LanguageKeys.notificationErrorMessage]);
  }
}

export default new CacheWarmupMenu();
