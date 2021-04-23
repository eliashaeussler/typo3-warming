/*
 * This file is part of the TYPO3 CMS extension "cache_warmup".
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
  'jquery',
  'TYPO3/CMS/Core/Ajax/AjaxRequest',
  'TYPO3/CMS/Backend/Icons',
  'TYPO3/CMS/Backend/Notification',
  'TYPO3/CMS/Backend/Viewport',
], function ($, AjaxRequest, Icons, Notification, Viewport)
{
  'use strict';

  const CacheWarmupMenu = {
    containerSelector: '#eliashaeussler-typo3cachewarmup-backend-toolbaritems-cachewarmuptoolbaritem',
    menuItemSelector: 'a.toolbar-cache-warmup-action',
    toolbarIconSelector: '.toolbar-item-icon .t3js-icon',
    notificationDuration: 15,
  }, _ = CacheWarmupMenu;

  CacheWarmupMenu.initializeEvents = function ()
  {
    $(_.containerSelector).on('click', _.menuItemSelector, function (event) {
      event.preventDefault();
      const pageId = $(event.currentTarget).attr('data-page-id');
      if (pageId) {
        _.warmupCache(pageId);
      }
    });
  };

  CacheWarmupMenu.warmupCache = function (pageId, mode = 'site')
  {
    // Close dropdown menu
    $(_.containerSelector).removeClass('open');

    const $toolbarItemIcon = $(_.toolbarIconSelector, _.containerSelector);
    const $existingIcon = $toolbarItemIcon.clone();

    Icons.getIcon('spinner-circle-light', Icons.sizes.small).then(function (spinner) {
      $toolbarItemIcon.replaceWith(spinner);
    });

    (new AjaxRequest(TYPO3.settings.ajaxUrls.cache_warmup))
      .withQueryArguments({pageId: pageId, mode: mode})
      .post({})
      .then(
        async function (response) {
          const data = await response.resolve();
          switch (data.state) {
            case 'failed':
              Notification.error(data.title, data.message, _.notificationDuration);
              break;
            case 'warning':
              Notification.warning(data.title, data.message, _.notificationDuration);
              break;
            case 'success':
              Notification.success(data.title, data.message, _.notificationDuration);
              break;
            case 'unknown':
              Notification.notice(data.title, data.message, _.notificationDuration);
              break;
            default:
              Notification.error(TYPO3.lang['cacheWarmup.error.title'], TYPO3.lang['cacheWarmup.error.message']);
              break;
          }
        },
        function () {
          Notification.error(TYPO3.lang['cacheWarmup.error.title'], TYPO3.lang['cacheWarmup.error.message']);
        }
      )
      .finally(function () {
        $(_.toolbarIconSelector, _.containerSelector).replaceWith($existingIcon);
      });
  };

  Viewport.Topbar.Toolbar.registerEvent(_.initializeEvents);

  return CacheWarmupMenu;
});
