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
  'TYPO3/CMS/Backend/ActionButton/ImmediateAction',
  'TYPO3/CMS/Backend/Viewport',
], function ($, AjaxRequest, Icons, Notification, ImmediateAction, Viewport) {
  'use strict';

  const CacheWarmupMenu = {
    containerSelector: '#eliashaeussler-typo3warming-backend-toolbaritems-cachewarmuptoolbaritem',
    menuItemSelector: 'a.toolbar-cache-warmup-action',
    toolbarIconSelector: '.toolbar-item-icon .t3js-icon',
    userAgentCopySelector: 'button.toolbar-cache-warmup-useragent-copy-action',
    userAgentCopyIconSelector: '.t3js-icon',
    userAgentCopyTextSelector: '.toolbar-cache-warmup-useragent-copy-text',
    notificationDuration: 15,
    panelCount: 0,
  }, _ = CacheWarmupMenu;

  /**
   * Initialize toolbar menu events.
   *
   * Registers the events to warm up caches of sites or specific pages.
   */
  CacheWarmupMenu.initializeEvents = function () {
    $(_.containerSelector).on('click', _.menuItemSelector, function (event) {
      event.preventDefault();
      const pageId = $(event.currentTarget).attr('data-page-id');
      if (pageId) {
        _.warmupCache(pageId);
      }
    });

    $(_.containerSelector).on('click', _.userAgentCopySelector, function (event) {
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
   * Create panel in modal of cache warmup report.
   *
   * Creates a panel within the modal containing a specific cache warmup report.
   * The panel can be used for multiple crawling states, such as successful crawls
   * or failed crawls. The variable "state" defines a custom panel state and will
   * be prefixed with "panel-" to match a valid class name.
   *
   * @param title {string} Title of the panel
   * @param state {string} Custom panel state, will be prefixed with "panel-" to match a valid class name
   * @param urls {string[]} List of URLs to be printed as table within the panel
   * @param viewPageIcon {string} Resolved icon markup for the "view page" action
   * @returns {jQuery} Complete panel as jQuery object
   */
  CacheWarmupMenu.createPanel = function (title, state, urls, viewPageIcon) {
    _.panelCount++;

    return $('<div>')
      .addClass('panel panel-' + state + ' panel-table')
      .addClass(function () {
        if (_.panelCount > 1) {
          return 'panel-space';
        }
      })
      .append(
        // Add panel header
        $('<div>')
          .addClass('panel-heading')
          .text(title + ' (' + urls.length + ')'),
        // Add panel content
        $('<div>')
          .addClass('table-fit table-fit-wrap')
          .append(
            // Add table
            $('<table>')
              .addClass('table table-striped table-hover')
              .append(
                // Add table body
                $('<tbody>').append(
                  urls.map(function (url) {
                    // Add table row for each URL
                    return $('<tr>').append(
                      // Add URL as table cell
                      $('<td>').addClass('col-title').text(url),
                      // Add controls as table cell
                      $('<td>').addClass('col-control').append(
                        $('<a>')
                          .attr('href', url)
                          .attr('target', '_blank')
                          .addClass('btn btn-default btn-sm')
                          .html(viewPageIcon + ' ' + TYPO3.lang['cacheWarmup.modal.action.view'])
                      )
                    ); // End: table row
                  })
                ) // End: table body
              ) // End: table
          ) // End: panel content
      );
  };

  /**
   * Create modal action to show full report of cache warmup.
   *
   * @param failedUrls {string[]} List of failed URLs
   * @param successfulUrls {string[]} List of successfully crawled URLs
   * @returns {{action, label}} The combined modal action
   */
  CacheWarmupMenu.createModalAction = function (failedUrls, successfulUrls) {
    return {
      label: TYPO3.lang['cacheWarmup.notification.action.showReport'],
      action: new ImmediateAction(function () {
        require(['jquery', 'TYPO3/CMS/Backend/Modal', 'TYPO3/CMS/Backend/Icons'], function ($, Modal, Icons) {
          Icons.getIcon('actions-view-page', Icons.sizes.small).done(function (viewPageIcon) {
            // Reset count of panels in report
            _.panelCount = 0;

            // Create content container
            const $content = $('<div/>');

            // Build panels from crawled URLs and the appropriate crawling states
            if (failedUrls.length > 0) {
              $content.append(
                _.createPanel(
                  TYPO3.lang['cacheWarmup.modal.panel.failed'],
                  'danger',
                  failedUrls,
                  viewPageIcon
                )
              );
            }
            if (successfulUrls.length > 0) {
              $content.append(
                _.createPanel(
                  TYPO3.lang['cacheWarmup.modal.panel.successful'],
                  'success',
                  successfulUrls,
                  viewPageIcon
                )
              );
            }

            // Add number of totally crawled pages
            const total = successfulUrls.length + failedUrls.length;
            if (total > 0) {
              $content.append(
                $('<div>')
                  .addClass('typo3-message alert alert-info')
                  .append(
                    $('<div>')
                      .addClass('message-body')
                      .text(TYPO3.lang['cacheWarmup.modal.total'] + ' ' + total)
                  )
              );
            } else {
              $content.append(
                $('<div>')
                  .addClass('typo3-message alert alert-warning')
                  .append(
                    $('<div>')
                      .addClass('message-body')
                      .text(TYPO3.lang['cacheWarmup.modal.message.noUrlsCrawled'])
                  )
              );
            }

            // Open modal with crawling report
            Modal.advanced({
              title: TYPO3.lang['cacheWarmup.modal.title'],
              content: $content,
              size: Modal.sizes.large,
            });
          });
        });
      }),
    };
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
  CacheWarmupMenu.warmupCache = function (pageId, mode = 'site') {
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
          const modalAction = _.createModalAction(failedUrls, successfulUrls);

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

    // Show spinner during cache warmup
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
