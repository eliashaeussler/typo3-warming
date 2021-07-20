<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Cache;

use EliasHaeussler\Typo3Warming\Cache\CacheManager;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * CacheManagerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class CacheManagerTest extends UnitTestCase
{
    /**
     * @var ObjectProphecy|PhpFrontend
     */
    protected $cacheProphecy;

    /**
     * @var CacheManager
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheProphecy = $this->prophesize(PhpFrontend::class);
        $this->subject = new CacheManager($this->cacheProphecy->reveal());
    }

    /**
     * @test
     */
    public function getReturnsEmptyArrayIfNoSitemapsAreCached(): void
    {
        $this->cacheProphecy->require(CacheManager::CACHE_IDENTIFIER)->willReturn(false);

        self::assertSame([], $this->subject->get());
    }

    /**
     * @test
     */
    public function getReturnsAllCachedSitemaps(): void
    {
        $this->cacheProphecy->require(CacheManager::CACHE_IDENTIFIER)->willReturn([
            'sitemaps' => [
                'foo' => 'baz',
            ],
        ]);

        self::assertSame(['foo' => 'baz'], $this->subject->get());
    }

    /**
     * @test
     */
    public function getReturnsNullIfGivenSiteIsNotCached(): void
    {
        $site = new Site('baz', 1, []);

        $this->cacheProphecy->require(CacheManager::CACHE_IDENTIFIER)->willReturn([
            'sitemaps' => [
                'foo' => 'baz',
            ],
        ]);

        self::assertNull($this->subject->get($site));
    }

    /**
     * @test
     */
    public function getReturnsCachedSitemap(): void
    {
        $site = new Site('foo', 1, []);

        $this->cacheProphecy->require(CacheManager::CACHE_IDENTIFIER)->willReturn([
            'sitemaps' => [
                'foo' => [
                    'default' => 'baz',
                ],
            ],
        ]);

        self::assertSame('baz', $this->subject->get($site));
    }
}
