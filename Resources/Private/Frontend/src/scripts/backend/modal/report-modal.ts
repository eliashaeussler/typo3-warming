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

import {html, LitElement, TemplateResult} from 'lit';
import {customElement} from 'lit/decorators.js';
import Modal from '@typo3/backend/modal.js';

import '@eliashaeussler/typo3-warming/backend/modal/element/report-panel'
import '@eliashaeussler/typo3-warming/backend/modal/element/report-summary-card'
import {IconIdentifiers} from '@eliashaeussler/typo3-warming/enums/icon-identifiers';
import {LanguageKeys} from '@eliashaeussler/typo3-warming/enums/language-keys';
import {WarmupProgress} from '@eliashaeussler/typo3-warming/request/warmup-progress';

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

  protected createRenderRoot(): Element {
    // Avoid shadow DOM for Bootstrap CSS to be applied
    return this;
  }

  protected render(): TemplateResult {
    // Add text if no URLs were crawled
    if (this.progress.getTotalNumberOfCrawledUrls() === 0) {
      return this.createEmptyCrawlingNotice();
    }

    // Count all excluded URLs and sitemaps
    const excluded: number = this.progress.getNumberOfExcludedSitemaps() + this.progress.getNumberOfExcludedUrls();

    return html`
      <div class="card-container">
        ${this.progress.getNumberOfFailedUrls() > 0 ? html`
          <warming-report-summary-card
            class="col-4"
            title="${TYPO3.lang[LanguageKeys.modalReportPanelFailed]}"
            body="${TYPO3.lang[LanguageKeys.modalReportPanelFailedSummary]}"
            state="danger"
            icon="overlay-readonly"
            currentNumber="${this.progress.getNumberOfFailedUrls()}"
            totalNumber="${this.progress.progress.current}"
          />
        ` : ''}

        ${this.progress.getNumberOfSuccessfulUrls() > 0 ? html`
          <warming-report-summary-card
            class="col-4"
            title="${TYPO3.lang[LanguageKeys.modalReportPanelSuccessful]}"
            body="${TYPO3.lang[LanguageKeys.modalReportPanelSuccessfulSummary]}"
            state="success"
            icon="overlay-approved"
            currentNumber="${this.progress.getNumberOfSuccessfulUrls()}"
            totalNumber="${this.progress.progress.current}"
          />
        ` : ''}

        ${excluded > 0 ? html`
          <warming-report-summary-card
            class="col-4"
            title="${TYPO3.lang[LanguageKeys.modalReportPanelExcluded]}"
            body="${TYPO3.lang[LanguageKeys.modalReportPanelExcludedSummary]}"
            state="warning"
            icon="overlay-warning"
            currentNumber="${this.progress.getNumberOfSuccessfulUrls()}"
          />
        ` : ''}
      </div>

      ${this.progress.getNumberOfFailedUrls() > 0 ? html`
        <warming-report-panel
          title="${TYPO3.lang[LanguageKeys.modalReportPanelFailed]}"
          state="danger"
          urls="${JSON.stringify(this.progress.urls.failed)}"
        />
      ` : ''}

      ${this.progress.getNumberOfSuccessfulUrls() > 0 ? html`
        <warming-report-panel
          title="${TYPO3.lang[LanguageKeys.modalReportPanelSuccessful]}"
          state="success"
          urls="${JSON.stringify(this.progress.urls.successful)}"
        />
      ` : ''}

      ${this.progress.getNumberOfExcludedSitemaps() > 0 ? html`
        <warming-report-panel
          title="${TYPO3.lang[LanguageKeys.modalReportPanelExcludedSitemaps]}"
          state="warning"
          urls="${JSON.stringify(this.progress.excluded.sitemaps)}"
        />
      ` : ''}

      ${this.progress.getNumberOfExcludedUrls() > 0 ? html`
        <warming-report-panel
          title="${TYPO3.lang[LanguageKeys.modalReportPanelExcludedUrls]}"
          state="warning"
          urls="${JSON.stringify(this.progress.excluded.urls)}"
        />
      ` : ''}
    `;
  }

  private createEmptyCrawlingNotice(): TemplateResult {
    return html`
      <div class="callout callout-info">
        <div class="media">
          <div class="media-left">
              <span class="icon-emphasized">
                <typo3-backend-icon identifier="${IconIdentifiers.info}" />
              </span>
          </div>
          <div class="media-body">
            ${TYPO3.lang[LanguageKeys.modalReportNoUrlsCrawled]}
          </div>
        </div>
      </div>
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

    // Create modal
    Modal.advanced({
      title: TYPO3.lang[LanguageKeys.modalReportTitle],
      content: new ReportModal(progress),
      size: Modal.sizes.large,
      buttons: buttons,
    });
  }
}
