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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\EventListener;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * LoggingCrawlerListenerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\EventListener\LoggingCrawlerListener::class)]
final class LoggingCrawlerListenerTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'warming',
    ];

    protected bool $initializeDatabase = false;

    private Src\EventListener\LoggingCrawlerListener $subject;
    private CacheWarmup\Event\Crawler\CrawlerConstructed $event;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(Src\EventListener\LoggingCrawlerListener::class);
        $this->event = new CacheWarmup\Event\Crawler\CrawlerConstructed(
            new Tests\Functional\Fixtures\Classes\DummyLoggingCrawler(),
        );
    }

    #[Framework\Attributes\Test]
    public function invokeInjectsCrawlerSpecificLogger(): void
    {
        ($this->subject)($this->event);

        $logManager = $this->get(Core\Log\LogManager::class);
        $expected = $logManager->getLogger(Tests\Functional\Fixtures\Classes\DummyLoggingCrawler::class);

        /** @var Tests\Functional\Fixtures\Classes\DummyLoggingCrawler $crawler */
        $crawler = $this->event->crawler();

        self::assertSame($expected, $crawler->logger);
    }
}
