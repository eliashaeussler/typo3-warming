/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2025 Elias Häußler <elias@haeussler.dev>
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

import {jsonSchema as codemirrorJsonSchema} from 'codemirror-json-schema';
import AjaxRequest from '@typo3/core/ajax/ajax-request.js';
import type AjaxResponse from '@typo3/core/ajax/ajax-response.js';
import Tagify, {TagData} from '@yaireo/tagify';

type ValidationState = boolean | 'error';

/**
 * Process settings in extension configuration.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class ExtensionConfiguration {
  initializeModalListener(nonce: string) {
    document.addEventListener('typo3-modal-shown', (event: CustomEvent) => {
      const target: HTMLElement = event.target as HTMLElement;
      const form: Element|null = target.querySelector('.t3js-extensionConfiguration-form[data-extension-key="warming"]');

      // Early return if extension configuration modal is *not* shown
      if (form === null) {
        return;
      }

      form.querySelectorAll('script').forEach((script: HTMLScriptElement) => {
        const clone = document.createElement('script');

        // Clone <script> node with text and all attributes
        clone.text = script.text;
        Array.from(script.attributes).forEach((attribute: Attr) => {
          clone.setAttribute(attribute.name, attribute.value);
        });

        // Enforce nonce attribute
        clone.setAttribute('nonce', nonce);

        // Inject cloned <script> node
        document.head.appendChild(clone).parentNode.removeChild(clone);
      });
    });
  }

  initializeCrawlerFqcnListener(fieldName: string, expectedInterface: string) {
    const element: HTMLInputElement = document.querySelector(`[name=${fieldName}]`);

    element.addEventListener('input', (event: InputEvent) => this.validateCrawlerFqcn(event, expectedInterface));
    element.dispatchEvent(new Event('input'));
  }

  async validateCrawlerFqcn(event: InputEvent, expectedInterface: string) {
    const target = event.target as HTMLInputElement;
    const actual: string = target.value;

    const validElement: HTMLElement = target.parentElement.querySelector('.is-valid');
    const invalidElement: HTMLElement = target.parentElement.querySelector('.is-invalid');
    const errorElement: HTMLElement = target.parentElement.querySelector('.has-error');

    let state: ValidationState;

    // Don't validate empty fields
    if (actual === '') {
      state = true;
    } else {
      state = await new AjaxRequest(TYPO3.settings.ajaxUrls.tx_warming_validate_crawler_fqcn)
        .post({actual, expected: expectedInterface})
        .then(
          async (response: typeof AjaxResponse) => {
            const {valid}: {valid: boolean} = await response.resolve();

            return 'boolean' === typeof valid ? valid : 'error';
          }
        )
        .catch(() => 'error')
      ;
    }

    switch (state) {
      case true:
        validElement.classList.remove('hidden');
        invalidElement.classList.add('hidden');
        errorElement.classList.add('hidden');
        break;
      case false:
        validElement.classList.add('hidden');
        invalidElement.classList.remove('hidden');
        errorElement.classList.add('hidden');
        break;
      case 'error':
        validElement.classList.add('hidden');
        invalidElement.classList.add('hidden');
        errorElement.classList.remove('hidden');
        break;
    }
  }

  initializeTagList(fieldName: string, validation: string | undefined = undefined) {
    const element: HTMLInputElement = document.querySelector(`[name=${fieldName}]`);
    const tagify = new Tagify(element, {
      originalInputValueFormat: (values) => values.map(item => item.value).join(','),
      createInvalidTags: false,
    });

    if (validation) {
      tagify.on('add', ({detail: {data, tag}}) => this.validateTag(validation, data, tag, tagify));
    }
  }

  async validateTag(validation: string, data: TagData, tag: HTMLElement, tagify: Tagify) {
    const {value}: {value: string} = data;

    // Wait before adding tag until validation is done
    tagify.tagLoading(tag, true);

    const {valid, error} = await new AjaxRequest(TYPO3.settings.ajaxUrls[validation])
      .post({pattern: value})
      .then(
        async (response: typeof AjaxResponse): Promise<{valid: boolean, error: string}> => {
          const {valid, error}: {valid: boolean, error?: string} = await response.resolve();

          return {valid, error};
        }
      )
      .catch((): {valid: boolean, error: string} => {
        return {
          valid: false,
          error: 'An unexpected error occurred. Please try again.',
        };
      })
      .finally(() => {
        tagify.tagLoading(tag, false);
      })
    ;

    if (!valid) {
      tagify.replaceTag(tag, {...data, __isValid: error});
    }
  }

  jsonSchema(json: string) {
    const schema = JSON.parse(json);

    return codemirrorJsonSchema(schema);
  }
}

export default new ExtensionConfiguration();
