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
    private const ENTRY_IDENTIFIER = 'sitemaps';

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
        /** @var array<string, array<string, string|list<string>>>|false $cacheData */
        $cacheData = $this->cache->require(self::ENTRY_IDENTIFIER);

        // Early return if cache is empty
        if ($cacheData === false) {
            return [];
        }

        // Fetch sitemaps from cache data
        $siteIdentifier = $site->getIdentifier();
        $languageIdentifier = $this->buildLanguageIdentifier($site, $siteLanguage);
        $sitemaps = $cacheData[$siteIdentifier][$languageIdentifier] ?? null;

        // BC: Convert single sitemap to sitemaps array
        if (\is_string($sitemaps)) {
            $sitemaps = [$sitemaps];
        }

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
        /** @var array<string, array<string, string|list<string>>>|false $cacheData */
        $cacheData = $this->cache->require(self::ENTRY_IDENTIFIER);

        // Enforce array for cached data
        if ($cacheData === false) {
            $cacheData = [];
        }

        $cachedUrls = [];

        // Append sitemap urls to cache data
        foreach ($sitemaps as $sitemap) {
            $siteIdentifier = $sitemap->getSite()->getIdentifier();
            $languageIdentifier = $this->buildLanguageIdentifier($sitemap->getSite(), $sitemap->getSiteLanguage());

            $cachedUrls[$siteIdentifier][$languageIdentifier] ??= [];
            $cachedUrls[$siteIdentifier][$languageIdentifier][] = (string)$sitemap->getUri();
        }

        $cacheData = array_replace_recursive($cacheData, $cachedUrls);

        $this->cache->set(
            self::ENTRY_IDENTIFIER,
            sprintf('return %s;', var_export($cacheData, true)),
        );
    }

    private function buildLanguageIdentifier(
        Core\Site\Entity\Site $site,
        Core\Site\Entity\SiteLanguage $siteLanguage = null,
    ): string {
        $languageIdentifier = 'default';
        if ($siteLanguage !== null && $siteLanguage !== $site->getDefaultLanguage()) {
            $languageIdentifier = (string)$siteLanguage->getLanguageId();
        }

        return $languageIdentifier;
    }
}
