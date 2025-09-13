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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Enums;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use Psr\Log;
use TYPO3\CMS\Core;
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
     * @param list<CacheWarmup\Result\CrawlingResult> $crawlingResults
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('fromCacheWarmupResultReturnsStateDeterminedFromGivenResultDataProvider')]
    public function fromCacheWarmupResultReturnsStateDeterminedFromGivenResult(
        array $crawlingResults,
        Src\Enums\WarmupState $expected,
    ): void {
        $cacheWarmupResult = new CacheWarmup\Result\CacheWarmupResult();

        foreach ($crawlingResults as $crawlingResult) {
            $cacheWarmupResult->addResult($crawlingResult);
        }

        $actual = Src\Enums\WarmupState::fromCacheWarmupResult(new Src\Result\CacheWarmupResult($cacheWarmupResult));

        self::assertSame($expected, $actual);
    }

    /**
     * @param list<CacheWarmup\Result\CrawlingResult> $successful
     * @param list<CacheWarmup\Result\CrawlingResult> $failed
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('fromCrawlingResultsReturnsStateDeterminedFromGivenCrawlingResultsDataProvider')]
    public function fromCrawlingResultsReturnsStateDeterminedFromGivenCrawlingResults(
        array $successful,
        array $failed,
        Src\Enums\WarmupState $expected,
    ): void {
        $actual = Src\Enums\WarmupState::fromCrawlingResults($successful, $failed);

        self::assertSame($expected, $actual);
    }

    /**
     * @return \Generator<string, array{Log\LogLevel::*, Src\Enums\WarmupState}>
     */
    public static function fromLogLevelReturnsWarmupStateFromGivenPsrLogLevelDataProvider(): \Generator
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

    /**
     * @return \Generator<string, array{list<CacheWarmup\Result\CrawlingResult>, Src\Enums\WarmupState}>
     */
    public static function fromCacheWarmupResultReturnsStateDeterminedFromGivenResultDataProvider(): \Generator
    {
        $successful = self::getSuccessfulCrawlingResult();
        $failed = self::getFailedCrawlingResult();

        yield 'no results' => [
            [],
            Src\Enums\WarmupState::Success,
        ];
        yield 'successful results only' => [
            [$successful],
            Src\Enums\WarmupState::Success,
        ];
        yield 'failed results only' => [
            [$failed],
            Src\Enums\WarmupState::Failed,
        ];
        yield 'successful and failed results' => [
            [$successful, $failed],
            Src\Enums\WarmupState::Warning,
        ];
    }

    /**
     * @return \Generator<string, array{list<CacheWarmup\Result\CrawlingResult>, list<CacheWarmup\Result\CrawlingResult>, Src\Enums\WarmupState}>
     */
    public static function fromCrawlingResultsReturnsStateDeterminedFromGivenCrawlingResultsDataProvider(): \Generator
    {
        $successful = self::getSuccessfulCrawlingResult();
        $failed = self::getFailedCrawlingResult();

        yield 'no results' => [
            [],
            [],
            Src\Enums\WarmupState::Success,
        ];
        yield 'successful results only' => [
            [$successful],
            [],
            Src\Enums\WarmupState::Success,
        ];
        yield 'failed results only' => [
            [],
            [$failed],
            Src\Enums\WarmupState::Failed,
        ];
        yield 'successful and failed results' => [
            [$successful],
            [$failed],
            Src\Enums\WarmupState::Warning,
        ];
    }

    private static function getSuccessfulCrawlingResult(): CacheWarmup\Result\CrawlingResult
    {
        $uri = new Core\Http\Uri('https://typo3-testing.local/');

        return CacheWarmup\Result\CrawlingResult::createSuccessful($uri);
    }

    private static function getFailedCrawlingResult(): CacheWarmup\Result\CrawlingResult
    {
        $uri = new Core\Http\Uri('https://typo3-testing.local/');

        return CacheWarmup\Result\CrawlingResult::createFailed($uri);
    }
}
