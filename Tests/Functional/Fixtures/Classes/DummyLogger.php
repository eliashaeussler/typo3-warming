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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Fixtures\Classes;

use EliasHaeussler\CacheWarmup;
use Psr\Log;
use Stringable;

/**
 * DummyLogger.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 *
 * @internal
 */
final class DummyLogger extends Log\AbstractLogger
{
    /**
     * @var array<Log\LogLevel::*, list<array{message: string|Stringable, context: array<string, mixed>}>>
     */
    public array $log = [];

    public function __construct()
    {
        foreach (CacheWarmup\Log\LogLevel::getAll() as $logLevel) {
            $this->log[$logLevel] = [];
        }
    }

    /**
     * @phpstan-param Log\LogLevel::* $level
     *
     * @param array<string, mixed> $context
     */
    public function log($level, Stringable|string $message, array $context = []): void
    {
        $this->log[$level][] = [
            'message' => $message,
            'context' => $context,
        ];
    }
}
