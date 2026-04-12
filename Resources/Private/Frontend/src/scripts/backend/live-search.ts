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

import LiveSearchConfigurator from '@typo3/backend/live-search/live-search-configurator.js';

import {CacheWarmer, PageWarmupRequest} from '@eliashaeussler/typo3-warming/cache-warmer';
import {SitesModal} from '@eliashaeussler/typo3-warming/backend/modal/sites-modal';

type ResultItem = {
  extraData: {
    languageId?: number,
    pageId?: number,
    siteIdentifier?: string,
  },
};

/**
 * Handle cache warmup from the Live Search result modal.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class LiveSearch {
  constructor() {
    LiveSearchConfigurator.addInvokeHandler(
      'TYPO3\\CMS\\Backend\\Search\\LiveSearch\\PageRecordProvider',
      'warmupPageCache',
      this.warmupPageCache,
    );
    LiveSearchConfigurator.addInvokeHandler(
      'TYPO3\\CMS\\Backend\\Search\\LiveSearch\\PageRecordProvider',
      'warmupSiteCache',
      this.warmupSiteCache,
    );
  }

  private warmupPageCache(resultItem: ResultItem): void {
    const {pageId, languageId} = resultItem.extraData;

    if (typeof pageId === 'number' && typeof languageId === 'number') {
      const pages: PageWarmupRequest = {};
      pages[pageId] = [languageId];

      (new CacheWarmer()).warmupCache({}, pages);
    }
  }

  private warmupSiteCache(resultItem: ResultItem): void {
    const {languageId, siteIdentifier} = resultItem.extraData;

    SitesModal.createModal(siteIdentifier, typeof languageId === 'number' ? languageId : null);
  }
}

export default new LiveSearch();
