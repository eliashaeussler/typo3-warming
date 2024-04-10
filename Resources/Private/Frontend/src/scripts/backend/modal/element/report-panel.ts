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
import {customElement, property} from 'lit/decorators.js';

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
  @property({ type: Array }) urls: string[];
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
            <a class="collapsed"
               href="#${this.id}"
               data-bs-toggle="collapse"
               aria-controls="${this.id}"
               aria-expanded="false"
            >
              <span class="caret"></span>
              <strong> ${this.title} (${this.urls.length})</strong>
            </a>
          </h3>
        </div>
        <div id="${this.id}" class="panel-collapse collapse">
          <div class="table-fit">
            <table class="table table-striped table-hover">
              <tbody>
                ${this.urls.map((url: string) => html`
                  <tr>
                    <td>${url}</td>
                    <td class="col-control nowrap">
                      <div class="btn-group">
                        <a href="${url}" target="_blank" class="btn btn-default btn-sm nowrap">
                          <typo3-backend-icon identifier="actions-view-page" size="small" />
                          ${TYPO3.lang[LanguageKeys.modalReportActionView]}
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
