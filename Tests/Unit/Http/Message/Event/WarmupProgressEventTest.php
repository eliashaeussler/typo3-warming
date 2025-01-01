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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Http\Message\Event;

use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * WarmupProgressEventTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Message\Event\WarmupProgressEvent::class)]
final class WarmupProgressEventTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Http\Message\Event\WarmupProgressEvent $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Http\Message\Event\WarmupProgressEvent(
            'https://typo3-testing.local/foo/',
            [
                'https://typo3-testing.local/',
                'https://typo3-testing.local/foo/',
            ],
            [
                'https://typo3-testing.local/baz/',
            ],
            5,
        );
    }

    #[Framework\Attributes\Test]
    public function getDataIncludesProgress(): void
    {
        $actual = $this->subject->getData();

        self::assertSame(3, $actual['progress']['current']);
        self::assertSame(5, $actual['progress']['total']);
    }

    #[Framework\Attributes\Test]
    public function getDataIncludesUrls(): void
    {
        $actual = $this->subject->getData();

        self::assertSame('https://typo3-testing.local/foo/', $actual['urls']['current']);
        self::assertSame(
            [
                'https://typo3-testing.local/',
                'https://typo3-testing.local/foo/',
            ],
            $actual['urls']['successful'],
        );
        self::assertSame(
            [
                'https://typo3-testing.local/baz/',
            ],
            $actual['urls']['failed'],
        );
    }

    #[Framework\Attributes\Test]
    public function subjectIsJsonSerializable(): void
    {
        self::assertJson(json_encode($this->subject, JSON_THROW_ON_ERROR));
    }
}
