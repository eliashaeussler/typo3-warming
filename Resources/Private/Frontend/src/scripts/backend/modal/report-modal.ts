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

import $ from 'jquery';
import Icons from '@typo3/backend/icons.js';
import ImmediateAction from '@typo3/backend/action-button/immediate-action.js';
import Modal from '@typo3/backend/modal.js';

import {IconIdentifiers} from '@eliashaeussler/typo3-warming/enums/icon-identifiers';
import {LanguageKeys} from '@eliashaeussler/typo3-warming/enums/language-keys';
import {NotificationAction} from '@eliashaeussler/typo3-warming/cache-warmer';
import {WarmupProgress} from '@eliashaeussler/typo3-warming/request/warmup-progress';

/**
 * Modal with report about a finished cache warmup.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
export class ReportModal {
  private panelCount = 0;

  constructor(
    private readonly progress: WarmupProgress,
  ) {
  }

  /**
   * Create action for a new cache warmup report modal.
   *
   * @param progress {WarmupProgress} Progress of a cache warmup that is passed to the new modal
   * @param retryFunction {() => Promise<WarmupProgress>} Function to retry cache warmup
   * @returns {NotificationAction} An object representing the created modal action
   */
  public static createModalAction(
    progress: WarmupProgress,
    retryFunction: () => Promise<WarmupProgress>
  ): NotificationAction {
    return {
      label: TYPO3.lang[LanguageKeys.notificationShowReport],
      action: new ImmediateAction((): void => {
        ReportModal.createModal(progress, retryFunction);
      }),
    };
  }

  /**
   * Create modal with cache warmup report.
   *
   * Creates a new modal that contains information about a finished cache warmup
   * derived from the given warmup progress.
   *
   * @param progress {WarmupProgress} Progress of a finished cache warmup to be shown in the modal
   * @param retryFunction {() => Promise<WarmupProgress>} Function to retry cache warmup
   */
  public static createModal(
    progress: WarmupProgress,
    retryFunction: () => Promise<WarmupProgress>,
  ): ReportModal {
    const modal: ReportModal = new ReportModal(progress);

    Promise.all<string>([
      Icons.getIcon(IconIdentifiers.readonly, Icons.sizes.medium),
      Icons.getIcon(IconIdentifiers.approved, Icons.sizes.medium),
      Icons.getIcon(IconIdentifiers.warning, Icons.sizes.medium),
      Icons.getIcon(IconIdentifiers.viewPage, Icons.sizes.small),
      Icons.getIcon(IconIdentifiers.info, Icons.sizes.default),
    ])
      .then(([readonlyIcon, approvedIcon, warningIcon, viewPageIcon, infoIcon]): void => {
        // Ensure all other modals are closed
        Modal.dismiss();

        // Build content
        const $content: JQuery = modal.buildModalContent(readonlyIcon, approvedIcon, warningIcon, viewPageIcon, infoIcon);

        // Open modal with crawling report
        const buttons: {text: string, icon?: string, btnClass: string, trigger?: () => void}[] = [
          {
            text: TYPO3.lang[LanguageKeys.modalProgressButtonRetry],
            icon: IconIdentifiers.refresh,
            btnClass: 'btn-default',
            trigger: retryFunction,
          },
          {
            text: TYPO3.lang[LanguageKeys.modalProgressButtonClose],
            btnClass: 'btn-default',
            trigger: (): void => Modal.dismiss(),
          },
        ];

        // Get number of totally crawled pages
        if (progress.progress.current > 0) {
          buttons.unshift(
            {
              text: `${TYPO3.lang[LanguageKeys.modalReportTotal]} ${progress.progress.current}`,
              icon: IconIdentifiers.exclamationCircle,
              btnClass: 'disabled border-0',
            },
          );
        }

        Modal.advanced({
          title: TYPO3.lang[LanguageKeys.modalReportTitle],
          content: $content,
          size: Modal.sizes.large,
          buttons: buttons,
        });
      });

    return modal;
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
    // Create unique ID to toggle panel
    const collapseId = `tx-warming-panel-${this.panelCount++}`;

    return $('<div>')
      .addClass(`panel panel-${state}`)
      .append(
        $('<div>')
          .addClass('panel-heading')
          .append(
            $('<h3>')
              .addClass('panel-title')
              .append(
                $('<a>')
                  .addClass('collapsed')
                  .attr('href', `#${collapseId}`)
                  .attr('data-bs-toggle', 'collapse')
                  .attr('aria-controls', collapseId)
                  .attr('aria-expanded', 'false')
                  .append(
                    $('<span>').addClass('caret'),
                    $('<strong>').text(` ${title} (${urls.length})`),
                  )
              )
          ),
        $('<div>')
          .attr('id', collapseId)
          .addClass('panel-collapse collapse')
          .append(
            $('<div>')
              .addClass('table-fit')
              .append(
                $('<table>')
                  .addClass('table table-striped table-hover')
                  .append(
                    $('<tbody>').append(
                      urls.map((url: string): JQuery => {
                        return $('<tr>').append(
                          $('<td>').text(url),
                          $('<td>')
                            .addClass('col-control nowrap')
                            .append(
                              $('<div>')
                                .addClass('btn-group')
                                .append(
                                  $('<a>')
                                    .attr('href', url)
                                    .attr('target', '_blank')
                                    .addClass('btn btn-default btn-sm nowrap')
                                    .html(`${viewPageIcon} ${TYPO3.lang[LanguageKeys.modalReportActionView]}`)
                                )
                            )
                          );
                      }),
                    )
                  )
              )
          )
      );
  }

  private createSummaryCard(
    title: string,
    body: string,
    state: string,
    icon: string,
    current: number,
    total: number = null,
  ): JQuery {
    return $('<div>')
      .addClass('col-4')
      .append(
        $('<div>')
          .addClass(`card card-${state} h-100`)
          .append(
            $('<div>')
              .addClass('card-header')
              .append(
                $('<div>')
                  .addClass('card-icon')
                  .html(icon),
                $('<div>')
                  .addClass('card-header-body')
                  .append(
                    $('<h1>')
                      .addClass('card-title')
                      .text(title),
                    $('<span>')
                      .addClass('card-subtitle')
                      .html(total !== null ? `<strong>${current}</strong>/${total}` : current.toString())
                  )
              ),
            $('<div>')
              .addClass('card-body')
              .append(
                $('<p>')
                  .addClass('card-text')
                  .text(body)
              )
          )
      );
  }

  /**
   * Build content for modal with panels for failed and successful URLs.
   *
   * @param readonlyIcon {string} Rendered "readonly" icon
   * @param approvedIcon {string} Rendered "approved" icon
   * @param warningIcon {string} Rendered "warning" icon
   * @param viewPageIcon {string} Rendered "view page" icon
   * @param infoIcon {string} Rendered "info" icon
   * @returns {JQuery} The modal content as {@link JQuery} object
   * @private
   */
  private buildModalContent(
    readonlyIcon: string,
    approvedIcon: string,
    warningIcon: string,
    viewPageIcon: string,
    infoIcon: string,
  ): JQuery {
    // Reset count of panels in report
    this.panelCount = 0;

    // Count all excluded URLs and sitemaps
    const excluded: number = this.progress.getNumberOfExcludedSitemaps() + this.progress.getNumberOfExcludedUrls();

    // Initialize content container
    const $cardContainer: JQuery = $('<div>').addClass('card-container');
    const $content: JQuery = $('<div>');

    // Add text if no URLs were crawled
    if (this.progress.getTotalNumberOfCrawledUrls() === 0) {
      $content.append(
        $('<div>')
          .addClass('callout callout-info')
          .append(
            $('<div>')
              .addClass('media')
              .append(
                $('<div>')
                  .addClass('media-left')
                  .append(
                    $('<span>').addClass('icon-emphasized').html(infoIcon),
                  ),
                $('<div>').addClass('media-body').text(TYPO3.lang[LanguageKeys.modalReportNoUrlsCrawled]),
              ),
          ),
      );

      return $content;
    }

    $content.append($cardContainer);

    // Add summary cards
    if (this.progress.getNumberOfFailedUrls() > 0) {
      $cardContainer.append(
        this.createSummaryCard(
          TYPO3.lang[LanguageKeys.modalReportPanelFailed],
          TYPO3.lang[LanguageKeys.modalReportPanelFailedSummary],
          'danger',
          readonlyIcon,
          this.progress.getNumberOfFailedUrls(),
          this.progress.progress.current,
        ),
      );
    }
    if (this.progress.getNumberOfSuccessfulUrls() > 0) {
      $cardContainer.append(
        this.createSummaryCard(
          TYPO3.lang[LanguageKeys.modalReportPanelSuccessful],
          TYPO3.lang[LanguageKeys.modalReportPanelSuccessfulSummary],
          'success',
          approvedIcon,
          this.progress.getNumberOfSuccessfulUrls(),
          this.progress.progress.current,
        ),
      );
    }
    if (excluded > 0) {
      $cardContainer.append(
        this.createSummaryCard(
          TYPO3.lang[LanguageKeys.modalReportPanelExcluded],
          TYPO3.lang[LanguageKeys.modalReportPanelExcludedSummary],
          'warning',
          warningIcon,
          excluded,
        ),
      );
    }

    // Build panels from crawled URLs and the appropriate crawling states
    if (this.progress.getNumberOfFailedUrls() > 0) {
      $content.append(
        this.createPanel(
          TYPO3.lang[LanguageKeys.modalReportPanelFailed],
          'danger',
          this.progress.urls.failed,
          viewPageIcon,
        ),
      );
    }
    if (this.progress.getNumberOfSuccessfulUrls() > 0) {
      $content.append(
        this.createPanel(
          TYPO3.lang[LanguageKeys.modalReportPanelSuccessful],
          'success',
          this.progress.urls.successful,
          viewPageIcon,
        ),
      );
    }

    // Build panels from excluded sitemaps and URLs
    if (this.progress.getNumberOfExcludedSitemaps() > 0) {
      $content.append(
        this.createPanel(
          TYPO3.lang[LanguageKeys.modalReportPanelExcludedSitemaps],
          'warning',
          this.progress.excluded.sitemaps,
          viewPageIcon,
        ),
      );
    }
    if (this.progress.getNumberOfExcludedUrls() > 0) {
      $content.append(
        this.createPanel(
          TYPO3.lang[LanguageKeys.modalReportPanelExcludedUrls],
          'warning',
          this.progress.excluded.urls,
          viewPageIcon,
        ),
      );
    }

    return $content;
  }
}
