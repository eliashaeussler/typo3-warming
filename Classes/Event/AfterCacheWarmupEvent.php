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

namespace EliasHaeussler\Typo3Warming\Event;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming\Result;

/**
 * AfterCacheWarmupEvent
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class AfterCacheWarmupEvent
{
    public function __construct(
        private readonly Result\CacheWarmupResult $result,
        private readonly CacheWarmup\Crawler\CrawlerInterface $crawler,
        private readonly CacheWarmup\CacheWarmer $cacheWarmer,
    ) {}

    public function getResult(): Result\CacheWarmupResult
    {
        return $this->result;
    }

    public function getCrawler(): CacheWarmup\Crawler\CrawlerInterface
    {
        return $this->crawler;
    }

    public function getCacheWarmer(): CacheWarmup\CacheWarmer
    {
        return $this->cacheWarmer;
    }
}
