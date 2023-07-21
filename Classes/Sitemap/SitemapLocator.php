<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Sitemap;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming\Cache;
use EliasHaeussler\Typo3Warming\Exception;
use EliasHaeussler\Typo3Warming\Utility;
use TYPO3\CMS\Core;

/**
 * SitemapLocator
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class SitemapLocator
{
    /**
     * @param iterable<Provider\Provider> $providers
     * @throws Exception\InvalidProviderException
     */
    public function __construct(
        private readonly Core\Http\RequestFactory $requestFactory,
        private readonly Cache\SitemapsCache $cache,
        private readonly iterable $providers,
    ) {
        $this->validateProviders();
    }

    /**
     * @return list<SiteAwareSitemap>
     * @throws CacheWarmup\Exception\InvalidUrlException
     * @throws Exception\UnsupportedConfigurationException
     * @throws Exception\UnsupportedSiteException
     */
    public function locateBySite(
        Core\Site\Entity\Site $site,
        Core\Site\Entity\SiteLanguage $siteLanguage = null,
    ): array {
        // Get sitemaps from cache
        if (($sitemaps = $this->cache->get($site, $siteLanguage)) !== []) {
            return $sitemaps;
        }

        // Build and validate base URL
        $baseUrl = $siteLanguage !== null ? $siteLanguage->getBase() : $site->getBase();
        if ($baseUrl->getHost() === '') {
            throw Exception\UnsupportedConfigurationException::forBaseUrl((string)$baseUrl);
        }

        // Resolve and validate sitemaps
        $sitemaps = $this->resolveSitemaps($site, $siteLanguage);
        if ($sitemaps === []) {
            throw Exception\UnsupportedSiteException::forMissingSitemap($site);
        }

        // Store resolved sitemaps in cache
        $this->cache->set($sitemaps);

        return $sitemaps;
    }

    /**
     * @return array<int, list<SiteAwareSitemap>>
     * @throws CacheWarmup\Exception\InvalidUrlException
     * @throws Exception\UnsupportedConfigurationException
     * @throws Exception\UnsupportedSiteException
     */
    public function locateAllBySite(Core\Site\Entity\Site $site): array
    {
        $sitemaps = [];

        foreach ($site->getAvailableLanguages(Utility\BackendUtility::getBackendUser()) as $siteLanguage) {
            if ($siteLanguage->isEnabled()) {
                $sitemaps[$siteLanguage->getLanguageId()] = $this->locateBySite($site, $siteLanguage);
            }
        }

        return $sitemaps;
    }

    public function sitemapExists(SiteAwareSitemap $sitemap): bool
    {
        try {
            $response = $this->requestFactory->request((string)$sitemap->getUri(), 'HEAD');

            return $response->getStatusCode() < 400;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * @return list<SiteAwareSitemap>
     */
    private function resolveSitemaps(
        Core\Site\Entity\Site $site,
        Core\Site\Entity\SiteLanguage $siteLanguage = null,
    ): array {
        foreach ($this->providers as $provider) {
            if (($sitemaps = $provider->get($site, $siteLanguage)) !== []) {
                return $sitemaps;
            }
        }

        return [];
    }

    /**
     * @throws Exception\InvalidProviderException
     */
    private function validateProviders(): void
    {
        foreach ($this->providers as $provider) {
            if (!\is_object($provider)) {
                throw Exception\InvalidProviderException::forInvalidType($provider);
            }

            if (!is_a($provider, Provider\Provider::class)) {
                throw Exception\InvalidProviderException::create($provider);
            }
        }
    }
}
