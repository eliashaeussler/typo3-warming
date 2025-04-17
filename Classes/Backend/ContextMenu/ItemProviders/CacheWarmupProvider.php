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

namespace EliasHaeussler\Typo3Warming\Backend\ContextMenu\ItemProviders;

use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Domain;
use EliasHaeussler\Typo3Warming\Security;
use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;

/**
 * CacheWarmupProvider
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class CacheWarmupProvider extends Backend\ContextMenu\ItemProviders\PageProvider
{
    private const ITEM_MODE_PAGE = 'cacheWarmupPage';
    private const ITEM_MODE_SITE = 'cacheWarmupSite';

    /**
     * @var array<string, array{
     *     type: string
     * }|array{
     *     type: string,
     *     label: string,
     *     iconIdentifier: string,
     *     callbackAction: string,
     *     childItems?: array<string, array{
     *         label?: string,
     *         iconIdentifier?: string,
     *         callbackAction?: string
     *     }>
     * }>
     */
    protected $itemsConfiguration = [
        'cacheWarmupDivider' => [
            'type' => 'divider',
        ],
        self::ITEM_MODE_PAGE => [
            'type' => 'item',
            'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang.xlf:contextMenu.item.cacheWarmup',
            'iconIdentifier' => 'cache-warmup-page',
            'callbackAction' => 'warmupPageCache',
        ],
        self::ITEM_MODE_SITE => [
            'type' => 'item',
            'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang.xlf:contextMenu.item.cacheWarmupAll',
            'iconIdentifier' => 'cache-warmup-site',
            'callbackAction' => 'warmupSiteCache',
        ],
    ];

    public function __construct(
        private readonly Typo3SitemapLocator\Sitemap\SitemapLocator $sitemapLocator,
        private readonly Domain\Repository\SiteRepository $siteRepository,
        private readonly Domain\Repository\SiteLanguageRepository $siteLanguageRepository,
        private readonly Configuration\Configuration $configuration,
        private readonly Security\WarmupPermissionGuard $permissionGuard,
    ) {
        parent::__construct();
    }

    protected function canRender(string $itemName, string $type): bool
    {
        // Early return if cache warmup from page tree is disabled globally
        if (!$this->configuration->isEnabledInPageTree()) {
            return false;
        }

        // Pseudo items (such as dividers) are always renderable
        if ($type !== 'item') {
            return true;
        }

        // Non-supported doktypes are never renderable
        $doktype = (int)($this->record['doktype'] ?? null);
        if ($doktype <= 0 || !\in_array($doktype, $this->configuration->getSupportedDoktypes(), true)) {
            return false;
        }

        // Language items in sub-menus are already filtered
        if (str_contains($itemName, '_lang_')) {
            return true;
        }

        if (\in_array($itemName, $this->disabledItems, true)) {
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

        return $this->permissionGuard->canWarmupCacheOfPage((int)$this->identifier);
    }

    /**
     * @param array<string, array<string, mixed>> $items
     * @return array<string, array<string, mixed>>
     */
    public function addItems(array $items): array
    {
        $this->initialize();

        $localItems = $this->prepareItems($this->itemsConfiguration);
        $items += $localItems;

        return $items;
    }

    protected function initialize(): void
    {
        parent::initialize();
        $this->initSubMenus();
    }

    public function getPriority(): int
    {
        return 45;
    }

    private function initSubMenus(): void
    {
        $site = $this->getCurrentSite();

        // Early return if site cannot be resolved
        if ($site === null) {
            return;
        }

        foreach ($this->itemsConfiguration as $itemName => &$configuration) {
            // Skip pseudo types and non-renderable items
            $type = $configuration['type'];
            if ($type !== 'item' || !$this->canRender($itemName, $type)) {
                continue;
            }

            // Get all languages of current site that are available for the current backend user
            $languages = $this->siteLanguageRepository->findAll($site);

            // Remove sites where no XML sitemap is available
            if ($itemName === self::ITEM_MODE_SITE) {
                $languages = array_filter(
                    $languages,
                    fn(Core\Site\Entity\SiteLanguage $siteLanguage): bool => $this->canWarmupCachesOfSite($siteLanguage),
                );
            } else {
                $languages = array_filter(
                    $languages,
                    fn(Core\Site\Entity\SiteLanguage $siteLanguage): bool => $this->permissionGuard->canWarmupCacheOfPage(
                        (int)$this->identifier,
                        new Security\Context\PermissionContext($siteLanguage->getLanguageId()),
                    ),
                );
            }

            // Ignore item if no languages are available
            if ($languages === []) {
                $this->disabledItems[] = $itemName;
                continue;
            }

            // Treat current item as submenu
            $configuration['type'] = 'submenu';
            $configuration['childItems'] = [];

            // Add each site language as child element of the current item
            foreach ($languages as $language) {
                $configuration['childItems'][$itemName . '_lang_' . $language->getLanguageId()] = [
                    'label' => $language->getTitle(),
                    'iconIdentifier' => $language->getFlagIdentifier(),
                    'callbackAction' => $configuration['callbackAction'] ?? null,
                ];
            }

            // Callback action is not required on the parent item
            unset($configuration['callbackAction']);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getAdditionalAttributes(string $itemName): array
    {
        $attributes = [
            'data-callback-module' => '@eliashaeussler/typo3-warming/backend/context-menu-action',
        ];

        // Early return if current item is not part of a submenu
        // within the configured context menu items
        if (!str_contains($itemName, '_lang_')) {
            return $attributes;
        }

        [$parentItem, $languageId] = explode('_lang_', $itemName);

        // Add site identifier as data attribute
        if ($parentItem === self::ITEM_MODE_SITE) {
            $attributes['data-site-identifier'] = $this->getCurrentSite()?->getIdentifier();
        }

        // Add language ID as data attribute
        $attributes['data-language-id'] = (int)$languageId;

        return $attributes;
    }

    private function canWarmupCachesOfSite(?Core\Site\Entity\SiteLanguage $siteLanguage = null): bool
    {
        $site = $this->getCurrentSite();

        // Skip item if we're not in site context or resolved site is unexpected
        if ($site === null || $site->getRootPageId() !== (int)$this->identifier) {
            return false;
        }

        // Check if any sitemap exists
        try {
            foreach ($this->sitemapLocator->locateBySite($site, $siteLanguage) as $sitemap) {
                if ($this->sitemapLocator->isValidSitemap($sitemap)) {
                    return true;
                }
            }
        } catch (\Exception) {
            // Unable to locate any sitemaps
        }

        return false;
    }

    private function getCurrentSite(): ?Core\Site\Entity\Site
    {
        /** @var positive-int $pageId */
        $pageId = (int)$this->identifier;

        return $this->siteRepository->findOneByPageId($pageId);
    }
}
