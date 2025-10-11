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

/**
 * Several language keys that are used in custom modules.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
export enum LanguageKeys {
  // Notification
  notificationShowReport = 'warming.notification.action.showReport',
  notificationActionRetry = 'warming.notification.action.retry',
  notificationAbortedTitle = 'warming.notification.aborted.title',
  notificationAbortedMessage = 'warming.notification.aborted.message',
  notificationErrorTitle = 'warming.notification.error.title',
  notificationErrorMessage = 'warming.notification.error.message',
  notificationNoSitesSelectedTitle = 'warming.notification.noSitesSelected.title',
  notificationNoSitesSelectedMessage = 'warming.notification.noSitesSelected.message',

  // Progress Modal
  modalProgressTitle = 'warming.modal.progress.title',
  modalProgressFailedTitle = 'warming.modal.progress.title.failed',
  modalProgressWarningTitle = 'warming.modal.progress.title.warning',
  modalProgressSuccessTitle = 'warming.modal.progress.title.success',
  modalProgressAbortedTitle = 'warming.modal.progress.title.aborted',
  modalProgressUnknownTitle = 'warming.modal.progress.title.unknown',
  modalProgressButtonReport = 'warming.modal.progress.button.report',
  modalProgressButtonRetry = 'warming.modal.progress.button.retry',
  modalProgressButtonClose = 'warming.modal.progress.button.close',
  modalProgressFailedCounter = 'warming.modal.progress.failedCounter',
  modalProgressAllCounter = 'warming.modal.progress.allCounter',
  modalProgressPlaceholder = 'warming.modal.progress.placeholder',

  // Report Modal
  modalReportTitle = 'warming.modal.report.title',
  modalReportPanelFailed = 'warming.modal.report.panel.failed',
  modalReportPanelFailedSummary = 'warming.modal.report.panel.failed.summary',
  modalReportPanelSuccessful = 'warming.modal.report.panel.successful',
  modalReportPanelSuccessfulSummary = 'warming.modal.report.panel.successful.summary',
  modalReportPanelExcluded = 'warming.modal.report.panel.excluded',
  modalReportPanelExcludedSummary = 'warming.modal.report.panel.excluded.summary',
  modalReportPanelExcludedSitemaps = 'warming.modal.report.panel.excluded.sitemaps',
  modalReportPanelExcludedUrls = 'warming.modal.report.panel.excluded.urls',
  modalReportActionEdit = 'warming.modal.report.action.edit',
  modalReportActionInfo = 'warming.modal.report.action.info',
  modalReportActionLog = 'warming.modal.report.action.log',
  modalReportActionView = 'warming.modal.report.action.view',
  modalReportRequestId = 'warming.modal.report.message.requestId',
  modalReportTotal = 'warming.modal.report.message.total',
  modalReportNoUrlsCrawled = 'warming.modal.report.message.noUrlsCrawled',

  // Sites Modal
  modalSitesTitle = 'warming.modal.sites.title',
  modalSitesUserAgentActionSuccessful = 'warming.modal.sites.userAgent.action.successful',
  modalSitesButtonStart = 'warming.modal.sites.button.start',
}
