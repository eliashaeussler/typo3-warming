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

import {CacheWarmer, PageWarmupRequest, SiteWarmupRequest} from '@eliashaeussler/typo3-warming/cache-warmer';
import {MissingSiteIdentifierException} from '@eliashaeussler/typo3-warming/exception/missing-site-identifier-exception';
import {SitesModal} from '@eliashaeussler/typo3-warming/backend/modal/sites-modal';

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
      const actionIdentifier: number | null = ContextMenuAction.determineActionIdentifier(data) as number | null;

      const pages: PageWarmupRequest = {};
      pages[uid] = [actionIdentifier];

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
      const actionIdentifier: string | number = ContextMenuAction.determineActionIdentifier(data);
      const siteIdentifier: string = ContextMenuAction.determineSiteIdentifier(data);

      if (typeof actionIdentifier === 'number') {
        // Run cache warmup if action identifier is a specific language id
        const sites: SiteWarmupRequest = {};
        sites[siteIdentifier] = [actionIdentifier];

        (new CacheWarmer()).warmupCache(sites, {});
      } else {
        // Show sites modal to select language(s) for given site
        SitesModal.createModal(siteIdentifier);
      }
    }
  }

  /**
   * Determine requested action from context menu action.
   *
   * Tests whether an action identifier is defined in the current context menu
   * action and returns it, otherwise `NULL` is returned. The action identifier
   * is defined as `data-action-identifier` attribute in the context menu action.
   *
   * @param data {object} Additional data attributes from the original context menu item
   * @returns {string|null} The resolved action identifier or `NULL`
   * @private
   */
  private static determineActionIdentifier(data: object): string | number | null {
    if (!('actionIdentifier' in data) || typeof data.actionIdentifier !== 'string') {
      return null;
    }

    const actionIdentifier: string = data.actionIdentifier;

    // Return integer if action identifier is a language ID
    if (!isNaN(parseInt(actionIdentifier))) {
      return parseInt(actionIdentifier);
    }

    return actionIdentifier;
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
