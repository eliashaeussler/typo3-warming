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

import IconIdentifiers from '../../../lib/Enums/IconIdentifiers';
import WarmupProgress from '../../../lib/WarmupProgress';

// Modules
import $ from 'jquery';
import ImmediateAction from 'TYPO3/CMS/Backend/ActionButton/ImmediateAction';
import Icons from 'TYPO3/CMS/Backend/Icons';
import Modal from 'TYPO3/CMS/Backend/Modal';


/**
 * AMD module that shows a modal with report about a finished cache warmup.
 *
 * Module: TYPO3/CMS/Warming/Backend/Modal/CacheWarmupReportModal
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class CacheWarmupReportModal {
  private progress!: WarmupProgress;
  private panelCount = 0;

  /**
   * Create action for a new cache warmup report modal.
   *
   * @param progress {WarmupProgress} Progress of a cache warmup that is passed to the new modal
   * @returns {object} An object representing the created modal action
   */
  public createModalAction(progress: WarmupProgress): { label: string, action: typeof ImmediateAction } {
    return {
      label: TYPO3.lang['cacheWarmup.notification.action.showReport'],
      action: new ImmediateAction((): void => this.createModal(progress)),
    };
  }

  /**
   * Create modal with cache warmup report.
   *
   * Creates a new modal that contains information about a finished cache warmup
   * derived from the given warmup progress.
   *
   * @param progress {WarmupProgress} Progress of a finished cache warmup to be shown in the modal
   */
  public createModal(progress: WarmupProgress): void {
    this.progress = progress;

    Promise.all<string, string>([
      Icons.getIcon(IconIdentifiers.viewPage, Icons.sizes.small),
      Icons.getIcon(IconIdentifiers.info, Icons.sizes.small),
    ])
      .then(([viewPageIcon, infoIcon]): void => {
        // Ensure all other modals are closed
        Modal.dismiss();

        // Reset count of panels in report
        this.panelCount = 0;

        // Build content
        const $content = this.buildModalContent(viewPageIcon, infoIcon);

        // Open modal with crawling report
        Modal.advanced({
          title: TYPO3.lang['cacheWarmup.modal.title'],
          content: $content,
          size: Modal.sizes.large,
        });
      });
  }

  /**
   * Create new panel for given URLs.
   *
   * Returns a panel that is integrated in the modal content. Each panel contains
   * a title with several URLs. The given state is used as class name.
   *
   * @param title {string} Panel title
   * @param state {string} Panel state, is applied as class name
   * @param urls {string[]} Set of URLs to be listed in the panel
   * @param viewPageIcon {string} Rendered "view page" icon that is appended to the panel
   * @returns {JQuery} A {@link JQuery} object with the created panel
   * @private
   */
  private createPanel(title: string, state: string, urls: string[], viewPageIcon: string): JQuery {
    this.panelCount++;

    return $('<div>')
      .addClass(`panel panel-${state} panel-table`)
      .addClass((): string => this.panelCount > 1 ? 'panel-space' : '')
      .append(
        // Add panel header
        $('<div>')
          .addClass('panel-heading')
          .text(`${title} (${urls.length})`),
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
                  urls.map((url): JQuery => {
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
                          .html(`${viewPageIcon} ${TYPO3.lang['cacheWarmup.modal.action.view']}`)
                      )
                    ); // End: table row
                  })
                ) // End: table body
              ) // End: table
          ) // End: panel content
      );
  }

  /**
   * Build content for modal with panels for failed and successful URLs.
   *
   * @param viewPageIcon {string} Rendered "view page" icon
   * @param infoIcon {string} Rendered "info" icon
   * @returns {JQuery} The modal content as {@link JQuery} object
   * @private
   */
  private buildModalContent(viewPageIcon: string, infoIcon: string): JQuery {
    const $content = $('<div>');

    // Build panels from crawled URLs and the appropriate crawling states
    if (this.progress.getNumberOfFailedUrls() > 0) {
      $content.append(
        this.createPanel(
          TYPO3.lang['cacheWarmup.modal.panel.failed'],
          'danger',
          this.progress.urls.failed,
          viewPageIcon
        )
      );
    }
    if (this.progress.getNumberOfSuccessfulUrls() > 0) {
      $content.append(
        this.createPanel(
          TYPO3.lang['cacheWarmup.modal.panel.successful'],
          'success',
          this.progress.urls.successful,
          viewPageIcon
        )
      );
    }

    // Add number of totally crawled pages
    const totalText = this.progress.progress.total > 0
      ? `${TYPO3.lang['cacheWarmup.modal.total']} ${this.progress.progress.total}`
      : TYPO3.lang['cacheWarmup.modal.message.noUrlsCrawled'];
    $content.append(
      $('<div>').addClass('badge badge-info').html(`${infoIcon} ${totalText}`)
    );

    return $content;
  }
}

export default new CacheWarmupReportModal();
