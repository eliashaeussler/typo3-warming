/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2024 Elias Häußler <elias@haeussler.dev>
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

import {html, LitElement, TemplateResult} from 'lit';
import {classMap, ClassInfo} from 'lit/directives/class-map.js';
import {customElement, property} from 'lit/decorators.js';
import {styleMap, StyleInfo} from 'lit/directives/style-map.js';
import {unsafeHTML} from 'lit/directives/unsafe-html.js';
import Modal from '@typo3/backend/modal.js';
import RegularEvent from '@typo3/core/event/regular-event.js';

import {IconIdentifiers} from '@eliashaeussler/typo3-warming/enums/icon-identifiers';
import {LanguageKeys} from '@eliashaeussler/typo3-warming/enums/language-keys';
import {ReportModal} from '@eliashaeussler/typo3-warming/backend/modal/report-modal';
import {StringHelper} from '@eliashaeussler/typo3-warming/helper/string-helper';
import {WarmupProgress} from '@eliashaeussler/typo3-warming/request/warmup-progress';
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
@customElement('warming-progress-modal')
export class ProgressModal extends LitElement {
  @property({
    attribute: false,
    type: Object,
    hasChanged: (): boolean => true,
  })
  progress: WarmupProgress;

  @property({ attribute: false }) modal: typeof Modal;

  constructor(progress: WarmupProgress) {
    super();
    this.progress = progress;
    this.modal = Modal.currentModal;
  }

  protected createRenderRoot(): HTMLElement {
    return this;
  }

  protected render(): TemplateResult {
    const isActive = this.progress.progress.current > 0;
    const failedCount: number = this.progress.getNumberOfFailedUrls();
    const percent: number = this.progress.getProgressInPercent();

    const progressBarClasses: ClassInfo = {
      'progress-bar': true,
      'progress-bar-striped': true,

      // Active
      active: isActive && !this.progress.isFinished(),
      'progress-bar-animated': isActive && !this.progress.isFinished(),

      // Finished
      'bg-danger': failedCount > 0 && this.progress.isFinished(),
      'bg-success': failedCount === 0 && this.progress.isFinished(),
      'progress-bar-danger': failedCount > 0 && this.progress.isFinished(),
      'progress-bar-success': failedCount === 0 && this.progress.isFinished(),

      // Failure
      'bg-warning': failedCount > 0,
      'progress-bar-warning': failedCount > 0 && !this.progress.isFinished(),
    };
    const progressBarStyles: StyleInfo = {
      width: `${isActive ? percent : 0}%`,
    };

    return html`
      <div class="tx-warming-progress-modal">
        <div class="tx-warming-progress-modal-progress progress">
          <div class=${classMap(progressBarClasses)}
               role="progressbar"
               aria-valuemin="0"
               aria-valuemax="${this.progress.progress.total}"
               aria-valuenow="${this.progress.progress.current}"
               style=${styleMap(progressBarStyles)}
          >
            ${isActive ? `${percent.toFixed(2)}%` : ''}
          </div>
        </div>
        <div class="tx-warming-progress-modal-counter">
          ${isActive ? '' : html`
            <div class="tx-warming-progress-placeholder">
              ${TYPO3.lang[LanguageKeys.modalProgressPlaceholder]}
            </div>
          `}
          <div>
            ${unsafeHTML(
              StringHelper.formatString(
                TYPO3.lang[LanguageKeys.modalProgressAllCounter],
                this.progress.progress.current.toString(),
                this.progress.progress.total.toString(),
              ),
            )}
          </div>
          ${failedCount > 0 ? html`
            <div class="badge badge-danger">
              ${StringHelper.formatString(
                TYPO3.lang[LanguageKeys.modalProgressFailedCounter],
                failedCount.toString(),
              )}
            </div>
          ` : ''}
        </div>
        ${this.progress.isFinished() ? '' : html`
          <div class="tx-warming-progress-modal-current-url">
            ${this.progress.getCurrentUrl()}
          </div>
        `}
      </div>
    `;
  }

  /**
   * Finish progress modal.
   *
   * @param progress {WarmupProgress} Current warmup progress state
   * @param retryFunction {() => Promise<WarmupProgress>} Function to retry cache warmup
   */
  public finishProgress(progress: WarmupProgress, retryFunction: () => Promise<WarmupProgress>): void {
    const reportButton = this.getReportButton();

    reportButton.classList.remove('hidden');

    new RegularEvent('click', () => {
      ReportModal.createModal(progress, retryFunction);
    }).bindTo(reportButton);

    // Apply trigger function to "retry" button of progress modal
    if (progress.state !== WarmupState.Aborted) {
      const retryButton = this.getRetryButton();

      retryButton.classList.remove('hidden');

      new RegularEvent('click', retryFunction).bindTo(retryButton);
    }
  }

  /**
   * Get current modal as {@link Modal} object.
   *
   * @returns {Modal} Current modal
   */
  public getModal(): typeof Modal {
    return this.modal;
  }

  /**
   * Get footer within current modal.
   *
   * @returns {HTMLElement} Footer within current modal
   */
  private getFooter(): HTMLElement {
    return this.modal.querySelector('.modal-footer');
  }

  /**
   * Get report button within current modal.
   *
   * @returns {HTMLElement} Report button within current modal
   */
  private getReportButton(): HTMLElement {
    return this.getFooter().querySelector(`button[name=${CacheWarmupProgressModalButtonNames.reportButton}]`);
  }

  /**
   * Get retry button within current modal.
   *
   * @returns {HTMLElement} Retry button within current modal
   */
  private getRetryButton(): HTMLElement {
    return this.getFooter().querySelector(`button[name=${CacheWarmupProgressModalButtonNames.retryButton}]`);
  }

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
   *
   * @param progress {WarmupProgress} An object holding data about the progress of the current cache warmup
   */
  public static createModal(progress: WarmupProgress): ProgressModal {
    const modal = new ProgressModal(progress);

    // Ensure all other modals are closed
    Modal.dismiss();

    // Create modal element
    modal.modal = Modal.advanced({
      title: TYPO3.lang[LanguageKeys.modalProgressTitle],
      content: modal,
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
    });

    return modal;
  }
}
