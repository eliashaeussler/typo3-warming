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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3Warming\Cache;

use EliasHaeussler\CacheWarmup\Exception;
use EliasHaeussler\Typo3Warming\Sitemap;
use TYPO3\CMS\Core;

/**
 * SitemapsCache
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class SitemapsCache
{
    public function __construct(
        private readonly Core\Cache\Frontend\PhpFrontend $cache,
    ) {
    }

    /**
     * @return list<Sitemap\SiteAwareSitemap>
     * @throws Exception\InvalidUrlException
     */
    public function get(
        Core\Site\Entity\Site $site,
        Core\Site\Entity\SiteLanguage $siteLanguage = null,
    ): array {
        $cacheData = $this->readCache($site->getIdentifier());

        // Early return if cache is empty or invalid
        if ($cacheData === []) {
            return [];
        }

        // Fetch sitemaps from cache data
        $languageIdentifier = ($siteLanguage ?? $site->getDefaultLanguage())->getLanguageId();
        $sitemaps = $cacheData[$languageIdentifier] ?? null;

        // Early return if sitemaps are not cached
        if (!\is_array($sitemaps)) {
            return [];
        }

        return array_values(
            array_map(
                static fn (string $sitemapUrl) => new Sitemap\SiteAwareSitemap(
                    new Core\Http\Uri($sitemapUrl),
                    $site,
                    $siteLanguage ?? $site->getDefaultLanguage(),
                ),
                array_filter($sitemaps, 'is_string'),
            ),
        );
    }

    /**
     * @param list<Sitemap\SiteAwareSitemap> $sitemaps
     */
    public function set(array $sitemaps): void
    {
        /** @var array<string, list<Sitemap\SiteAwareSitemap>> $sitemapsBySite */
        $sitemapsBySite = [];

        // Re-index sitemaps by site
        foreach ($sitemaps as $sitemap) {
            $siteIdentifier = $sitemap->getSite()->getIdentifier();
            $sitemapsBySite[$siteIdentifier] ??= [];
            $sitemapsBySite[$siteIdentifier][] = $sitemap;
        }

        // Update cache data of all given sites
        foreach ($sitemapsBySite as $siteIdentifier => $sitemapsOfCurrentSite) {
            $cacheData = $this->readCache($siteIdentifier);
            $cachedUrls = [];

            // Append sitemap urls to cache data
            foreach ($sitemapsOfCurrentSite as $sitemap) {
                $languageIdentifier = $sitemap->getSiteLanguage()->getLanguageId();

                $cachedUrls[$languageIdentifier] ??= [];
                $cachedUrls[$languageIdentifier][] = (string)$sitemap->getUri();
            }

            $cacheData = array_replace($cacheData, $cachedUrls);

            $this->writeCache($siteIdentifier, $cacheData);
        }
    }

    /**
     * @internal
     */
    public function remove(
        Core\Site\Entity\Site $site,
        Core\Site\Entity\SiteLanguage $siteLanguage = null,
    ): void {
        $siteIdentifier = $site->getIdentifier();

        // Remove whole site from cache
        if ($siteLanguage === null) {
            $this->cache->remove($siteIdentifier);

            return;
        }

        $cacheData = $this->readCache($siteIdentifier);

        // Remove specific site language from cache data
        unset($cacheData[$siteLanguage->getLanguageId()]);

        $this->writeCache($siteIdentifier, $cacheData);
    }

    /**
     * @return array<int, list<string>>
     */
    private function readCache(string $siteIdentifier): array
    {
        /** @var array<int, list<string>>|false $cacheData */
        $cacheData = $this->cache->require($siteIdentifier);

        // Enforce array for cached data
        if (!\is_array($cacheData)) {
            $cacheData = [];
        }

        return $cacheData;
    }

    /**
     * @param array<int, list<string>> $cacheData
     */
    private function writeCache(string $siteIdentifier, array $cacheData): void
    {
        $this->cache->set(
            $siteIdentifier,
            sprintf('return %s;', var_export($cacheData, true)),
        );
    }
}
