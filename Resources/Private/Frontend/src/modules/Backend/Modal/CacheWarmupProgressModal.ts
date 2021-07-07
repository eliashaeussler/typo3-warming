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
import LanguageKeys from '../../../lib/Enums/LanguageKeys';
import Util from '../../../lib/Util';
import WarmupProgress from '../../../lib/WarmupProgress';

// Modules
import $ from 'jquery';
import Modal from 'TYPO3/CMS/Backend/Modal';

/**
 * Button names within cache warmup progress modal.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
enum CacheWarmupProgressModalButtonNames {
  reportButton = 'tx-warming-open-report',
  retryButton = 'tx-warming-retry',
}

/**
 * AMD module that shows a modal with the progress of the current cache warmup.
 *
 * Module: TYPO3/CMS/Warming/Backend/Modal/CacheWarmupProgressModal
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class CacheWarmupProgressModal {
  private $modal!: JQuery;
  private $progressBar!: JQuery;
  private $allCounter!: JQuery;
  private $failedCounter!: JQuery;

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
  public createModal(): void {
    const $content = this.buildInitialModalContent();

    // Build initial modal or apply content to existing modal
    if (Modal.currentModal) {
      this.$modal = Modal.currentModal;
      this.$modal.show();
      this.$modal.find('.modal-body').empty().append($content);
    } else {
      this.$modal = this.createModalWithContent($content);
    }

    // Hide footer until cache warmup is finished
    this.$modal.find('.modal-footer').hide();
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
    const percent = progress.getProgressInPercent();
    const failedCount = progress.getNumberOfFailedUrls();
    const {current, total} = progress.progress;

    this.$progressBar.attr('aria-valuenow', current);
    this.$progressBar.attr('aria-valuemax', total);
    this.$progressBar.css('width', `${percent}%`);
    this.$progressBar.html(`${percent.toFixed(2)}%`);

    if (failedCount > 0) {
      this.$progressBar.addClass('progress-bar-warning');
      this.$failedCounter.show().html(
        Util.formatString(TYPO3.lang[LanguageKeys.modalProgressFailedCounter], failedCount.toString())
      );
    }

    this.$allCounter.html(
      Util.formatString(TYPO3.lang[LanguageKeys.modalProgressAllCounter], current.toString(), total.toString())
    );

    if (progress.isFinished()) {
      this.$progressBar.removeClass('progress-bar-warning').addClass(
        failedCount > 0 ? 'progress-bar-danger' : 'progress-bar-success'
      );
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
   * Dismiss current modal.
   */
  public dismiss(): void {
    Modal.dismiss();
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
    const $content = $('<div>');

    this.$progressBar = $('<div class="progress-bar progress-bar-striped">')
      .attr('role', 'progressbar')
      .attr('aria-valuemin', 0)
      .attr('aria-valuemax', 0)
      .attr('aria-valuenow', 0);
    this.$allCounter = $('<div>').html(TYPO3.lang[LanguageKeys.modalProgressPlaceholder]);
    this.$failedCounter = $('<div class="badge badge-danger">');

    // Hide failed counter until any URL fails to be warmed up
    this.$failedCounter.hide();

    // Append progress bar and counter
    $content
      .append($('<div class="tx-warming-progress progress">').append(this.$progressBar))
      .append($('<div class="tx-warming-counter">').append(this.$allCounter, this.$failedCounter));

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
    return Modal.advanced({
      title: TYPO3.lang[LanguageKeys.modalProgressTitle],
      content: $content,
      size: Modal.sizes.small,
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
      ]
    });
  }
}

export default new CacheWarmupProgressModal();
