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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\EventListener;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use Psr\Log;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * LoggingCrawlerListenerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\EventListener\LoggingCrawlerListener::class)]
final class LoggingCrawlerListenerTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Core\Log\LogManager&Framework\MockObject\MockObject $logManagerMock;
    private Src\EventListener\LoggingCrawlerListener $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->logManagerMock = $this->createMock(Core\Log\LogManager::class);
        $this->subject = new Src\EventListener\LoggingCrawlerListener($this->logManagerMock);
    }

    #[Framework\Attributes\Test]
    public function invokeInjectsCrawlerSpecificLogger(): void
    {
        $event = new CacheWarmup\Event\Crawler\CrawlerConstructed(
            new Tests\Functional\Fixtures\Classes\DummyLoggingCrawler(),
        );

        $logger = new Log\NullLogger();

        $this->logManagerMock
            ->expects(self::once())
            ->method('getLogger')
            ->willReturn($logger)
        ;

        ($this->subject)($event);

        $crawler = $event->crawler();

        self::assertInstanceOf(Tests\Functional\Fixtures\Classes\DummyLoggingCrawler::class, $crawler);
        self::assertSame($logger, $crawler->logger);
    }

    #[Framework\Attributes\Test]
    public function invokeDoesNothingIfCrawlerIsNotSupported(): void
    {
        $event = new CacheWarmup\Event\Crawler\CrawlerConstructed(
            new Tests\Functional\Fixtures\Classes\DummyCrawler(),
        );

        $this->logManagerMock
            ->expects(self::never())
            ->method('getLogger')
        ;

        ($this->subject)($event);
    }
}
