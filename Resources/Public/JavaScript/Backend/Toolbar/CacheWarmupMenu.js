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

define([
  'jquery',
  'TYPO3/CMS/Core/Ajax/AjaxRequest',
  'TYPO3/CMS/Backend/Icons',
  'TYPO3/CMS/Backend/Notification',
  'TYPO3/CMS/Backend/Viewport',
  'TYPO3/CMS/Warming/Backend/CacheWarmupReportModal',
], function ($, AjaxRequest, Icons, Notification, Viewport, CacheWarmupReportModal) {
  'use strict';

  const CacheWarmupMenu = {
    containerSelector: '#eliashaeussler-typo3warming-backend-toolbaritems-cachewarmuptoolbaritem',
    dropdownTableSelector: '.dropdown-table',
    menuItemSelector: 'a.toolbar-cache-warmup-action',
    toolbarIconSelector: '.toolbar-item-icon .t3js-icon',
    userAgentCopySelector: 'button.toolbar-cache-warmup-useragent-copy-action',
    userAgentCopyIconSelector: '.t3js-icon',
    userAgentCopyTextSelector: '.toolbar-cache-warmup-useragent-copy-text',
    notificationDuration: 15,
  }, _ = CacheWarmupMenu;

  /**
   * Cache warmup modes, can be either "page" or "site".
   *
   * @type {{site: string, page: string}}
   */
  CacheWarmupMenu.modes = {
    page: 'page',
    site: 'site',
  };

  /**
   * Initialize toolbar menu events.
   *
   * Registers the events to warm up caches of sites or specific pages.
   */
  CacheWarmupMenu.initializeEvents = function () {
    $(_.containerSelector).ready(_.fetchSites);

    $(_.containerSelector).on('click', _.menuItemSelector, function (event) {
      event.preventDefault();
      const pageId = $(event.currentTarget).attr('data-page-id');
      if (pageId) {
        _.warmupCache(pageId);
      }
    });

    $(_.containerSelector).on('click', _.userAgentCopySelector, function (event) {
      event.preventDefault();
      const userAgent = $(event.currentTarget).attr('data-text');
      if (userAgent) {
        _.copyUserAgentToClipboard(userAgent);
      }
    });
  };

  /**
   * Show error notification on failed cache warmup.
   */
  CacheWarmupMenu.errorNotification = function () {
    Notification.error(
      TYPO3.lang['cacheWarmup.notification.error.title'],
      TYPO3.lang['cacheWarmup.notification.error.message']
    );
  };

  /**
   * Asynchronously fetch sites available for cache warmup.
   */
  CacheWarmupMenu.fetchSites = function () {
    const $toolbarItemIcon = $(_.toolbarIconSelector, _.containerSelector);
    const $existingIcon = $toolbarItemIcon.clone();

    // Close dropdown menu
    $(_.containerSelector).removeClass('open');

    // Show spinner during cache warmup
    Icons.getIcon('spinner-circle-light', Icons.sizes.small).then(function (spinner) {
      $toolbarItemIcon.replaceWith(spinner);
    });

    // Fetch rendered sites
    (new AjaxRequest(TYPO3.settings.ajaxUrls.tx_warming_fetch_sites))
      .get()
      .then(
        async function (response) {
          const data = await response.resolve();
          const $table = $(_.dropdownTableSelector, _.containerSelector);

          $table.html(data);
        }
      )
      .finally(function () {
        $(_.toolbarIconSelector, _.containerSelector).replaceWith($existingIcon);
      });
  };

  /**
   * Trigger cache warmup for given page in given mode.
   *
   * Triggers the cache warmup for a specific page or site using an AJAX request. Once the
   * request is fulfilled, a notification will be shown displaying the crawling state. The
   * notification contains an action which allows to show the full report containing all
   * crawled URLs.
   *
   * @param pageId {int} Page ID of the page or site whose caches should be warmed up
   * @param mode {string} Warmup mode, can be one of "page", "site" (default)
   */
  CacheWarmupMenu.warmupCache = function (pageId, mode = CacheWarmupMenu.modes.site) {
    const $toolbarItemIcon = $(_.toolbarIconSelector, _.containerSelector);
    const $existingIcon = $toolbarItemIcon.clone();

    // Close dropdown menu
    $(_.containerSelector).removeClass('open');

    // Show spinner during cache warmup
    Icons.getIcon('spinner-circle-light', Icons.sizes.small).then(function (spinner) {
      $toolbarItemIcon.replaceWith(spinner);
    });

    // Trigger cache warmup
    (new AjaxRequest(TYPO3.settings.ajaxUrls.tx_warming_cache_warmup))
      .withQueryArguments({pageId: pageId, mode: mode})
      .post({})
      .then(
        async function (response) {
          const data = await response.resolve();
          const failedUrls = data.urls.failed;
          const successfulUrls = data.urls.successful;

          // Create action to open full report as modal
          const modalAction = CacheWarmupReportModal.createModalAction(failedUrls, successfulUrls);

          // Show notification
          switch (data.state) {
            case 'failed':
              Notification.error(data.title, data.message, _.notificationDuration, [modalAction]);
              break;
            case 'warning':
              Notification.warning(data.title, data.message, _.notificationDuration, [modalAction]);
              break;
            case 'success':
              Notification.success(data.title, data.message, _.notificationDuration, [modalAction]);
              break;
            case 'unknown':
              Notification.notice(data.title, data.message, _.notificationDuration);
              break;
            default:
              _.errorNotification();
              break;
          }
        },
        function () {
          _.errorNotification();
        }
      )
      .finally(function () {
        $(_.toolbarIconSelector, _.containerSelector).replaceWith($existingIcon);
      });
  };

  /**
   * Copy user-agent header to clipboard.
   *
   * @param userAgent {string} The user agent to be copied to clipboard
   */
  CacheWarmupMenu.copyUserAgentToClipboard = function (userAgent) {
    const $copyIcon = $(_.userAgentCopyIconSelector, _.userAgentCopySelector);
    const $existingIcon = $copyIcon.clone();

    // Show spinner when copying user agent
    Icons.getIcon('spinner-circle-light', Icons.sizes.small).then(function (spinner) {
      $copyIcon.replaceWith(spinner);
    });

    // Copy user agent to clipboard
    Promise.all([
      navigator.clipboard.writeText(userAgent),
      Icons.getIcon('actions-check', Icons.sizes.small),
    ])
      .then(
        async function (responses) {
          $(_.userAgentCopyTextSelector).text(TYPO3.lang['cacheWarmup.toolbar.copy.successful']);
          $(_.userAgentCopyIconSelector, _.userAgentCopySelector).replaceWith(responses[1]);
        },
        function () {
          $(_.userAgentCopyIconSelector, _.userAgentCopySelector).replaceWith($existingIcon);
        }
      );
  };

  // Register events to trigger cache warmup for available sites
  Viewport.Topbar.Toolbar.registerEvent(_.initializeEvents);

  return CacheWarmupMenu;
});
