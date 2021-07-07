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

/**
 * Several language keys that are used in custom modules.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
enum LanguageKeys {
  // Toolbar
  toolbarCopySuccessful = 'cacheWarmup.toolbar.copy.successful',

  // Notification
  notificationShowReport = 'cacheWarmup.notification.action.showReport',
  notificationErrorTitle = 'cacheWarmup.notification.error.title',
  notificationErrorMessage = 'cacheWarmup.notification.error.message',

  // Report Modal
  modalReportTitle = 'cacheWarmup.modal.report.title',
  modalReportPanelFailed = 'cacheWarmup.modal.report.panel.failed',
  modalReportPanelSuccessful = 'cacheWarmup.modal.report.panel.successful',
  modalReportActionView = 'cacheWarmup.modal.report.action.view',
  modalReportTotal = 'cacheWarmup.modal.report.message.total',
  modalReportNoUrlsCrawled = 'cacheWarmup.modal.report.message.noUrlsCrawled',

  // Progress Modal
  modalProgressTitle = 'cacheWarmup.modal.progress.title',
  modalProgressButtonReport = 'cacheWarmup.modal.progress.button.report',
  modalProgressButtonRetry = 'cacheWarmup.modal.progress.button.retry',
  modalProgressButtonClose = 'cacheWarmup.modal.progress.button.close',
  modalProgressFailedCounter = 'cacheWarmup.modal.progress.failedCounter',
  modalProgressAllCounter = 'cacheWarmup.modal.progress.allCounter',
  modalProgressPlaceholder = 'cacheWarmup.modal.progress.placeholder',
}

export default LanguageKeys;
