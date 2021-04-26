<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "cache_warmup".
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

namespace EliasHaeussler\Typo3CacheWarmup\Cache;

use EliasHaeussler\CacheWarmup\Sitemap;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * CacheManager
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class CacheManager
{
    protected const CACHE_IDENTIFIER = 'tx_cachewarmup';

    /**
     * @var PhpFrontend
     */
    protected $cache;

    public function __construct(PhpFrontend $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param Site|null $site
     * @return array<string, string>|string|null
     */
    public function get(Site $site = null)
    {
        $cacheData = $this->cache->require(self::CACHE_IDENTIFIER);

        if ($site !== null) {
            return $cacheData['sitemaps'][$site->getIdentifier()] ?? null;
        }

        return $cacheData['sitemaps'] ?? [];
    }

    public function set(Site $site, Sitemap $sitemap): void
    {
        $cacheData = $this->get();
        $cacheData[$site->getIdentifier()] = (string)$sitemap->getUri();

        $this->cache->set(
            self::CACHE_IDENTIFIER,
            sprintf('return %s;', var_export(['sitemaps' => $cacheData], true))
        );
    }
}
