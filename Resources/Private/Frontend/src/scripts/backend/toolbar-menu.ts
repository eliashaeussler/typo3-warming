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

import DocumentService from '@typo3/core/document-service.js';
import RegularEvent from '@typo3/core/event/regular-event.js';

import {SitesModal} from '@eliashaeussler/typo3-warming/backend/modal/sites-modal';

enum CacheWarmupMenuSelectors {
  container = '#eliashaeussler-typo3warming-backend-toolbaritems-cachewarmuptoolbaritem',
}

/**
 * Handle cache warmup from the Backend toolbar.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class ToolbarMenu {
  constructor() {
    DocumentService.ready().then((): void => {
      new RegularEvent('click', (): void => {
        new SitesModal();
      }).delegateTo(document, CacheWarmupMenuSelectors.container);
    });
  }
}

export default new ToolbarMenu();
