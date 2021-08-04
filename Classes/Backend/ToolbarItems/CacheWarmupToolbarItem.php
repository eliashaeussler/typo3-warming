<?php

declare(strict_types=1);

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

namespace EliasHaeussler\Typo3Warming\Backend\ToolbarItems;

use EliasHaeussler\Typo3Warming\Configuration\Configuration;
use EliasHaeussler\Typo3Warming\Traits\TranslatableTrait;
use EliasHaeussler\Typo3Warming\Traits\ViewTrait;
use EliasHaeussler\Typo3Warming\Utility\AccessUtility;
use TYPO3\CMS\Backend\Toolbar\ToolbarItemInterface;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * CacheWarmupToolbarItem
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class CacheWarmupToolbarItem implements ToolbarItemInterface
{
    use TranslatableTrait;
    use ViewTrait;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var SiteFinder
     */
    protected $siteFinder;

    public function __construct(Configuration $configuration, SiteFinder $siteFinder, PageRenderer $pageRenderer)
    {
        $this->configuration = $configuration;
        $this->siteFinder = $siteFinder;

        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Warming/Backend/Toolbar/CacheWarmupMenu');
        $pageRenderer->addInlineLanguageLabelArray([
            // Toolbar
            'cacheWarmup.toolbar.sitemap.missing' => static::translate('toolbar.sitemap.missing'),
            'cacheWarmup.toolbar.sitemap.placeholder' => static::translate('toolbar.sitemap.placeholder'),
            'cacheWarmup.toolbar.copy.successful' => static::translate('toolbar.copy.successful'),

            // Notification
            'cacheWarmup.notification.aborted.title' => static::translate('notification.aborted.title'),
            'cacheWarmup.notification.aborted.message' => static::translate('notification.aborted.message'),
            'cacheWarmup.notification.error.title' => static::translate('notification.error.title'),
            'cacheWarmup.notification.error.message' => static::translate('notification.error.message'),
            'cacheWarmup.notification.action.showReport' => static::translate('notification.action.showReport'),
            'cacheWarmup.notification.action.retry' => static::translate('notification.action.retry'),

            // Report Modal
            'cacheWarmup.modal.report.title' => static::translate('modal.report.title'),
            'cacheWarmup.modal.report.panel.failed' => static::translate('modal.report.panel.failed'),
            'cacheWarmup.modal.report.panel.successful' => static::translate('modal.report.panel.successful'),
            'cacheWarmup.modal.report.action.view' => static::translate('modal.report.action.view'),
            'cacheWarmup.modal.report.message.total' => static::translate('modal.report.message.total'),
            'cacheWarmup.modal.report.message.noUrlsCrawled' => static::translate('modal.report.message.noUrlsCrawled'),

            // Progress Modal
            'cacheWarmup.modal.progress.title' => static::translate('modal.progress.title'),
            'cacheWarmup.modal.progress.button.report' => static::translate('modal.progress.button.report'),
            'cacheWarmup.modal.progress.button.retry' => static::translate('modal.progress.button.retry'),
            'cacheWarmup.modal.progress.button.close' => static::translate('modal.progress.button.close'),
            'cacheWarmup.modal.progress.failedCounter' => static::translate('modal.progress.failedCounter'),
            'cacheWarmup.modal.progress.allCounter' => static::translate('modal.progress.allCounter'),
            'cacheWarmup.modal.progress.placeholder' => static::translate('modal.progress.placeholder'),
        ]);
    }

    public function checkAccess(): bool
    {
        foreach ($this->siteFinder->getAllSites() as $site) {
            if (AccessUtility::canWarmupCacheOfSite($site)) {
                return true;
            }
        }

        return false;
    }

    public function getItem(): string
    {
        return $this->buildView('CacheWarmupToolbarItem.html')->render();
    }

    public function hasDropDown(): bool
    {
        return true;
    }

    public function getDropDown(): string
    {
        $view = $this->buildView('CacheWarmupToolbarItemDropDown.html');
        $view->assign('userAgent', $this->configuration->getUserAgent());

        return $view->render();
    }

    /**
     * @return array<string, string>
     */
    public function getAdditionalAttributes(): array
    {
        return [];
    }

    public function getIndex(): int
    {
        // Clear cache toolbar item has index=25
        return 27;
    }
}
