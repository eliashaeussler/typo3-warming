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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Fixtures\Classes;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\CacheWarmup\Result;
use Psr\Log;

/**
 * DummyLoggingCrawler
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class DummyLoggingCrawler implements CacheWarmup\Crawler\LoggingCrawler
{
    public ?Log\LoggerInterface $logger = null;
    public string $logLevel = Log\LogLevel::ERROR;

    public function crawl(array $urls): Result\CacheWarmupResult
    {
        return new Result\CacheWarmupResult();
    }

    public function setLogger(Log\LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function setLogLevel(string $logLevel): void
    {
        $this->logLevel = $logLevel;
    }
}
