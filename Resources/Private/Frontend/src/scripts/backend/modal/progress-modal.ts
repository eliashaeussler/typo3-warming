/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2026 Elias Häußler <elias@haeussler.dev>
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

import {html, LitElement, nothing, TemplateResult} from 'lit';
import {customElement, property} from 'lit/decorators.js';
import {unsafeHTML} from 'lit/directives/unsafe-html.js';
import '@typo3/backend/element/progress-bar-element.js';
import Modal from '@typo3/backend/modal.js';
import RegularEvent from '@typo3/core/event/regular-event.js';
import {lll} from '@typo3/core/lit-helper.js';

import {IconIdentifiers} from '@eliashaeussler/typo3-warming/enums/icon-identifiers';
import {LanguageKeys} from '@eliashaeussler/typo3-warming/enums/language-keys';
import {ReportModal} from '@eliashaeussler/typo3-warming/backend/modal/report-modal';
import {StringHelper} from '@eliashaeussler/typo3-warming/helper/string-helper';
import {WarmupProgress} from '@eliashaeussler/typo3-warming/request/warmup-progress';
import {WarmupState} from '@eliashaeussler/typo3-warming/enums/warmup-state';

enum CacheWarmupProgressModalButtonNames {
  closeButton = 'tx-warming-close',
  reportButton = 'tx-warming-open-report',
  retryButton = 'tx-warming-retry',
}

/**
 * @see https://github.com/TYPO3/typo3/blob/v14.3.0/Build/Sources/TypeScript/backend/enum/severity.ts
 */
enum SeverityEnum {
  notice = -2,
  info = -1,
  ok = 0,
  warning = 1,
  error = 2,
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

    const severity: SeverityEnum = (() => {
      switch (this.progress.state) {
        case WarmupState.Failed:
          return SeverityEnum.error;
        case WarmupState.Warning:
          return SeverityEnum.warning;
        case WarmupState.Success:
          return isActive ? SeverityEnum.notice : SeverityEnum.ok;
        case WarmupState.Cancelled:
        case WarmupState.Unknown:
          return SeverityEnum.notice;
        default:
          return failedCount > 0 ? SeverityEnum.error : SeverityEnum.notice;
      }
    })();

    return html`
      <div class="tx-warming-progress-modal">
        <typo3-backend-progress-bar value="${this.progress.progress.current}"
                                    max="${this.progress.progress.total}"
                                    severity="${severity}"
        ></typo3-backend-progress-bar>
        <div class="tx-warming-progress-modal-counter">
          ${isActive ? html`<div>${percent.toFixed(2)}%</div>` : html`
            <div class="tx-warming-progress-placeholder">
              ${lll(LanguageKeys.modalProgressPlaceholder)}
            </div>
          `}
          ${failedCount > 0
            ? html`
              <div class="badge badge-danger">
                ${StringHelper.formatString(
                  lll(LanguageKeys.modalProgressFailedCounter),
                  failedCount.toString(),
                  this.progress.progress.total.toString(),
                )}
              </div>
            `
            : html`
              <div>
                ${unsafeHTML(
                  StringHelper.formatString(
                    lll(LanguageKeys.modalProgressAllCounter),
                    this.progress.progress.current.toString(),
                    this.progress.progress.total.toString(),
                  ),
                )}
              </div>
            `
          }
        </div>
        ${this.progress.isFinished() ? nothing : html`
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
    const closeButton = this.getCloseButton();
    const reportButton = this.getReportButton();
    let modalTitle: string;

    if (closeButton !== null) {
      closeButton.innerText = lll(LanguageKeys.modalProgressButtonClose);
    }

    reportButton?.classList.remove('hidden');

    new RegularEvent('click', () => {
      ReportModal.createModal(progress, retryFunction);
    }).bindTo(reportButton);

    // Apply trigger function to "retry" button of progress modal
    if (progress.state !== WarmupState.Cancelled) {
      const retryButton = this.getRetryButton();

      retryButton?.classList.remove('hidden');

      new RegularEvent('click', retryFunction).bindTo(retryButton);
    }

    // Update modal title
    switch (progress.state) {
      case WarmupState.Failed:
        modalTitle = lll(LanguageKeys.modalProgressFailedTitle);
        break;
      case WarmupState.Warning:
        modalTitle = lll(LanguageKeys.modalProgressWarningTitle);
        break;
      case WarmupState.Success:
        modalTitle = lll(LanguageKeys.modalProgressSuccessTitle);
        break;
      case WarmupState.Cancelled:
        modalTitle = lll(LanguageKeys.modalProgressCancelledTitle);
        break;
      case WarmupState.Unknown:
        modalTitle = lll(LanguageKeys.modalProgressUnknownTitle);
        break;
    }

    this.getTitle().innerHTML = modalTitle;
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
   * Get title of current modal.
   *
   * @returns {HTMLElement} Title of current modal
   */
  private getTitle(): HTMLElement {
    const title: HTMLElement|null = this.modal.querySelector('.modal-header-title');

    if (title !== null) {
      return title;
    }

    // @todo Remove once support for TYPO3 v13 is dropped
    return this.modal.querySelector('.modal-title');
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
   * @returns {HTMLElement|null} Report button within current modal
   */
  private getReportButton(): HTMLElement|null {
    return this.getFooter().querySelector(`button[name=${CacheWarmupProgressModalButtonNames.reportButton}]`);
  }

  /**
   * Get retry button within current modal.
   *
   * @returns {HTMLElement|null} Retry button within current modal
   */
  private getRetryButton(): HTMLElement|null {
    return this.getFooter().querySelector(`button[name=${CacheWarmupProgressModalButtonNames.retryButton}]`);
  }

  /**
   * Get close button within current modal.
   *
   * @returns {HTMLElement|null} Close button within current modal
   */
  private getCloseButton(): HTMLElement|null {
    return this.getFooter().querySelector(`button[name=${CacheWarmupProgressModalButtonNames.closeButton}]`);
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
      title: lll(LanguageKeys.modalProgressTitle),
      content: modal,
      size: Modal.sizes.small,
      staticBackdrop: true,
      buttons: [
        {
          text: lll(LanguageKeys.modalProgressButtonReport),
          icon: IconIdentifiers.listAlternative,
          // Trigger is defined by external module, button is hidden in the meantime
          btnClass: 'btn-primary hidden',
          name: CacheWarmupProgressModalButtonNames.reportButton,
        },
        {
          text: lll(LanguageKeys.modalProgressButtonRetry),
          icon: IconIdentifiers.refresh,
          // Trigger is defined by external module, button is hidden in the meantime
          btnClass: 'btn-default hidden',
          name: CacheWarmupProgressModalButtonNames.retryButton,
        },
        {
          text: lll(LanguageKeys.modalProgressButtonCancel),
          btnClass: 'btn-default',
          name: CacheWarmupProgressModalButtonNames.closeButton,
          trigger: (): void => Modal.dismiss(),
        },
      ],
    });

    return modal;
  }
}
