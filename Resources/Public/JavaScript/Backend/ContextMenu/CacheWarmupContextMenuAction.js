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

define([
  'TYPO3/CMS/Warming/Backend/Toolbar/CacheWarmupMenu',
], function (CacheWarmupMenu)
{
  'use strict';

  const CacheWarmupContextMenuAction = {};

  CacheWarmupContextMenuAction.warmupPageCache = function (table, uid) {
    if (table === 'pages') {
      CacheWarmupMenu.warmupCache(uid, CacheWarmupMenu.modes.page);
    }
  };

  CacheWarmupContextMenuAction.warmupSiteCache = function (table, uid) {
    if (table === 'pages') {
      CacheWarmupMenu.warmupCache(uid, CacheWarmupMenu.modes.site);
    }
  };

  return CacheWarmupContextMenuAction;
});
