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

namespace EliasHaeussler\Typo3Warming\Controller;

use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Crawler;
use EliasHaeussler\Typo3Warming\Http;
use EliasHaeussler\Typo3Warming\Security;
use EliasHaeussler\Typo3Warming\Utility;
use EliasHaeussler\Typo3Warming\ValueObject;
use Psr\Http\Message;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;

/**
 * FetchSitesController
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\Autoconfigure(public: true)]
final class FetchSitesController
{
    public function __construct(
        private readonly Configuration\Configuration $configuration,
        private readonly Crawler\Strategy\CrawlingStrategyFactory $crawlingStrategyFactory,
        private readonly Core\Imaging\IconFactory $iconFactory,
        private readonly Http\Message\ResponseFactory $responseFactory,
        private readonly Core\Site\SiteFinder $siteFinder,
        private readonly Typo3SitemapLocator\Sitemap\SitemapLocator $sitemapLocator,
        private readonly Security\WarmupPermissionGuard $accessGuard,
    ) {}

    /**
     * @throws Typo3SitemapLocator\Exception\BaseUrlIsNotSupported
     * @throws Typo3SitemapLocator\Exception\SitemapIsMissing
     */
    public function __invoke(): Message\ResponseInterface
    {
        $siteGroups = [];
        $sites = array_filter(
            $this->siteFinder->getAllSites(),
            fn(Core\Site\Entity\Site $site) => $this->accessGuard->canWarmupCacheOfSite(
                $site,
                Security\Context\PermissionContext::forCurrentBackendUser(),
            ),
        );

        foreach ($sites as $site) {
            $row = Backend\Utility\BackendUtility::getRecord('pages', $site->getRootPageId(), '*', ' AND hidden = 0');

            if (!\is_array($row)) {
                continue;
            }

            $siteGroup = $this->createSiteGroup($site, $row);

            if ($siteGroup !== null) {
                $siteGroups[] = $siteGroup;
            }
        }

        return $this->responseFactory->htmlTemplate('Modal/SitesModal', [
            'siteGroups' => $siteGroups,
            'userAgent' => $this->configuration->getUserAgent(),
            'configuration' => [
                'limit' => $this->configuration->getLimit(),
                'strategy' => $this->configuration->getStrategy(),
            ],
            'availableStrategies' => array_keys($this->crawlingStrategyFactory->getAll()),
            'isAdmin' => Utility\BackendUtility::getBackendUser()->isAdmin(),
        ]);
    }

    /**
     * @param array<string, mixed> $page
     * @throws Typo3SitemapLocator\Exception\BaseUrlIsNotSupported
     * @throws Typo3SitemapLocator\Exception\SitemapIsMissing
     */
    private function createSiteGroup(Core\Site\Entity\Site $site, array $page): ?ValueObject\Modal\SiteGroup
    {
        $items = [];

        // Check all available languages for possible sitemaps
        foreach ($this->sitemapLocator->locateAllBySite($site) as $i => $sitemaps) {
            foreach ($sitemaps as $sitemap) {
                $siteLanguage = $sitemap->getSiteLanguage();
                $url = null;

                // Check if sitemap exists
                if ($this->sitemapLocator->isValidSitemap($sitemap)) {
                    $url = (string)$sitemap->getUri();
                }

                $items[] = new ValueObject\Modal\SiteGroupItem(
                    $siteLanguage,
                    $siteLanguage === $site->getDefaultLanguage(),
                    $url,
                );
            }
        }

        // Early return if no languages are available
        if ($items === []) {
            return null;
        }

        return new ValueObject\Modal\SiteGroup(
            $site,
            $this->resolvePageTitle($site, $page),
            $this->iconFactory->getIconForRecord('pages', $page)->getIdentifier(),
            $items,
        );
    }

    /**
     * @param array<string, mixed> $page
     */
    private function resolvePageTitle(Core\Site\Entity\Site $site, array $page): string
    {
        $websiteTitle = $site->getConfiguration()['websiteTitle'] ?? null;

        if (\is_string($websiteTitle) && trim($websiteTitle) !== '') {
            return $websiteTitle;
        }

        return Backend\Utility\BackendUtility::getRecordTitle('pages', $page);
    }
}
