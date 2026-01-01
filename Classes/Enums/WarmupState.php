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

namespace EliasHaeussler\Typo3Warming\Enums;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming\Result;
use Psr\Log;

/**
 * WarmupState
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
enum WarmupState: string
{
    case Failed = 'failed';
    case Success = 'success';
    case Unknown = 'unknown';
    case Warning = 'warning';

    /**
     * @phpstan-param Log\LogLevel::* $level
     */
    public static function fromLogLevel(string $level): self
    {
        if (CacheWarmup\Log\LogLevel::satisfies(Log\LogLevel::ERROR, $level)) {
            return self::Failed;
        }

        if (CacheWarmup\Log\LogLevel::satisfies(Log\LogLevel::WARNING, $level)) {
            return self::Warning;
        }

        if (CacheWarmup\Log\LogLevel::satisfies(Log\LogLevel::INFO, $level)) {
            return self::Success;
        }

        return self::Unknown;
    }

    public static function fromCacheWarmupResult(Result\CacheWarmupResult $result): self
    {
        return self::fromCrawlingResults(
            $result->getResult()->getSuccessful(),
            $result->getResult()->getFailed(),
        );
    }

    /**
     * @param list<CacheWarmup\Result\CrawlingResult> $successful
     * @param list<CacheWarmup\Result\CrawlingResult> $failed
     */
    public static function fromCrawlingResults(array $successful, array $failed): self
    {
        $successfulCount = \count($successful);
        $failedCount = \count($failed);

        if ($failedCount > 0 && $successfulCount === 0) {
            return self::Failed;
        }

        if ($failedCount > 0 && $successfulCount > 0) {
            return self::Warning;
        }

        if ($failedCount === 0) {
            return self::Success;
        }

        return self::Unknown;
    }
}
