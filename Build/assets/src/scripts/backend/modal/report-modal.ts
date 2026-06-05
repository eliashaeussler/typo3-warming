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

import {html, LitElement, TemplateResult} from 'lit';
import {customElement} from 'lit/decorators.js';
import Modal from '@typo3/backend/modal.js';
import {SeverityEnum} from '@typo3/backend/enum/severity.js';
import {lll} from '@typo3/core/lit-helper.js';
import '@typo3/backend/element/alert-element.js'

import '@eliashaeussler/typo3-warming/backend/modal/element/report-panel'
import '@eliashaeussler/typo3-warming/backend/modal/element/report-summary-card'
import {IconIdentifiers} from '@eliashaeussler/typo3-warming/enums/icon-identifiers';
import {LanguageKeys} from '@eliashaeussler/typo3-warming/enums/language-keys';
import {WarmupProgress} from '@eliashaeussler/typo3-warming/request/warmup-progress';
import {WarmupState} from '@eliashaeussler/typo3-warming/enums/warmup-state';

enum ClassNames {
  ModalBody = 'tx-warming-report-modal-body',
  ModalFooter = 'tx-warming-report-modal-footer',
  ModalHeader = 'tx-warming-report-modal-header',
  RequestId = 'tx-warming-request-id',
}

/**
 * Modal with report about a finished cache warmup.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
@customElement('warming-report-modal')
export class ReportModal extends LitElement {
  constructor(
    private progress: WarmupProgress,
  ) {
    super();
  }

  protected createRenderRoot(): HTMLElement {
    // Avoid shadow DOM for Bootstrap CSS to be applied
    return this;
  }

  protected render(): TemplateResult {
    const [header, footer]: [TemplateResult, TemplateResult] = this.renderHeaderAndFooter();

    // Add text if no URLs were crawled
    if (this.progress.getTotalNumberOfCrawledUrls() === 0) {
      return html`
        ${header}

        <div class="${ClassNames.ModalBody}">
          ${this.renderEmptyCrawlingAlert()}
        </div>

        ${footer}
      `;
    }

    // Count all excluded URLs and sitemaps
    const excluded: number = this.progress.getNumberOfExcludedSitemaps() + this.progress.getNumberOfExcludedUrls();

    return html`
      ${header}

      <div class="${ClassNames.ModalBody}">
        <div class="card-container">
          ${this.progress.getNumberOfFailedUrls() > 0 ? html`
            <warming-report-summary-card
              title="${lll(LanguageKeys.modalReportPanelFailed)}"
              body="${lll(LanguageKeys.modalReportPanelFailedSummary)}"
              state="danger"
              icon="overlay-readonly"
              currentNumber="${this.progress.getNumberOfFailedUrls()}"
              totalNumber="${this.progress.progress.current}"
            />
          ` : ''}

          ${this.progress.getNumberOfSuccessfulUrls() > 0 ? html`
            <warming-report-summary-card
              title="${lll(LanguageKeys.modalReportPanelSuccessful)}"
              body="${lll(LanguageKeys.modalReportPanelSuccessfulSummary)}"
              state="success"
              icon="overlay-approved"
              currentNumber="${this.progress.getNumberOfSuccessfulUrls()}"
              totalNumber="${this.progress.progress.current}"
            />
          ` : ''}

          ${excluded > 0 ? html`
            <warming-report-summary-card
              title="${lll(LanguageKeys.modalReportPanelExcluded)}"
              body="${lll(LanguageKeys.modalReportPanelExcludedSummary)}"
              state="warning"
              icon="overlay-warning"
              currentNumber="${excluded}"
            />
          ` : ''}
        </div>

        <div class="panel-container">
          ${this.progress.getNumberOfFailedUrls() > 0 ? html`
            <warming-report-panel
              title="${lll(LanguageKeys.modalReportPanelFailed)}"
              state="danger"
              urls="${JSON.stringify(this.progress.getFailedUrls())}"
              show="true"
            />
          ` : ''}

          ${this.progress.getNumberOfSuccessfulUrls() > 0 ? html`
            <warming-report-panel
              title="${lll(LanguageKeys.modalReportPanelSuccessful)}"
              state="success"
              urls="${JSON.stringify(this.progress.getSuccessfulUrls())}"
            />
          ` : ''}

          ${this.progress.getNumberOfExcludedSitemaps() > 0 ? html`
            <warming-report-panel
              title="${lll(LanguageKeys.modalReportPanelExcludedSitemaps)}"
              state="warning"
              urls="${JSON.stringify(this.progress.excluded.sitemaps)}"
            />
          ` : ''}

          ${this.progress.getNumberOfExcludedUrls() > 0 ? html`
            <warming-report-panel
              title="${lll(LanguageKeys.modalReportPanelExcludedUrls)}"
              state="warning"
              urls="${JSON.stringify(this.progress.excluded.urls)}"
            />
          ` : ''}
        </div>
      </div>

      ${footer}
    `;
  }

  private renderHeaderAndFooter(): [TemplateResult, TemplateResult] {
    return [
      html`
        <div class="${ClassNames.ModalHeader}">
          ${this.renderIncompleteCacheWarmupAlert()}
        </div>
      `,
      html`
        <div class="${ClassNames.ModalFooter}">
          ${this.renderRequestId()}
        </div>
      `,
    ];
  }

  private renderEmptyCrawlingAlert(): TemplateResult {
    return html`
      <typo3-backend-alert severity="${SeverityEnum.info}"
                           heading="${lll(LanguageKeys.modalReportNoUrlsCrawledHeader)}"
                           message="${lll(LanguageKeys.modalReportNoUrlsCrawledMessage)}"
                           show-icon
      ></typo3-backend-alert>
    `;
  }

  private renderIncompleteCacheWarmupAlert(): TemplateResult|null {
    if (this.progress.state === WarmupState.Cancelled) {
      return html`
        <typo3-backend-alert severity="${SeverityEnum.warning}"
                             heading="${lll(LanguageKeys.modalReportIncompleteHeader)}"
                             message="${lll(LanguageKeys.modalReportIncompleteMessage)}"
                             show-icon
        ></typo3-backend-alert>
      `;
    }

    return null;
  }

  private renderRequestId(): TemplateResult {
    return html`
      <small class="${ClassNames.RequestId}">
        ${lll(LanguageKeys.modalReportRequestId)} <code>${this.progress.requestId}</code
      ></small>
    `;
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
  ): void {
    // Ensure all other modals are closed
    Modal.dismiss();

    // Open modal with crawling report
    const buttons: {text: string, icon?: string, btnClass: string, trigger?: () => void}[] = [
      {
        text: lll(LanguageKeys.modalReportButtonRetry),
        icon: IconIdentifiers.refresh,
        btnClass: 'btn-default',
        trigger: retryFunction,
      },
      {
        text: lll(LanguageKeys.modalReportButtonClose),
        btnClass: 'btn-default',
        trigger: (): void => Modal.dismiss(),
      },
    ];

    // Get number of totally crawled pages
    if (progress.progress.current > 0) {
      buttons.unshift(
        {
          text: `${lll(LanguageKeys.modalReportTotal)} ${progress.progress.current}`,
          icon: IconIdentifiers.exclamationCircle,
          btnClass: 'disabled border-0',
        },
      );
    }

    // Create modal
    Modal.advanced({
      title: lll(LanguageKeys.modalReportTitle),
      content: new ReportModal(progress),
      size: Modal.sizes.large,
      buttons: buttons,
    });
  }
}
