<?php

declare(strict_types=1);

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

namespace EliasHaeussler\Typo3CacheWarmup\Backend\ContextMenu\ItemProviders;

use EliasHaeussler\Typo3CacheWarmup\Sitemap\SitemapLocator;
use EliasHaeussler\Typo3CacheWarmup\Utility\AccessUtility;
use TYPO3\CMS\Backend\ContextMenu\ItemProviders\PageProvider;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * CacheWarmupProvider
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class CacheWarmupProvider extends PageProvider
{
    /**
     * @var array<string, array>
     */
    protected $itemsConfiguration = [
        'cacheWarmupPage' => [
            'label' => 'LLL:EXT:cache_warmup/Resources/Private/Language/locallang.xlf:contextMenu.item.cacheWarmup',
            'iconIdentifier' => 'cache-warmup-page',
            'callbackAction' => 'warmupPageCache',
        ],
        'cacheWarmupSite' => [
            'label' => 'LLL:EXT:cache_warmup/Resources/Private/Language/locallang.xlf:contextMenu.item.cacheWarmupAll',
            'iconIdentifier' => 'cache-warmup-site',
            'callbackAction' => 'warmupSiteCache',
        ],
    ];

    protected function canRender(string $itemName, string $type): bool
    {
        if (in_array($itemName, $this->disabledItems, true)) {
            return false;
        }

        // Root page cannot be used for cache warmup since it is not accessible in Frontend
        if ($this->isRoot()) {
            return false;
        }

        // Running cache warmup in "site" mode (= using XML sitemap) is only valid for root pages
        if ($itemName === 'cacheWarmupSite') {
            return $this->canWarmupCachesOfSite();
        }

        return AccessUtility::canWarmupCacheOfPage((int)$this->identifier);
    }

    /**
     * @param array<string, array> $items
     * @return array<string, array>
     */
    public function addItems(array $items): array
    {
        $this->initDisabledItems();
        $localItems = $this->prepareItems($this->itemsConfiguration);
        $items += $localItems;

        return $items;
    }

    public function getPriority(): int
    {
        return 50;
    }

    /**
     * @param string $itemName
     * @return array<string, string>
     */
    protected function getAdditionalAttributes(string $itemName): array
    {
        return [
            'data-callback-module' => 'TYPO3/CMS/CacheWarmup/Backend/ContextMenu/CacheWarmupContextMenuAction',
        ];
    }

    protected function canWarmupCachesOfSite(): bool
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $sitemapLocator = GeneralUtility::makeInstance(SitemapLocator::class);

        try {
            $site = $siteFinder->getSiteByPageId((int)$this->identifier);

            return $site->getRootPageId() === (int)$this->identifier
                && AccessUtility::canWarmupCacheOfSite($site)
                && $sitemapLocator->siteContainsSitemap($site);
        } catch (SiteNotFoundException $e) {
            return false;
        }
    }
}
