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

namespace EliasHaeussler\Typo3Warming\Cache;

use EliasHaeussler\Typo3Warming\Sitemap\SiteAwareSitemap;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * CacheManager
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class CacheManager
{
    public const CACHE_IDENTIFIER = 'tx_warming';

    /**
     * @var PhpFrontend
     */
    protected $cache;

    public function __construct(PhpFrontend $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return array<string, array>|string|null
     */
    public function get(Site $site = null, SiteLanguage $siteLanguage = null)
    {
        $cacheData = $this->cache->require(self::CACHE_IDENTIFIER);

        // Return complete cache if no specific site is requested
        if ($site === null) {
            return $cacheData['sitemaps'] ?? [];
        }

        $siteIdentifier = $site->getIdentifier();
        $languageIdentifier = $this->buildLanguageIdentifier($site, $siteLanguage);

        return $cacheData['sitemaps'][$siteIdentifier][$languageIdentifier] ?? null;
    }

    public function set(SiteAwareSitemap $sitemap): void
    {
        $cacheData = $this->get();
        $siteIdentifier = $sitemap->getSite()->getIdentifier();
        $languageIdentifier = $this->buildLanguageIdentifier($sitemap->getSite(), $sitemap->getSiteLanguage());
        $cacheData[$siteIdentifier][$languageIdentifier] = (string)$sitemap->getUri();

        $this->cache->set(
            self::CACHE_IDENTIFIER,
            sprintf('return %s;', var_export(['sitemaps' => $cacheData], true))
        );
    }

    protected function buildLanguageIdentifier(Site $site, SiteLanguage $siteLanguage = null): string
    {
        $languageIdentifier = 'default';
        if (null !== $siteLanguage && $siteLanguage !== $site->getDefaultLanguage()) {
            $languageIdentifier = (string)$siteLanguage->getLanguageId();
        }

        return $languageIdentifier;
    }
}
