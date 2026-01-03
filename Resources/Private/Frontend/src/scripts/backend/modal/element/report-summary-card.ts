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

/**
 * Summary card used in report modal to summarize cache warmup result.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
@customElement('warming-report-summary-card')
export class ReportSummaryCard extends LitElement {
  @property({ type: String }) title: string;
  @property({ type: String }) body: string;
  @property({ type: String }) state: string;
  @property({ type: String }) icon: string;
  @property({ type: Number }) currentNumber: number;
  @property({ type: Number }) totalNumber: number = null;

  createRenderRoot(): HTMLElement {
    // Avoid shadow DOM for Bootstrap CSS to be applied
    return this;
  }

  render(): TemplateResult {
    return html`
      <div class="card card-${this.state} h-100">
        <div class="card-header">
          <div class="card-icon">
            <typo3-backend-icon identifier="${this.icon}" size="medium" />
          </div>
          <div class="card-header-body">
            <h1 class="card-title">${this.title}</h1>
            <span class="card-subtitle">${
              this.totalNumber !== null
                ? html`<strong>${this.currentNumber}</strong>/${this.totalNumber}`
                : this.currentNumber.toString()
            }</span>
          </div>
        </div>
        <div class="card-body">
          <p class="card-text">${this.body}</p>
        </div>
      </div>
    `;
  }
}
