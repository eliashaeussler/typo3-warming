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

import {CacheWarmer, PageWarmupRequest, SiteWarmupRequest} from '@eliashaeussler/typo3-warming/cache-warmer';
import {MissingSiteIdentifierException} from '@eliashaeussler/typo3-warming/exception/missing-site-identifier-exception';

/**
 * Run cache warmup from the SVG tree context menu.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
export class ContextMenuAction {
  /**
   * Trigger cache warmup for a specific page, identified by the given UID.
   *
   * @param table {string} Table name associated to the triggered SVG tree
   * @param uid {number} UID of the associated element within the triggered SVG tree
   * @param data {object} Additional data attributes from the original context menu item
   */
  public warmupPageCache(table: string, uid: number, data: object): void {
    if ('pages' === table) {
      const languageId: number = ContextMenuAction.determineLanguage(data);

      const pages: PageWarmupRequest = {};
      pages[uid] = [languageId];

      (new CacheWarmer()).warmupCache({}, pages);
    }
  }

  /**
   * Trigger cache warmup for a specific site, identified by the given UID.
   *
   * @param table {string} Table name associated to the triggered SVG tree
   * @param uid {number} UID of the associated element within the triggered SVG tree
   * @param data {object} Additional data attributes from the original context menu item
   */
  public warmupSiteCache(table: string, uid: number, data: object): void {
    if ('pages' === table) {
      const languageId: number = ContextMenuAction.determineLanguage(data);
      const siteIdentifier: string = ContextMenuAction.determineSiteIdentifier(data);

      const sites: SiteWarmupRequest = {};
      sites[siteIdentifier] = [languageId];

      (new CacheWarmer()).warmupCache(sites, {});
    }
  }

  /**
   * Determine requested language ID from context menu action.
   *
   * Tests whether a language ID is defined in the current context menu
   * action and returns it, otherwise `NULL` is returned. The language ID
   * is defined as `data-language-id` attribute in the context menu action.
   *
   * @param data {object} Additional data attributes from the original context menu item
   * @returns {number|null} The resolved language ID or `NULL`
   * @private
   */
  private static determineLanguage(data: object): number | null {
    if (!('languageId' in data) || typeof data.languageId !== 'string') {
      return null;
    }

    return parseInt(data.languageId);
  }

  /**
   * Determine current site identifier from context menu action.
   *
   * @param data {object} Additional data attributes from the original context menu item
   * @returns {string} The resolved site identifier
   * @private
   */
  private static determineSiteIdentifier(data: object): string {
    if (!('siteIdentifier' in data) || typeof data.siteIdentifier !== 'string') {
      throw MissingSiteIdentifierException.create();
    }

    return data.siteIdentifier;
  }
}

export default new ContextMenuAction();
