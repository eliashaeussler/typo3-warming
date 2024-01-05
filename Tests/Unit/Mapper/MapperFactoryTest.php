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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Mapper;

use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * MapperFactoryTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Mapper\MapperFactory::class)]
final class MapperFactoryTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Tests\Unit\Fixtures\DummySiteFinder $siteFinder;
    private Src\Mapper\MapperFactory $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siteFinder = new Tests\Unit\Fixtures\DummySiteFinder();
        $this->subject = new Src\Mapper\MapperFactory($this->siteFinder);
    }

    #[Framework\Attributes\Test]
    public function getReturnsMapper(): void
    {
        $site = new Core\Site\Entity\Site('foo', 1, []);

        $this->siteFinder->expectedSite = $site;

        $mapper = $this->subject->get();

        $expected = [
            'site' => $site,
        ];

        $actual = $mapper->map(
            'array{site: \TYPO3\CMS\Core\Site\Entity\Site}',
            [
                'site' => 'foo',
            ],
        );

        self::assertSame($expected, $actual);
    }
}
