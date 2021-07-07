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

import WarmupRequestMode from '../../../lib/Enums/WarmupRequestMode';

// Modules
import CacheWarmupMenu from '../Toolbar/CacheWarmupMenu';

/**
 * AMD module that allows running cache warmup from the SVG tree context menu.
 *
 * Module: TYPO3/CMS/Warming/Backend/ContextMenu/CacheWarmupContextMenuAction
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class CacheWarmupContextMenuAction {
  /**
   * Trigger cache warmup for a specific page, identified by the given UID.
   *
   * @param table {string} Table name associated to the triggered SVG tree
   * @param uid {number} UID of the associated element within the triggered SVG tree
   */
  public static warmupPageCache(table: string, uid: number): void {
    if ('pages' === table) {
      CacheWarmupMenu.warmupCache(uid, WarmupRequestMode.Page);
    }
  }

  /**
   * Trigger cache warmup for a specific site, identified by the given UID.
   *
   * @param table {string} Table name associated to the triggered SVG tree
   * @param uid {number} UID of the associated element within the triggered SVG tree
   */
  public static warmupSiteCache(table: string, uid: number): void {
    if ('pages' === table) {
      CacheWarmupMenu.warmupCache(uid, WarmupRequestMode.Site);
    }
  }
}

export default new CacheWarmupContextMenuAction();

// We need to export the static methods separately to ensure those functions
// can be properly triggered by ContextMenu.ts from sysext EXT:backend, see
// https://github.com/TYPO3/TYPO3.CMS/blob/bb831f2272815cae672dd382161f0bb9e6123b8e/Build/Sources/TypeScript/backend/Resources/Public/TypeScript/ContextMenu.ts#L200
export const warmupPageCache = CacheWarmupContextMenuAction.warmupPageCache;
export const warmupSiteCache = CacheWarmupContextMenuAction.warmupSiteCache;
