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

namespace EliasHaeussler\Typo3Warming\Backend\ContextMenu\ItemProviders;

use EliasHaeussler\Typo3Warming\Sitemap\SitemapLocator;
use EliasHaeussler\Typo3Warming\Traits\BackendUserAuthenticationTrait;
use EliasHaeussler\Typo3Warming\Utility\AccessUtility;
use TYPO3\CMS\Backend\ContextMenu\ItemProviders\PageProvider;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

/**
 * CacheWarmupProvider
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class CacheWarmupProvider extends PageProvider
{
    use BackendUserAuthenticationTrait;

    protected const ITEM_MODE_PAGE = 'cacheWarmupPage';
    protected const ITEM_MODE_SITE = 'cacheWarmupSite';

    /**
     * @var array<string, array>
     */
    protected $itemsConfiguration = [
        'cacheWarmupDivider' => [
            'type' => 'divider',
        ],
        self::ITEM_MODE_PAGE => [
            'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang.xlf:contextMenu.item.cacheWarmup',
            'iconIdentifier' => 'cache-warmup-page',
            'callbackAction' => 'warmupPageCache',
        ],
        self::ITEM_MODE_SITE => [
            'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang.xlf:contextMenu.item.cacheWarmupAll',
            'iconIdentifier' => 'cache-warmup-site',
            'callbackAction' => 'warmupSiteCache',
        ],
    ];

    /**
     * @var SitemapLocator
     */
    protected $sitemapLocator;

    /**
     * @var SiteFinder
     */
    protected $siteFinder;

    public function __construct(string $table, string $identifier, string $context = '')
    {
        parent::__construct($table, $identifier, $context);
        $this->sitemapLocator = GeneralUtility::makeInstance(SitemapLocator::class);
        $this->siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
    }

    protected function canRender(string $itemName, string $type): bool
    {
        // Pseudo items (such as dividers) are always renderable
        if ('item' !== $type) {
            return true;
        }

        if (in_array($itemName, $this->disabledItems, true)) {
            return false;
        }

        // Root page cannot be used for cache warmup since it is not accessible in Frontend
        if ($this->isRoot()) {
            return false;
        }

        // Running cache warmup in "site" mode (= using XML sitemap) is only valid for root pages
        if ($itemName === self::ITEM_MODE_SITE) {
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
        $this->initSubMenus();
        $localItems = $this->prepareItems($this->itemsConfiguration);
        $items += $localItems;

        return $items;
    }

    public function getPriority(): int
    {
        return 50;
    }

    protected function initSubMenus(): void
    {
        $site = $this->getCurrentSite();

        // Early return if site cannot be resolved
        if (null === $site) {
            return;
        }

        foreach ($this->itemsConfiguration as $itemName => $configuration) {
            // Skip pseudo types and non-renderable items
            $type = $configuration['type'] ?? 'item';
            if ('item' !== $type || !$this->canRender($itemName, $type)) {
                continue;
            }

            // Get all languages of current site that are available
            // for the current Backend user
            $languages = $site->getAvailableLanguages(static::getBackendUser());

            // Remove sites where no XML sitemap is available
            if (self::ITEM_MODE_SITE === $itemName) {
                $languages = array_filter($languages, function (SiteLanguage $siteLanguage): bool {
                    return $this->canWarmupCachesOfSite($siteLanguage);
                });
            } else {
                $languages = array_filter($languages, function (SiteLanguage $siteLanguage): bool {
                    return AccessUtility::canWarmupCacheOfPage((int)$this->identifier, $siteLanguage->getLanguageId());
                });
            }

            // Ignore item if no languages are available
            if ([] === $languages) {
                $this->disabledItems[] = $itemName;
                continue;
            }

            // Treat current item as submenu
            $this->itemsConfiguration[$itemName]['type'] = 'submenu';
            $this->itemsConfiguration[$itemName]['childItems'] = [];

            // Add each site language as child element of the current item
            foreach ($languages as $language) {
                $this->itemsConfiguration[$itemName]['childItems']['lang_' . $language->getLanguageId()] = [
                    'label' => $language->getTitle(),
                    'iconIdentifier' => $language->getFlagIdentifier(),
                    'callbackAction' => $this->itemsConfiguration[$itemName]['callbackAction'],
                ];
            }

            // Callback action is not required on the parent item
            unset($this->itemsConfiguration[$itemName]['callbackAction']);
        }
    }

    /**
     * @param string $itemName
     * @return array<string, mixed>
     */
    protected function getAdditionalAttributes(string $itemName): array
    {
        $attributes = [
            'data-callback-module' => 'TYPO3/CMS/Warming/Backend/ContextMenu/CacheWarmupContextMenuAction',
        ];

        // Add language ID as data attribute if current item is part
        // of a submenu within the configured context menu items
        if (StringUtility::beginsWith($itemName, 'lang_')) {
            $attributes['data-language-id'] = (int)substr($itemName, 5);
        }

        return $attributes;
    }

    protected function canWarmupCachesOfSite(SiteLanguage $siteLanguage = null): bool
    {
        $site = $this->getCurrentSite();
        $languageId = null !== $siteLanguage ? $siteLanguage->getLanguageId() : null;

        return null !== $site
            && $site->getRootPageId() === (int)$this->identifier
            && AccessUtility::canWarmupCacheOfSite($site, $languageId)
            && $this->sitemapLocator->siteContainsSitemap($site, $siteLanguage);
    }

    protected function getCurrentSite(): ?Site
    {
        try {
            return $this->siteFinder->getSiteByPageId((int)$this->identifier);
        } catch (SiteNotFoundException $e) {
            return null;
        }
    }
}
