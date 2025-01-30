<?php

declare(strict_types=1);

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

namespace EliasHaeussler\Typo3Warming\Backend\ToolbarItems;

use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Domain;
use EliasHaeussler\Typo3Warming\View;
use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;

/**
 * CacheWarmupToolbarItem
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class CacheWarmupToolbarItem implements Backend\Toolbar\ToolbarItemInterface
{
    public function __construct(
        private readonly Configuration\Configuration $configuration,
        private readonly View\TemplateRenderer $renderer,
        private readonly Domain\Repository\SiteRepository $siteRepository,
        Core\Page\PageRenderer $pageRenderer,
    ) {
        $pageRenderer->loadJavaScriptModule('@eliashaeussler/typo3-warming/backend/toolbar-menu.js');
        $pageRenderer->addInlineLanguageLabelArray([
            // Notification
            'warming.notification.aborted.title' => Configuration\Localization::translate('notification.aborted.title'),
            'warming.notification.aborted.message' => Configuration\Localization::translate('notification.aborted.message'),
            'warming.notification.error.title' => Configuration\Localization::translate('notification.error.title'),
            'warming.notification.error.message' => Configuration\Localization::translate('notification.error.message'),
            'warming.notification.action.showReport' => Configuration\Localization::translate('notification.action.showReport'),
            'warming.notification.action.retry' => Configuration\Localization::translate('notification.action.retry'),
            'warming.notification.noSitesSelected.title' => Configuration\Localization::translate('notification.noSitesSelected.title'),
            'warming.notification.noSitesSelected.message' => Configuration\Localization::translate('notification.noSitesSelected.message'),

            // Progress Modal
            'warming.modal.progress.title' => Configuration\Localization::translate('modal.progress.title'),
            'warming.modal.progress.button.report' => Configuration\Localization::translate('modal.progress.button.report'),
            'warming.modal.progress.button.retry' => Configuration\Localization::translate('modal.progress.button.retry'),
            'warming.modal.progress.button.close' => Configuration\Localization::translate('modal.progress.button.close'),
            'warming.modal.progress.failedCounter' => Configuration\Localization::translate('modal.progress.failedCounter'),
            'warming.modal.progress.allCounter' => Configuration\Localization::translate('modal.progress.allCounter'),
            'warming.modal.progress.placeholder' => Configuration\Localization::translate('modal.progress.placeholder'),

            // Report Modal
            'warming.modal.report.title' => Configuration\Localization::translate('modal.report.title'),
            'warming.modal.report.panel.failed' => Configuration\Localization::translate('modal.report.panel.failed'),
            'warming.modal.report.panel.failed.summary' => Configuration\Localization::translate('modal.report.panel.failed.summary'),
            'warming.modal.report.panel.successful' => Configuration\Localization::translate('modal.report.panel.successful'),
            'warming.modal.report.panel.successful.summary' => Configuration\Localization::translate('modal.report.panel.successful.summary'),
            'warming.modal.report.panel.excluded' => Configuration\Localization::translate('modal.report.panel.excluded'),
            'warming.modal.report.panel.excluded.summary' => Configuration\Localization::translate('modal.report.panel.excluded.summary'),
            'warming.modal.report.panel.excluded.sitemaps' => Configuration\Localization::translate('modal.report.panel.excluded.sitemaps'),
            'warming.modal.report.panel.excluded.urls' => Configuration\Localization::translate('modal.report.panel.excluded.urls'),
            'warming.modal.report.action.view' => Configuration\Localization::translate('modal.report.action.view'),
            'warming.modal.report.message.requestId' => Configuration\Localization::translate('modal.report.message.requestId'),
            'warming.modal.report.message.total' => Configuration\Localization::translate('modal.report.message.total'),
            'warming.modal.report.message.noUrlsCrawled' => Configuration\Localization::translate('modal.report.message.noUrlsCrawled'),

            // Sites Modal
            'warming.modal.sites.title' => Configuration\Localization::translate('modal.sites.title'),
            'warming.modal.sites.userAgent.action.successful' => Configuration\Localization::translate('modal.sites.userAgent.action.successful'),
            'warming.modal.sites.button.start' => Configuration\Localization::translate('modal.sites.button.start'),
        ]);
    }

    public function checkAccess(): bool
    {
        // Early return if cache warmup from backend toolbar is disabled globally
        if (!$this->configuration->isEnabledInToolbar()) {
            return false;
        }

        return $this->siteRepository->countAll() > 0;
    }

    public function getItem(): string
    {
        return $this->renderer->render('Toolbar/CacheWarmupToolbarItem');
    }

    public function hasDropDown(): bool
    {
        return false;
    }

    public function getDropDown(): string
    {
        return '';
    }

    /**
     * @return array<string, string>
     */
    public function getAdditionalAttributes(): array
    {
        return [
            'class' => 'tx-warming-toolbar-item',
        ];
    }

    public function getIndex(): int
    {
        // Clear cache toolbar item has index=25
        return 27;
    }
}
