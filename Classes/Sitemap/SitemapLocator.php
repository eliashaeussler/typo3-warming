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
     * @throws CacheWarmup\Exception\InvalidUrlException
     * @throws Exception\UnsupportedConfigurationException
     * @throws Exception\UnsupportedSiteException
     */
    public function locateBySite(
        Core\Site\Entity\Site $site,
        Core\Site\Entity\SiteLanguage $siteLanguage = null,
    ): SiteAwareSitemap {
        // Get sitemap from cache
        if (($sitemap = $this->cache->get($site, $siteLanguage)) !== null) {
            return $sitemap;
        }

        // Build and validate base URL
        $baseUrl = $siteLanguage !== null ? $siteLanguage->getBase() : $site->getBase();
        if ($baseUrl->getHost() === '') {
            throw Exception\UnsupportedConfigurationException::forBaseUrl((string)$baseUrl);
        }

        // Resolve and validate sitemap
        $sitemap = $this->resolveSitemap($site, $siteLanguage);
        if ($sitemap === null) {
            throw Exception\UnsupportedSiteException::forMissingSitemap($site);
        }

        // Store resolved sitemap in cache
        $this->cache->set($sitemap);

        return $sitemap;
    }

    /**
     * @return array<int, SiteAwareSitemap>
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

    // @todo think about the locate <> contains behavior
    public function siteContainsSitemap(
        Core\Site\Entity\Site $site,
        Core\Site\Entity\SiteLanguage $siteLanguage = null,
    ): bool {
        try {
            $sitemap = $this->locateBySite($site, $siteLanguage);
            $response = $this->requestFactory->request((string)$sitemap->getUri(), 'HEAD');

            return $response->getStatusCode() < 400;
        } catch (\Exception) {
            return false;
        }
    }

    private function resolveSitemap(
        Core\Site\Entity\Site $site,
        Core\Site\Entity\SiteLanguage $siteLanguage = null,
    ): ?SiteAwareSitemap {
        foreach ($this->providers as $provider) {
            if (($sitemap = $provider->get($site, $siteLanguage)) !== null) {
                return $sitemap;
            }
        }

        return null;
    }

    /**
     * @throws Exception\InvalidProviderException
     */
    private function validateProviders(): void
    {
        foreach ($this->providers as $provider) {
            /* @phpstan-ignore-next-line */
            if (!\is_object($provider)) {
                throw Exception\InvalidProviderException::forInvalidType($provider);
            }

            if (!is_a($provider, Provider\Provider::class)) {
                throw Exception\InvalidProviderException::create($provider);
            }
        }
    }
}
