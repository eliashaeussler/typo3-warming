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
  'TYPO3/CMS/Backend/Modal',
  'TYPO3/CMS/Backend/Icons',
  'TYPO3/CMS/Backend/ActionButton/ImmediateAction',
], function ($, Modal, Icons, ImmediateAction) {
  'use strict';

  const CacheWarmupReportModal = {
    panelCount: 0,
  }, _ = CacheWarmupReportModal;

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
  CacheWarmupReportModal.createPanel = function (title, state, urls, viewPageIcon) {
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
  CacheWarmupReportModal.createModalAction = function (failedUrls, successfulUrls) {
    return {
      label: TYPO3.lang['cacheWarmup.notification.action.showReport'],
      action: new ImmediateAction(function () {
        _.createModal(failedUrls, successfulUrls);
      }),
    };
  };

  /**
   * Create modal with cache warmup report.
   *
   * Creates an advanced modal containing the results from a previous cache warmup.
   * The modal shows all crawled URLs, grouped by state (successful or failing).
   *
   * @param failedUrls {string[]} List of failed URLs
   * @param successfulUrls {string[]} List of successfully crawled URLs
   */
  CacheWarmupReportModal.createModal = function (failedUrls, successfulUrls) {
    Promise.all([
      Icons.getIcon('actions-view-page', Icons.sizes.small),
      Icons.getIcon('content-info', Icons.sizes.small),
    ])
      .done(function (icons) {
        const [viewPageIcon, infoIcon] = icons;

        // Reset count of panels in report
        _.panelCount = 0;

        // Create content container
        const $content = $('<div>');

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
        const totalText = total > 0
          ? TYPO3.lang['cacheWarmup.modal.total'] + ' ' + total
          : TYPO3.lang['cacheWarmup.modal.message.noUrlsCrawled'];
        $content.append(
          $('<div>')
            .addClass('badge badge-info')
            .html(infoIcon + ' ' + totalText)
        );

        // Open modal with crawling report
        Modal.advanced({
          title: TYPO3.lang['cacheWarmup.modal.title'],
          content: $content,
          size: Modal.sizes.large,
        });
      });
  };

  return CacheWarmupReportModal;
});
