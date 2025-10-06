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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Hook;

use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * DataHandlerClearCacheHookTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Hook\DataHandlerClearCacheHook::class)]
final class DataHandlerClearCacheHookTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'typed_extconf',
        'warming',
    ];

    private Src\Hook\DataHandlerClearCacheHook $subject;
    private Src\Queue\CacheWarmupQueue $queue;

    public function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_groups.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/pages.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/tt_content.csv');

        $this->subject = $this->get(Src\Hook\DataHandlerClearCacheHook::class);
        $this->queue = $this->get(Src\Queue\CacheWarmupQueue::class);
    }

    #[Framework\Attributes\Test]
    public function warmupPageCacheDoesNothingIfDisabledViaExtensionConfiguration(): void
    {
        $configuration = new Src\Configuration\Configuration(runAfterCacheClear: false);
        $subject = new Src\Hook\DataHandlerClearCacheHook($configuration, $this->queue);

        $subject->warmupPageCache([
            'table' => 'pages',
            'uid' => 1,
        ]);

        self::assertTrue($this->queue->isEmpty());
    }

    #[Framework\Attributes\Test]
    public function warmupPageCacheDoesNothingIfRequiredParametersAreMissing(): void
    {
        $this->subject->warmupPageCache([]);

        self::assertTrue($this->queue->isEmpty());
    }

    #[Framework\Attributes\Test]
    public function warmupPageCacheDoesNothingIfCurrentPageIsNotSupported(): void
    {
        $this->subject->warmupPageCache([
            'table' => 'pages',
            'uid' => 8,
        ]);

        self::assertTrue($this->queue->isEmpty());
    }

    #[Framework\Attributes\Test]
    public function warmupPageCacheAddsWarmupRequestForCurrentPageToQueue(): void
    {
        $this->subject->warmupPageCache([
            'table' => 'pages',
            'uid' => 1,
        ]);

        $this->assertQueueContainsCacheWarmupRequests([
            1 => null,
        ]);
    }

    #[Framework\Attributes\Test]
    public function warmupPageCacheAddsWarmupRequestForGivenPageIfRecordIsNotAvailableToQueue(): void
    {
        $this->subject->warmupPageCache([
            'table' => 'tt_content',
            // Record with uid=99 does not exist
            'uid' => 99,
            'uid_page' => 1,
        ]);

        $this->assertQueueContainsCacheWarmupRequests([
            1 => null,
        ]);
    }

    #[Framework\Attributes\Test]
    public function warmupPageCacheDoesNothingIfPageOfGivenRecordIsNotSuppliedInHookParams(): void
    {
        $this->subject->warmupPageCache([
            'table' => 'tt_content',
            'uid' => 3,
        ]);

        self::assertTrue($this->queue->isEmpty());
    }

    #[Framework\Attributes\Test]
    public function warmupPageCacheDoesNothingIfPageOfGivenRecordIsNotSupported(): void
    {
        $this->subject->warmupPageCache([
            'table' => 'tt_content',
            'uid' => 3,
            'uid_page' => 8,
        ]);

        self::assertTrue($this->queue->isEmpty());
    }

    #[Framework\Attributes\Test]
    public function warmupPageCacheAddsWarmupRequestForPageOfCurrentRecordToQueue(): void
    {
        $this->subject->warmupPageCache([
            'table' => 'tt_content',
            'uid' => 1,
            'uid_page' => 1,
        ]);

        $this->assertQueueContainsCacheWarmupRequests([
            1 => 0,
        ]);
    }

    #[Framework\Attributes\Test]
    public function warmupPageCacheAddsWarmupRequestForLocalizedPageOfCurrentLocalizedRecordToQueue(): void
    {
        $this->subject->warmupPageCache([
            'table' => 'tt_content',
            'uid' => 2,
            'uid_page' => 1,
        ]);

        $this->assertQueueContainsCacheWarmupRequests([
            1 => 1,
        ]);
    }

    #[Framework\Attributes\Test]
    public function warmupPageCacheAddsWarmupRequestForPageOfCurrentNonLocalizableRecordToQueue(): void
    {
        $this->subject->warmupPageCache([
            'table' => 'be_users',
            'uid' => 1,
            'uid_page' => 1,
        ]);

        $this->assertQueueContainsCacheWarmupRequests([
            1 => null,
        ]);
    }

    /**
     * @param array<positive-int, non-negative-int|null> $pages
     */
    private function assertQueueContainsCacheWarmupRequests(array $pages = []): void
    {
        $expected = [];

        foreach ($pages as $pageId => $languageId) {
            if ($languageId === null) {
                $languageIds = [];
            } else {
                $languageIds = [$languageId];
            }

            $expected[] = new Src\ValueObject\Request\PageWarmupRequest($pageId, $languageIds);
        }

        $actual = $this->queue->wrapInWarmupRequest()->getPages();

        self::assertEquals($expected, $actual);
    }
}
