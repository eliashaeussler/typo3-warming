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

import {LitElement} from 'lit';
import {customElement, query, queryAll} from 'lit/decorators.js';
import * as clipboard from 'clipboard-polyfill';
import Icons from '@typo3/backend/icons.js';
import Modal from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';
import RegularEvent from '@typo3/core/event/regular-event.js';
import {lll} from '@typo3/core/lit-helper.js';

import {CacheWarmer, SiteWarmupRequest, WarmingConfiguration} from '@eliashaeussler/typo3-warming/cache-warmer';
import {IconIdentifiers} from '@eliashaeussler/typo3-warming/enums/icon-identifiers';
import {LanguageKeys} from '@eliashaeussler/typo3-warming/enums/language-keys';
import {SiteSelection} from '@eliashaeussler/typo3-warming/backend/modal/dto/site-selection';

enum SitesModalSelectors {
  form = '.tx-warming-sites-modal',
  showAllButton = '.tx-warming-sites-show-all',
  siteCheckbox = '.tx-warming-sites-group-selector > input',
  siteCheckboxAll = '.tx-warming-sites-group-selector > input[data-select-all]',
  useragentCopy = 'button.tx-warming-user-agent-copy-action',
  useragentCopyIcon = SitesModalSelectors.useragentCopy + ' .t3js-icon',
  useragentCopyText = '.tx-warming-user-agent-copy-text',
}

enum SitesModalButtonNames {
  startButton = 'tx-warming-start-warmup',
}

type FormValues = {
  configuration: WarmingConfiguration,
  sites: SiteWarmupRequest,
};

