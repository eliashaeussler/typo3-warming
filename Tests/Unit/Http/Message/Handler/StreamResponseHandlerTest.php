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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Http\Message\Handler;

use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use Exception;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * StreamResponseHandlerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Message\Handler\StreamResponseHandler::class)]
final class StreamResponseHandlerTest extends TestingFramework\Core\Unit\UnitTestCase
{
    protected Tests\Unit\Fixtures\DummyEventStream $eventStream;
    protected Src\Http\Message\Handler\StreamResponseHandler $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventStream = new Tests\Unit\Fixtures\DummyEventStream();
        $this->subject = new Src\Http\Message\Handler\StreamResponseHandler($this->eventStream, 5);
    }

    #[Framework\Attributes\Test]
    public function onSuccessSendsWarmupProgressEvent(): void
    {
        $expected = new Src\Http\Message\Event\WarmupProgressEvent(
            'https://typo3-testing.local/',
            ['https://typo3-testing.local/'],
            [],
            5,
        );

        $this->subject->onSuccess(
            new Core\Http\Response(),
            new Core\Http\Uri('https://typo3-testing.local/'),
        );

        self::assertEquals([$expected], $this->eventStream->receivedEvents);
    }

    #[Framework\Attributes\Test]
    public function onFailureSendsWarmupProgressEvent(): void
    {
        $expected = new Src\Http\Message\Event\WarmupProgressEvent(
            'https://typo3-testing.local/',
            [],
            ['https://typo3-testing.local/'],
            5,
        );

        $this->subject->onFailure(
            new Exception(),
            new Core\Http\Uri('https://typo3-testing.local/'),
        );

        self::assertEquals([$expected], $this->eventStream->receivedEvents);
    }
}
