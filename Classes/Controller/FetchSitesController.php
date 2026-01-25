<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2026 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Domain;
use EliasHaeussler\Typo3Warming\Http;
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
final readonly class FetchSitesController
{
    public function __construct(
        private Configuration\Configuration $configuration,
        private CacheWarmup\Crawler\Strategy\CrawlingStrategyFactory $crawlingStrategyFactory,
        private Core\Imaging\IconFactory $iconFactory,
        private Http\Message\Request\RequestOptions $requestOptions,
        private Http\Message\ResponseFactory $responseFactory,
        private Domain\Repository\SiteRepository $siteRepository,
        private Domain\Repository\SiteLanguageRepository $siteLanguageRepository,
        private Typo3SitemapLocator\Sitemap\SitemapLocator $sitemapLocator,
    ) {}

    /**
     * @throws Typo3SitemapLocator\Exception\BaseUrlIsNotSupported
     * @throws Typo3SitemapLocator\Exception\SitemapIsMissing
     */
    public function __invoke(Message\ServerRequestInterface $request): Message\ResponseInterface
    {
        $limitToSite = $request->getQueryParams()['limitToSite'] ?? null;
        $crawlingStrategy = $this->configuration->crawlingStrategy;
        $siteGroups = [];

        if (\is_string($limitToSite) && $limitToSite !== '') {
            $site = $this->siteRepository->findOneByIdentifier($limitToSite);

            if ($site !== null) {
                $sites = [$site];
            } else {
                $sites = [];
            }
        } else {
            $sites = $this->siteRepository->findAll();
        }

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
            'userAgent' => $this->requestOptions->getUserAgent(),
            'configuration' => [
                'limit' => $this->configuration->limit,
                'strategy' => $crawlingStrategy !== null ? $crawlingStrategy::getName() : null,
            ],
            'availableStrategies' => $this->crawlingStrategyFactory->getAll(),
            'isAdmin' => Utility\BackendUtility::getBackendUser()->isAdmin(),
            'isFiltered' => $limitToSite !== null,
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
        foreach ($this->sitemapLocator->locateAllBySite($site) as $sitemaps) {
            foreach ($sitemaps as $sitemap) {
                $siteLanguage = $this->siteLanguageRepository->findOneByLanguageId(
                    $sitemap->getSite(),
                    $sitemap->getSiteLanguage()->getLanguageId(),
                );

                // Skip sitemap if site language is inaccessible
                if ($siteLanguage === null) {
                    continue;
                }

                // Check if sitemap exists
                if ($this->sitemapLocator->isValidSitemap($sitemap)) {
                    $url = (string)$sitemap->getUri();
                } else {
                    $url = null;
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