/**
 * Modal with site selections used to start a new cache warmup.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
@customElement('warming-sites-modal')
export class SitesModal extends LitElement {
  @query(SitesModalSelectors.form)
  private _form: HTMLFormElement;

  @query(SitesModalSelectors.showAllButton)
  private _showAllButton: HTMLButtonElement | null;

  @query(SitesModalSelectors.siteCheckboxAll)
  private _selectAllCheckbox: HTMLInputElement | null;

  @queryAll(SitesModalSelectors.siteCheckbox)
  private _checkboxes: NodeListOf<HTMLInputElement>

  @queryAll(SitesModalSelectors.siteCheckbox + ':enabled')
  private _enabledCheckboxes: NodeListOf<HTMLInputElement>;

  @queryAll(SitesModalSelectors.siteCheckbox + ':enabled:not([data-select-all])')
  private _enabledCheckboxesWithoutGroupElements: NodeListOf<HTMLInputElement>;

  @query(SitesModalSelectors.useragentCopy)
  private _useragentCopyButton: HTMLButtonElement;

  @query(SitesModalSelectors.useragentCopyIcon)
  private _useragentCopyIcon: HTMLElement;

  @query(SitesModalSelectors.useragentCopyText)
  private _useragentCopyText: HTMLElement;

  private cacheWarmer: CacheWarmer;
  private modal: typeof Modal;

  constructor() {
    super();

    this.cacheWarmer = new CacheWarmer();
    this.modal = Modal.currentModal;
  }

  protected createRenderRoot(): HTMLElement {
    // Avoid shadow DOM for Bootstrap CSS to be applied
    return this;
  }

  connectedCallback() {
    super.connectedCallback();

    this.initializeSites();
    this.initializeFilteredList();
  }

  /**
   * Initialize sites within modal.
   *
   * @private
   */
  private initializeSites(): void {
    const modalFooter = this.modal.querySelector('.modal-footer');

    // Hide footer until site is selected
    modalFooter.classList.add('tx-warming-modal-footer', 'visually-hidden');

    // Run cache warmup
    new RegularEvent('submit', (event: Event): false => {
      event.preventDefault();

      this.performCacheWarmup();

      return false;
    }).bindTo(this._form);

    // Handle checked sites
    this._checkboxes.forEach((checkbox: HTMLInputElement): void => {
      new RegularEvent('input', (event: Event): void => {
        modalFooter.classList.remove('visually-hidden');
        this.toggleInputs(event.target as HTMLInputElement);
      }).bindTo(checkbox);
    })

    // Copy user agent to clipboard in case the copy button is clicked
    new RegularEvent('click', (event: Event): void => {
      event.preventDefault();
      event.stopImmediatePropagation();

      const userAgent: string|undefined = (event.currentTarget as HTMLButtonElement).dataset.text;
      if (userAgent) {
        this.copyUserAgentToClipboard(userAgent);
      }
    }).bindTo(this._useragentCopyButton);
  }

  /**
   * Initialize actions regarding filtered sites list within modal.
   *
   * @private
   */
  private initializeFilteredList(): void {
    if (this._showAllButton !== null) {
      new RegularEvent('click', (event: Event): void => {
        event.preventDefault();

        SitesModal.createModal();
      }).bindTo(this._showAllButton);
    }
  }

  /**
   * Start a new cache warmup request from the modal form.
   *
   * @private
   */
  private performCacheWarmup(): void {
    // Early return if no sites are selected
    if (!this.areSitesSelected()) {
      Notification.warning(
        lll(LanguageKeys.notificationNoSitesSelectedTitle),
        lll(LanguageKeys.notificationNoSitesSelectedMessage),
        15,
      );

      return;
    }

    const {configuration, sites} = this.parseFormValues();

    this.cacheWarmer.warmupCache(sites, [], configuration);
  }

  /**
   * Parse values of form.
   *
   * @returns {FormValues} Parsed form values
   * @private
   */
  private parseFormValues(): FormValues {
    const formData: FormData = new FormData(this._form);
    const configuration: WarmingConfiguration = {};
    const sites: SiteWarmupRequest = {};

    for (const [name, value] of formData) {
      switch (name) {
        case 'site':
          try {
            const selection = SiteSelection.fromJson(value.toString());
            const site = selection.getSiteIdentifier();

            // Skip root checkboxes
            if (selection.isGroupRoot()) {
              continue;
            }

            // Push site language
            sites[site] ??= [];
            sites[site].push(selection.getLanguageId());
          } catch {
            // Continue with next input field.
          }
          break;

        case 'limit':
          configuration.limit = parseInt(value.toString());
          break;

        case 'strategy':
          configuration.strategy = value.toString();
          break;
      }
    }

    return {configuration, sites};
  }

  /**
   * Toggle site selection input fields, based on the given input element.
   *
   * @param element {HTMLInputElement} The element used to toggle other input fields
   * @private
   */
  private toggleInputs(element: HTMLInputElement): void {
    if (element.dataset.selectAll) {
      // Toggle all inputs
      this.toggleAll(element.checked);
    } else {
      const selection: SiteSelection = SiteSelection.fromJson(element.value);

      // Toggle input groups
      if (selection.isWithinGroup()) {
        this.toggleGroup(selection, element);
      }

      // Toggle select all
      this.toggleSelectAll(element)
    }

    // Toggle submit button
    if (this.areSitesSelected()) {
      this.getStartButton().classList.remove('disabled');
    } else {
      this.getStartButton().classList.add('disabled');
    }
  }

  /**
   * Check if any input field of a site selection is checked.
   *
   * @private
   */
  private areSitesSelected(): boolean {
    let sitesAreSelected = false;

    this._enabledCheckboxesWithoutGroupElements.forEach((checkbox: HTMLInputElement): false|void => {
      if (checkbox.checked) {
        sitesAreSelected = true;
        return false;
      }
    });

    return sitesAreSelected;
  }

  /**
   * Toggle all site selections within a given group, identified by the given
   * site selection and corresponding element.
   *
   * @param siteSelection {SiteSelection} Site selection whose input elements should be toggled
   * @param element {HTMLInputElement} Current element used as reference for toggling other input fields
   * @private
   */
  private toggleGroup(siteSelection: SiteSelection, element: HTMLInputElement): void {
    const checkboxes = this.getCheckboxesByGroup(siteSelection.getGroupName());

    if (siteSelection.getLanguageId() === null) {
      checkboxes.forEach((checkbox: HTMLInputElement): void => {
        checkbox.checked = element.checked;
      });
    } else {
      const checkboxGroupRoot = this.getCheckboxGroupRoot(siteSelection.getGroupName());
      let hasChecked = false;
      let hasUnchecked = false;

      checkboxes.forEach((checkbox: HTMLInputElement): void => {
        if (checkbox.checked) {
          hasChecked = true;
        } else {
          hasUnchecked = true;
        }
      });

      checkboxGroupRoot.checked = hasChecked && !hasUnchecked;
      checkboxGroupRoot.indeterminate = hasChecked && hasUnchecked;
    }
  }

  /**
   * Toggle all site selection input fields, based on the given state.
   *
   * @param checked {boolean} `true` if all input fields should be checked, `false` otherwise
   * @private
   */
  private toggleAll(checked: boolean): void {
    this._enabledCheckboxes.forEach((checkbox: HTMLInputElement): void => {
      checkbox.checked = checked;
    })
  }

  /**
   * Toggle "select all" input field, based on the state of the given element.
   *
   * @param element {HTMLInputElement} Input element used as reference to toggle "select all" input field
   * @private
   */
  private toggleSelectAll(element: HTMLInputElement): void {
    let checked = element.checked;

    if (checked) {
      this._enabledCheckboxesWithoutGroupElements.forEach((checkbox: HTMLInputElement): false|void => {
        if (checkbox.id !== element.id && !checkbox.checked) {
          checked = false;
          return false;
        }
      })
    }

    if (this._selectAllCheckbox !== null) {
      this._selectAllCheckbox.checked = checked;
    }
  }

  /**
   * Get all checkboxes of given group.
   *
   * @param groupName {string} Name of the group to query.
   * @returns {NodeListOf<HTMLInputElement>} List of checkboxes of the given group.
   * @private
   */
  private getCheckboxesByGroup(groupName: string): NodeListOf<HTMLInputElement> {
    return this.renderRoot.querySelectorAll(`input[data-group="${groupName}"]:enabled`);
  }

  /**
   * Get root checkbox of given group.
   *
   * @param groupName {string} Name of the group to query.
   * @returns {HTMLInputElement | undefined} Reference to root checkbox of given group if available, `undefined` otherwise
   * @private
   */
  private getCheckboxGroupRoot(groupName: string): HTMLInputElement | undefined {
    return this.renderRoot.querySelector(`input[data-group-root="${groupName}"]:enabled`);
  }

  /**
   * Get "start" button within modal footer.
   *
   * @returns {HTMLButtonElement} Reference to "start" button
   * @private
   */
  private getStartButton(): HTMLButtonElement {
    return this.modal.querySelector(`button[name=${SitesModalButtonNames.startButton}]`);
  }

  /**
   * Copy given User-Agent header to clipboard.
   *
   * @param userAgent {string} User-Agent header to be copied to clipboard
   * @private
   */
  private copyUserAgentToClipboard(userAgent: string): void {
    const existingIcon: HTMLElement = this._useragentCopyIcon.cloneNode(true) as HTMLElement;

    // Show spinner when copying user agent
    Icons.getIcon(IconIdentifiers.spinner, Icons.sizes.small).then((spinner: string): void => {
      this._useragentCopyIcon.innerHTML = spinner;
    });

    // Copy user agent to clipboard
    Promise.all([
      (navigator.clipboard ?? clipboard).writeText(userAgent),
      Icons.getIcon(IconIdentifiers.check, Icons.sizes.small),
    ])
      .then(
        async ([, icon]): Promise<void> => {
          const existingText = this._useragentCopyText.innerText;
          this._useragentCopyText.innerText = lll(LanguageKeys.modalSitesUserAgentActionSuccessful);
          this._useragentCopyIcon.innerHTML = icon;

          // Restore copy button after 3 seconds
          window.setTimeout((): void => {
            this._useragentCopyIcon.innerHTML = existingIcon.innerHTML;
            this._useragentCopyText.innerText = existingText;
            this._useragentCopyButton.blur();
          }, 3000);
        },
        (): void => {
          this._useragentCopyIcon.innerHTML = existingIcon.innerHTML;
        }
      );
  }

  /**
   * Create modal with available sites.
   *
   * Creates a new modal with content fetched as AJAX request. The modal contains
   * all available sites used to start a new cache warmup. In addition, it shows
   * the current user-agent and some adjustable cache warmup settings.
   *
   * Next to the modal content, a footer with a "start" button is added. The footer
   * is hidden as long as no site is actively selected.
   */
  public static createModal(limitToSite: string | null = null): void {
    const url: URL = new URL(TYPO3.settings.ajaxUrls.tx_warming_fetch_sites, window.location.origin);

    if (limitToSite !== null) {
      url.searchParams.set('limitToSite', limitToSite);
    }

    // Ensure all other modals are closed
    Modal.dismiss();

    // Create new modal
    Modal.advanced({
      type: Modal.types.ajax,
      content: url.toString(),
      title: lll(LanguageKeys.modalSitesTitle),
      size: Modal.sizes.medium,
      buttons: [
        {
          text: lll(LanguageKeys.modalSitesButtonStart),
          icon: IconIdentifiers.rocket,
          btnClass: 'btn-primary disabled',
          name: SitesModalButtonNames.startButton,
          trigger: (): void => {
            Modal.currentModal.querySelector(SitesModalSelectors.form).requestSubmit();
          },
        },
      ],
    });
  }
}
