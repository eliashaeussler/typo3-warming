'use strict'

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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
import * as clipboard from 'clipboard-polyfill';
import Icons from '@typo3/backend/icons.js';
import Modal from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';

import {CacheWarmer, SiteWarmupRequest, WarmingConfiguration} from '@eliashaeussler/typo3-warming/cache-warmer';
import {IconIdentifiers} from '@eliashaeussler/typo3-warming/enums/icon-identifiers';
import {LanguageKeys} from '@eliashaeussler/typo3-warming/enums/language-keys';
import {SiteSelection} from '@eliashaeussler/typo3-warming/backend/modal/dto/site-selection';

enum SitesModalSelectors {
  form = '.tx-warming-sites-modal',
  siteCheckbox = '.tx-warming-sites-group-selector > input',
  siteCheckboxAll = '.tx-warming-sites-group-selector > input[data-select-all]',
  useragentCopy = 'button.tx-warming-user-agent-copy-action',
  useragentCopyIcon = '.t3js-icon',
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
export class SitesModal {
  private modal!: HTMLElement;

  private readonly cacheWarmer: CacheWarmer;

  constructor() {
    this.cacheWarmer = new CacheWarmer();
    this.createModal();
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
  private createModal(): void {
    const url: URL = new URL(TYPO3.settings.ajaxUrls.tx_warming_fetch_sites, window.location.origin);

    // Ensure all other modals are closed
    Modal.dismiss();

    // Create new modal
    this.modal = Modal.advanced({
      type: Modal.types.ajax,
      content: url.toString(),
      title: TYPO3.lang[LanguageKeys.modalSitesTitle],
      size: Modal.sizes.medium,
      buttons: [
        {
          text: TYPO3.lang[LanguageKeys.modalSitesButtonStart],
          icon: IconIdentifiers.rocket,
          btnClass: 'btn-primary disabled',
          name: SitesModalButtonNames.startButton,
          trigger: (): void => {
            $(this.modal).find(SitesModalSelectors.form).submit();
          },
        },
      ],
      ajaxCallback: (element: HTMLElement): void => this.initializeSites(element),
    });
  }

  /**
   * Initialize sites within given modal body.
   *
   * @param modalBody {HTMLElement} Element referencing the modal body
   * @private
   */
  private initializeSites(modalBody: HTMLElement): void {
    // Hide footer until site is selected
    $(this.modal).find('.modal-footer').addClass('tx-warming-modal-footer').addClass('visually-hidden');

    // Run cache warmup
    $(modalBody).off('submit', SitesModalSelectors.form);
    $(modalBody).on('submit', SitesModalSelectors.form, (event: JQuery.TriggeredEvent): false => {
      event.preventDefault();

      this.performCacheWarmup(event.target);

      return false;
    });

    // Handle checked sites
    $(modalBody).on('input', SitesModalSelectors.siteCheckbox, (event: JQuery.TriggeredEvent): void => {
      $(this.modal).find('.modal-footer').removeClass('visually-hidden');
      this.toggleInputs(event.target);
    });

    // Copy user agent to clipboard in case the copy button is clicked
    $(modalBody).on('click', SitesModalSelectors.useragentCopy, (event: JQuery.TriggeredEvent): void => {
      event.preventDefault();
      event.stopImmediatePropagation();

      const userAgent: string|undefined = $(event.currentTarget).attr('data-text');
      if (userAgent) {
        SitesModal.copyUserAgentToClipboard(userAgent);
      }
    });
  }

  /**
   * Start a new cache warmup request from the given form.
   *
   * @param form {HTMLFormElement} The form with selected sites and settings
   * @private
   */
  private performCacheWarmup(form: HTMLFormElement): void {
    // Early return if no sites are selected
    if (!this.areSitesSelected()) {
      Notification.warning(
        TYPO3.lang[LanguageKeys.notificationNoSitesSelectedTitle],
        TYPO3.lang[LanguageKeys.notificationNoSitesSelectedMessage],
        15,
      );

      return;
    }

    const {configuration, sites} = this.parseFormValues(form);

    this.cacheWarmer.warmupCache(sites, [], configuration);
  }

  /**
   * Parse values of given form.
   *
   * @param form {HTMLFormElement} The form whose values are to be parsed
   * @returns {FormValues} Parsed form values
   * @private
   */
  private parseFormValues(form: HTMLFormElement): FormValues {
    const formValues = $(form).serializeArray();
    const configuration: WarmingConfiguration = {};
    const sites: SiteWarmupRequest = {};

    formValues.forEach(({name, value}) => {
      switch (name) {
        case 'site':
          try {
            const selection = SiteSelection.fromJson(value);
            const site = selection.getSiteIdentifier();

            if (!selection.isGroupRoot()) {
              if (!(site in sites)) {
                sites[site] = [];
              }
              sites[site].push(selection.getLanguageId());
            }
          } catch (InvalidSiteSelectionException) {
            // Continue with next input field.
          }
          break;

        case 'limit':
          configuration.limit = parseInt(value);
          break;

        case 'strategy':
          configuration.strategy = value;
          break;
      }
    });

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
      this.getStartButton().removeClass('disabled');
    } else {
      this.getStartButton().addClass('disabled');
    }
  }

