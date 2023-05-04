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
     * @throws Exception\InvalidUrlException
     */
    public function get(
        Core\Site\Entity\Site $site,
        Core\Site\Entity\SiteLanguage $siteLanguage = null,
    ): Sitemap\SiteAwareSitemap|null {
        /** @var array<string, array<string, string>>|false $cacheData */
        $cacheData = $this->cache->require(self::ENTRY_IDENTIFIER);

        // Early return if cache is empty
        if ($cacheData === false) {
            return null;
        }

        // Fetch sitemap from cache data
        $siteIdentifier = $site->getIdentifier();
        $languageIdentifier = $this->buildLanguageIdentifier($site, $siteLanguage);
        $sitemap = $cacheData[$siteIdentifier][$languageIdentifier] ?? null;

        // Early return if sitemap is not cached
        if (!\is_string($sitemap)) {
            return null;
        }

        return new Sitemap\SiteAwareSitemap(
            new Core\Http\Uri($sitemap),
            $site,
            $siteLanguage ?? $site->getDefaultLanguage(),
        );
    }

    public function set(Sitemap\SiteAwareSitemap $sitemap): void
    {
        /** @var array<string, array<string, string>>|false $cacheData */
        $cacheData = $this->cache->require(self::ENTRY_IDENTIFIER);

        // Enforce array for cached data
        if ($cacheData === false) {
            $cacheData = [];
        }

        // Append sitemap url to cache data
        $siteIdentifier = $sitemap->getSite()->getIdentifier();
        $languageIdentifier = $this->buildLanguageIdentifier($sitemap->getSite(), $sitemap->getSiteLanguage());
        $cacheData[$siteIdentifier][$languageIdentifier] = (string)$sitemap->getUri();

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
