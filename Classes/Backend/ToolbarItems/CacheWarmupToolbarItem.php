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
            'cacheWarmup.toolbar.copy.successful' => static::translate('toolbar.copy.successful'),
            'cacheWarmup.notification.error.title' => static::translate('notification.error.title'),
            'cacheWarmup.notification.error.message' => static::translate('notification.error.message'),
            'cacheWarmup.notification.action.showReport' => static::translate('notification.action.showReport'),
            'cacheWarmup.modal.title' => static::translate('modal.title'),
            'cacheWarmup.modal.panel.failed' => static::translate('modal.panel.failed'),
            'cacheWarmup.modal.panel.successful' => static::translate('modal.panel.successful'),
            'cacheWarmup.modal.action.view' => static::translate('modal.action.view'),
            'cacheWarmup.modal.total' => static::translate('modal.total'),
            'cacheWarmup.modal.message.noUrlsCrawled' => static::translate('modal.message.noUrlsCrawled'),
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