  /**
   * Check if any input field of a site selection is checked.
   *
   * @private
   */
  private areSitesSelected(): boolean {
    let sitesAreSelected = false;

    this.getCheckboxes(true).each(function (): false|void {
      if ((this as HTMLInputElement).checked) {
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
    let checked = element.checked;

    if (siteSelection.getLanguageId() === null) {
      this.getCheckboxesByGroup(siteSelection.getGroupName()).each(function (): void {
        this.checked = checked;
      });
    } else {
      if (checked) {
        this.getCheckboxesByGroup(siteSelection.getGroupName()).each(function (): false|void {
          if (this.id !== element.id && !this.checked) {
            checked = false;
            return false;
          }
        });
      }

      this.getCheckboxGroupRoot(siteSelection.getGroupName()).checked = checked;
    }
  }

  /**
   * Toggle all site selection input fields, based on the given state.
   *
   * @param checked {boolean} `true` if all input fields should be checked, `false` otherwise
   * @private
   */
  private toggleAll(checked: boolean): void {
    this.getCheckboxes().each(function (): void {
      this.checked = checked;
    });
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
      this.getCheckboxes(true).each(function (): false|void {
        if (this.id !== element.id && !this.checked) {
          checked = false;
          return false;
        }
      });
    }

    this.getSelectAllCheckbox().checked = checked;
  }

  /**
   * Get all available checkboxes.
   *
   * @param excludeSelectAll {boolean} `true` if "select all" input field should be excluded, `false` otherwise
   * @returns {JQuery<HTMLInputElement>} All queried checkboxes
   * @private
   */
  private getCheckboxes(excludeSelectAll = false): JQuery<HTMLInputElement> {
    let selector = SitesModalSelectors.siteCheckbox + ':enabled';

    if (excludeSelectAll) {
      selector += ':not([data-select-all])';
    }

    return $(this.modal).find(selector) as JQuery<HTMLInputElement>;
  }

  /**
   * Get "select all" checkbox.
   *
   * @returns {HTMLInputElement | undefined} Reference to "select all" checkbox if available, `undefined` otherwise
   * @private
   */
  private getSelectAllCheckbox(): HTMLInputElement | undefined {
    return ($(this.modal).find(SitesModalSelectors.siteCheckboxAll) as JQuery<HTMLInputElement>).get(0);
  }

  /**
   * Get all checkboxes of given group.
   *
   * @param groupName {string} Name of the group to query.
   * @returns {JQuery<>HTMLInputElement>} List of checkboxes of the given group.
   * @private
   */
  private getCheckboxesByGroup(groupName: string): JQuery<HTMLInputElement> {
    return $(this.modal).find(`input[data-group="${groupName}"]:enabled`) as JQuery<HTMLInputElement>;
  }

  /**
   * Get root checkbox of given group.
   *
   * @param groupName {string} Name of the group to query.
   * @returns {HTMLInputElement | undefined} Reference to root checkbox of given group if available, `undefined` otherwise
   * @private
   */
  private getCheckboxGroupRoot(groupName: string): HTMLInputElement | undefined {
    return ($(this.modal).find(`input[data-group-root="${groupName}"]:enabled`) as JQuery<HTMLInputElement>).get(0);
  }

  /**
   * Get "start" button within modal footer.
   *
   * @returns {JQuery} Reference to "start" button
   * @private
   */
  private getStartButton(): JQuery {
    return $(this.modal).find(`button[name=${SitesModalButtonNames.startButton}]`);
  }

  /**
   * Copy given User-Agent header to clipboard.
   *
   * @param userAgent {string} User-Agent header to be copied to clipboard
   * @private
   */
  private static copyUserAgentToClipboard(userAgent: string): void {
    const $copyIcon: JQuery = $(SitesModalSelectors.useragentCopyIcon, SitesModalSelectors.useragentCopy);
    const $existingIcon: JQuery = $copyIcon.clone();

    // Show spinner when copying user agent
    Icons.getIcon(IconIdentifiers.spinner, Icons.sizes.small).then((spinner: string): void => {
      $copyIcon.replaceWith(spinner);
    });

    // Copy user agent to clipboard
    Promise.all([
      (navigator.clipboard ?? clipboard).writeText(userAgent),
      Icons.getIcon(IconIdentifiers.check, Icons.sizes.small),
    ])
      .then(
        async ([, icon]): Promise<void> => {
          const existingText = $(SitesModalSelectors.useragentCopyText).text();
          $(SitesModalSelectors.useragentCopyText).text(TYPO3.lang[LanguageKeys.modalSitesUserAgentActionSuccessful]);
          $(SitesModalSelectors.useragentCopyIcon, SitesModalSelectors.useragentCopy).replaceWith(icon);

          // Restore copy button after 3 seconds
          window.setTimeout((): void => {
            $(SitesModalSelectors.useragentCopyIcon, SitesModalSelectors.useragentCopy).replaceWith($existingIcon);
            $(SitesModalSelectors.useragentCopyText).text(existingText);
            $(SitesModalSelectors.useragentCopy).trigger('blur');
          }, 3000);
        },
        (): void => {
          $(SitesModalSelectors.useragentCopyIcon, SitesModalSelectors.useragentCopy).replaceWith($existingIcon);
        }
      );
  }
}
