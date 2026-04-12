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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Http\Message\Handler;

use EliasHaeussler\TransientLogger;
use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * LogHandlerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Message\Handler\LogHandler::class)]
final class LogHandlerTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private TransientLogger\TransientLogger $logger;
    private Src\Http\Message\Handler\LogHandler $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->logger = new TransientLogger\TransientLogger();
        $this->subject = new Src\Http\Message\Handler\LogHandler($this->logger, 'baz');
    }

    #[Framework\Attributes\Test]
    public function onSuccessLogsInfoAndAttachesRequestId(): void
    {
        $response = new Core\Http\Response();
        $uri = new Core\Http\Uri('https://typo3-testing.local/');

        $expected = new TransientLogger\Log\LogRecord(
            TransientLogger\Log\LogLevel::Info,
            'URL {url} was successfully crawled (status code: {status_code}).',
            [
                'url' => $uri,
                'status_code' => 200,
                'request_id' => 'baz',
            ],
        );

        $this->subject->onSuccess($response, $uri);

        self::assertEquals([$expected], $this->logger->getAll());
    }

    #[Framework\Attributes\Test]
    public function onFailureLogsErrorAndAttachesRequestId(): void
    {
        $exception = new \Exception('Something went wrong');
        $uri = new Core\Http\Uri('https://typo3-testing.local/');

        $expected = new TransientLogger\Log\LogRecord(
            TransientLogger\Log\LogLevel::Error,
            'Error while crawling URL {url} (exception: {exception}).',
            [
                'url' => $uri,
                'exception' => $exception,
                'request_id' => 'baz',
            ],
        );

        $this->subject->onFailure($exception, $uri);

        self::assertEquals([$expected], $this->logger->getAll());
    }
}
