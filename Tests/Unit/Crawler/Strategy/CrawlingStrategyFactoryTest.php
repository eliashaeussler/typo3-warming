<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Crawler\Strategy;

use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use Symfony\Component\DependencyInjection;
use TYPO3\TestingFramework;

/**
 * CrawlingStrategyFactoryTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Crawler\Strategy\CrawlingStrategyFactory::class)]
final class CrawlingStrategyFactoryTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Crawler\Strategy\CrawlingStrategyFactory $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Crawler\Strategy\CrawlingStrategyFactory(
            new DependencyInjection\ServiceLocator([
                'dummy' => static fn (): Tests\Unit\Fixtures\DummyCrawlingStrategy => new Tests\Unit\Fixtures\DummyCrawlingStrategy(),
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function getReturnsNullIfGivenCrawlingStrategyIsNotAvailable(): void
    {
        self::assertNull($this->subject->get('foo'));
    }

    #[Framework\Attributes\Test]
    public function getReturnsCrawlingStrategyOfGivenName(): void
    {
        self::assertInstanceOf(Tests\Unit\Fixtures\DummyCrawlingStrategy::class, $this->subject->get('dummy'));
    }

    #[Framework\Attributes\Test]
    public function getAllReturnsAllRegisteredCrawlingStrategies(): void
    {
        $expected = [
            'dummy' => new Tests\Unit\Fixtures\DummyCrawlingStrategy(),
        ];

        self::assertEquals($expected, $this->subject->getAll());
    }

    #[Framework\Attributes\Test]
    public function hasReturnsTrueIfGivenCrawlingStrategyIsAvailable(): void
    {
        self::assertTrue($this->subject->has('dummy'));
        self::assertFalse($this->subject->has('foo'));
    }
}
