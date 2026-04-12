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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Log\Processor;

use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use Psr\Log;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * RequestIdProcessorTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Log\Processor\RequestIdProcessor::class)]
final class RequestIdProcessorTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Core\Log\LogRecord $logRecord;
    private Src\Log\Processor\RequestIdProcessor $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->logRecord = new Core\Log\LogRecord(
            'test',
            Log\LogLevel::ERROR,
            'Something went wrong.',
            [],
            'foo',
        );
        $this->subject = new Src\Log\Processor\RequestIdProcessor();
    }

    #[Framework\Attributes\Test]
    public function processLogRecordDoesNothingIfRequestIdIsMissingInRecordData(): void
    {
        $actual = $this->subject->processLogRecord($this->logRecord);

        self::assertSame('foo', $actual->getRequestId());
    }

    #[Framework\Attributes\Test]
    public function processLogRecordOverwritesRequestIdFromRecordData(): void
    {
        $this->logRecord->setData(['request_id' => 'baz']);

        $actual = $this->subject->processLogRecord($this->logRecord);

        self::assertSame('baz', $actual->getRequestId());
    }
}
