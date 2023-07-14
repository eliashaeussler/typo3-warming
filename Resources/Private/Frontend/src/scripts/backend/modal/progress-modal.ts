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
import Modal from '@typo3/backend/modal.js';

import {CrawlingProgress, WarmupProgress} from '@eliashaeussler/typo3-warming/request/warmup-progress';
import {IconIdentifiers} from '@eliashaeussler/typo3-warming/enums/icon-identifiers';
import {LanguageKeys} from '@eliashaeussler/typo3-warming/enums/language-keys';
import {ReportModal} from '@eliashaeussler/typo3-warming/backend/modal/report-modal';
import {StringHelper} from '@eliashaeussler/typo3-warming/helper/string-helper';
import {WarmupState} from '@eliashaeussler/typo3-warming/enums/warmup-state';

enum CacheWarmupProgressModalButtonNames {
  reportButton = 'tx-warming-open-report',
  retryButton = 'tx-warming-retry',
}

/**
 * Modal with a progress bar, displaying the current cache warmup progress.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
export class ProgressModal {
  private $modal!: JQuery;
  private $progressBar!: JQuery;
  private $placeholder!: JQuery;
  private $allCounter!: JQuery;
  private $failedCounter!: JQuery;
  private $currentUrl!: JQuery;

  /**
   * Create modal with progress bar.
   *
   * Re-initializes the current modal or creates a new modal and applies initial
   * content. The modal contains a progress bar as well as counters of currently
   * processed pages, total number of pages to be crawled as well as number of
   * failed crawls.
   *
   * Next to the modal content, a footer with several buttons is added. The footer
   * is hidden as long as cache warmup is in progress since it contains several
   * actions that require cache warmup to be finished.
   */
  public static createModal(): ProgressModal {
    const modal: ProgressModal = new this();
    const $content: JQuery = modal.buildInitialModalContent();

    // Ensure all other modals are closed
    Modal.dismiss();

    // Create new modal
    modal.$modal = modal.createModalWithContent($content);

    // Hide footer until cache warmup is finished
    modal.$modal.find('.modal-footer').hide();

    return modal;
  }

  /**
   * Update progress bar and counters in modal content.
   *
   * Reads data from the given {@link WarmupProgress} object and applies its data
   * to the single components within the modal content (progress bar and counters).
   *
   * @param progress {WarmupProgress} An object holding data about the progress of the current cache warmup
   */
  public updateProgress(progress: WarmupProgress): void {
    const currentUrl: string = progress.getCurrentUrl();
    const percent: number = progress.getProgressInPercent();
    const failedCount: number = progress.getNumberOfFailedUrls();
    const {current, total}: CrawlingProgress = progress.progress;

    this.$progressBar.addClass('progress-bar-animated active');
    this.$progressBar.attr('aria-valuenow', current);
    this.$progressBar.attr('aria-valuemax', total);
    this.$progressBar.css('width', `${percent}%`);
    this.$progressBar.html(`${percent.toFixed(2)}%`);

    // Remove placeholder
    if (this.$placeholder.length > 0) {
      this.$placeholder.remove();
    }

    if (failedCount > 0) {
      this.$progressBar.addClass('progress-bar-warning bg-warning');
      this.$failedCounter.show().html(
        StringHelper.formatString(TYPO3.lang[LanguageKeys.modalProgressFailedCounter], failedCount.toString()),
      );
    }

    this.$allCounter.html(
      StringHelper.formatString(TYPO3.lang[LanguageKeys.modalProgressAllCounter], current.toString(), total.toString()),
    );

    if (currentUrl !== '') {
      this.$currentUrl.html(currentUrl);
    }

    if (progress.isFinished()) {
      this.$progressBar
        .removeClass('progress-bar-animated active')
        .removeClass('progress-bar-warning bg-warning')
        .addClass(failedCount > 0 ? 'progress-bar-danger bg-danger' : 'progress-bar-success bg-success')
      ;
      this.$currentUrl.remove();
    }
  }

  public finishProgress(progress: WarmupProgress, retryFunction: () => Promise<WarmupProgress>): void {
    // Build report modal on click on "open report" button
    this.getReportButton()
      .removeClass('hidden')
      .off('click')
      .on('click', (): void => {
        ReportModal.createModal(progress, retryFunction)
      })
    ;

    // Apply trigger function to "retry" button of progress modal
    if (progress.state !== WarmupState.Aborted) {
      this.getRetryButton()
        .removeClass('hidden')
        .off('click')
        .on('click', retryFunction)
      ;
    }
  }

  /**
   * Get current modal as {@link JQuery} object.
   *
   * @returns {JQuery} Current modal as {@link JQuery} object
   */
  public getModal(): JQuery {
    return this.$modal;
  }

  /**
   * Get report button within current modal as {@link JQuery} object.
   *
   * @returns {JQuery} Report button within current modal as {@link JQuery} object
   */
  public getReportButton(): JQuery {
    return this.$modal.find(`button[name=${CacheWarmupProgressModalButtonNames.reportButton}]`);
  }

  /**
   * Get retry button within current modal as {@link JQuery} object.
   *
   * @returns {JQuery} Retry button within current modal as {@link JQuery} object
   */
  public getRetryButton(): JQuery {
    return this.$modal.find(`button[name=${CacheWarmupProgressModalButtonNames.retryButton}]`);
  }

  /**
   * Build initial modal content and return its wrapper.
   *
   * Creates a progress bar and several counters and wraps them in a <div>
   * container. The container is then returned as {@link JQuery} object.
   *
   * @returns {JQuery} Modal content as {@link JQuery} object
   * @private
   */
  private buildInitialModalContent(): JQuery {
    const $content: JQuery = $('<div class="tx-warming-progress-modal">');

    this.$progressBar = $('<div class="progress-bar progress-bar-striped progress-bar-animated active">')
      .attr('role', 'progressbar')
      .attr('aria-valuemin', 0)
      .attr('aria-valuemax', 0)
      .attr('aria-valuenow', 0)
    ;
    this.$placeholder = $('<div class="tx-warming-progress-placeholder">').html(TYPO3.lang[LanguageKeys.modalProgressPlaceholder]);
    this.$allCounter = $('<div>');
    this.$failedCounter = $('<div class="badge badge-danger">');
    this.$currentUrl = $('<div class="tx-warming-progress-modal-current-url">');

    // Hide failed counter until any URL fails to be warmed up
    this.$failedCounter.hide();

    // Append progress bar, counter and current url
    $content
      .append($('<div class="tx-warming-progress-modal-progress progress">').append(this.$progressBar))
      .append($('<div class="tx-warming-progress-modal-counter">').append(this.$placeholder, this.$allCounter, this.$failedCounter))
      .append(this.$currentUrl)
    ;

    return $content;
  }

  /**
   * Create new modal and apply given content to the modal body.
   *
   * @param $content {JQuery} Content to be applied to the modal body
   * @returns {JQuery} The new modal as {@link JQuery} object
   * @private
   */
  private createModalWithContent($content: JQuery): JQuery {
    return $(
      Modal.advanced({
        title: TYPO3.lang[LanguageKeys.modalProgressTitle],
        content: $content,
        size: Modal.sizes.small,
        staticBackdrop: true,
        buttons: [
          {
            text: TYPO3.lang[LanguageKeys.modalProgressButtonReport],
            icon: IconIdentifiers.listAlternative,
            // Trigger is defined by external module, button is hidden in the meantime
            btnClass: 'btn-primary hidden',
            name: CacheWarmupProgressModalButtonNames.reportButton,
          },
          {
            text: TYPO3.lang[LanguageKeys.modalProgressButtonRetry],
            icon: IconIdentifiers.refresh,
            // Trigger is defined by external module, button is hidden in the meantime
            btnClass: 'btn-default hidden',
            name: CacheWarmupProgressModalButtonNames.retryButton,
          },
          {
            text: TYPO3.lang[LanguageKeys.modalProgressButtonClose],
            btnClass: 'btn-default',
            trigger: (): void => Modal.dismiss(),
          },
        ],
      })
    );
  }
}
