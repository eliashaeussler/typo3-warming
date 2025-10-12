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
use Psr\Http\Message;

/**
 * DummyCrawler
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final class DummyCrawler implements CacheWarmup\Crawler\ConfigurableCrawler
{
    /**
     * @var list<Message\UriInterface>
     */
    public static array $crawledUrls = [];

    /**
     * @var array<string, mixed>
     */
    public static array $options = [];

    public static bool $failOnNextIteration = false;

    /**
     * @var list<non-negative-int>
     */
    public static array $failOnIterations = [];

    public static bool $throwExceptionOnNextIteration = false;

    public function crawl(array $urls): CacheWarmup\Result\CacheWarmupResult
    {
        self::$crawledUrls = $urls;

        $result = new CacheWarmup\Result\CacheWarmupResult();

        if (self::$throwExceptionOnNextIteration) {
            self::$throwExceptionOnNextIteration = false;

            throw new \Exception('Something went wrong.');
        }

        foreach ($urls as $i => $url) {
            if (\in_array($i, self::$failOnIterations, true)) {
                $crawlingResult = CacheWarmup\Result\CrawlingResult::createFailed($url);
            } else {
                $crawlingResult = CacheWarmup\Result\CrawlingResult::createSuccessful($url);
            }

            $result->addResult($crawlingResult);
        }

        self::$failOnIterations = [];

        return $result;
    }

    public function setOptions(array $options): void
    {
        self::$options = $options;
    }

    public static function reset(): void
    {
        self::$crawledUrls = [];
        self::$options = [];
        self::$failOnIterations = [];
        self::$throwExceptionOnNextIteration = false;
    }
}
