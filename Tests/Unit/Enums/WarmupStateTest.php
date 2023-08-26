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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Enums;

use EliasHaeussler\Typo3Warming as Src;
use Generator;
use PHPUnit\Framework;
use Psr\Log;
use TYPO3\TestingFramework;

/**
 * WarmupStateTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Enums\WarmupState::class)]
final class WarmupStateTest extends TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @phpstan-param Log\LogLevel::* $logLevel
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('fromLogLevelReturnsWarmupStateFromGivenPsrLogLevelDataProvider')]
    public function fromLogLevelReturnsWarmupStateFromGivenPsrLogLevel(
        string $logLevel,
        Src\Enums\WarmupState $expected,
    ): void {
        self::assertSame($expected, Src\Enums\WarmupState::fromLogLevel($logLevel));
    }

    /**
     * @return Generator<string, array{Log\LogLevel::*, Src\Enums\WarmupState}
     */
    public static function fromLogLevelReturnsWarmupStateFromGivenPsrLogLevelDataProvider(): Generator
    {
        yield 'emergency' => [Log\LogLevel::EMERGENCY, Src\Enums\WarmupState::Failed];
        yield 'alert' => [Log\LogLevel::ALERT, Src\Enums\WarmupState::Failed];
        yield 'critical' => [Log\LogLevel::CRITICAL, Src\Enums\WarmupState::Failed];
        yield 'error' => [Log\LogLevel::ERROR, Src\Enums\WarmupState::Failed];
        yield 'warning' => [Log\LogLevel::WARNING, Src\Enums\WarmupState::Warning];
        yield 'notice' => [Log\LogLevel::NOTICE, Src\Enums\WarmupState::Success];
        yield 'info' => [Log\LogLevel::INFO, Src\Enums\WarmupState::Success];
        yield 'debug' => [Log\LogLevel::DEBUG, Src\Enums\WarmupState::Unknown];
    }
}
