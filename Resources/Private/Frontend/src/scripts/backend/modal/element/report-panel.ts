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
import {customElement, property} from 'lit/decorators.js';
import InfoWindow from '@typo3/backend/info-window.js';
import {lll} from '@typo3/core/lit-helper.js';

import {CrawlingResult} from '@eliashaeussler/typo3-warming/request/warmup-progress';
import {LanguageKeys} from '@eliashaeussler/typo3-warming/enums/language-keys';
import {StringHelper} from '@eliashaeussler/typo3-warming/helper/string-helper';

/**
 * Panel used in report modal to list crawled URLs.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
@customElement('warming-report-panel')
export class ReportPanel extends LitElement {
  @property({ type: String }) title: string;
  @property({ type: String }) state: string;
  @property({ type: Array }) urls: CrawlingResult[];
  @property({ type: Boolean }) show: boolean = false;
  @property({ attribute: false }) id: string;

  constructor() {
    super();
    this.id = `tx-warming-report-panel-${StringHelper.generateUniqueId()}`;
  }

  createRenderRoot(): HTMLElement {
    // Avoid shadow DOM for Bootstrap CSS to be applied
    return this;
  }

  render(): TemplateResult {
    return html`
      <div class="panel panel-${this.state}">
        <div class="panel-heading">
          <h3 class="panel-title">
            <a class="${this.show ? '' : 'collapsed'}"
               href="#${this.id}"
               data-bs-toggle="collapse"
               aria-controls="${this.id}"
               aria-expanded="${this.show ? 'true' : 'false'}"
            ><span class="caret"></span><strong>${this.title} (${this.urls.length})</strong></a>
          </h3>
        </div>
        <div id="${this.id}" class="panel-collapse collapse ${this.show ? 'show' : ''}">
          <div class="table-fit">
            <table class="table table-striped table-hover">
              <tbody>
                ${this.urls.map((result: CrawlingResult) => html`
                  <tr>
                    <td>
                      <a href="${result.url}" target="_blank">${result.url}</a>
                    </td>
                    <td class="col-control nowrap">
                      <div class="btn-group">
                        ${result.data.pageActions?.viewLog ? html`
                          <a href="${result.data.pageActions.viewLog}"
                             class="btn btn-default btn-sm nowrap"
                             title="${lll(LanguageKeys.modalReportActionLog)}"
                             target="_blank"
                          >
                            <typo3-backend-icon identifier="actions-list-alternative" size="small" />
                            ${lll(LanguageKeys.modalReportActionLog)}
                          </a>
                        ` : ''}

                        ${result.data.pageActions?.editRecord ? html`
                          <a href="${result.data.pageActions.editRecord}"
                             class="btn btn-default btn-sm nowrap"
                             title="${lll(LanguageKeys.modalReportActionEdit)}"
                             target="_blank"
                          >
                            <typo3-backend-icon identifier="actions-file-edit" size="small" />
                            ${lll(LanguageKeys.modalReportActionEdit)}
                          </a>
                        ` : ''}

                        ${result.data.urlMetadata?.pageId ? html`<button class="btn btn-default btn-sm nowrap"
                             title="${lll(LanguageKeys.modalReportActionInfo)}"
                             @click="${(event: MouseEvent) => {
                               event.preventDefault();

                               InfoWindow.showItem('pages', result.data.urlMetadata.pageId);
                             }}"
                          >
                            <typo3-backend-icon identifier="actions-info" size="small" />
                            ${lll(LanguageKeys.modalReportActionInfo)}
                          </button>
                        ` : ''}

                        <a href="${result.url}"
                           target="_blank"
                           class="btn btn-default btn-sm nowrap"
                           title="${lll(LanguageKeys.modalReportActionView)}"
                        >
                          <typo3-backend-icon identifier="actions-view-page" size="small" />
                          ${lll(LanguageKeys.modalReportActionView)}
                        </a>
                      </div>
                    </td>
                  </tr>
                `)}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  }
}
