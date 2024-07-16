<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2024 Elias Häußler <elias@haeussler.dev>
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
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * CacheManagerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class CacheManagerTest extends UnitTestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy|PhpFrontend
     */
    protected ObjectProphecy $cacheProphecy;
    protected CacheManager $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheProphecy = $this->prophesize(PhpFrontend::class);
        $this->subject = new CacheManager($this->cacheProphecy->reveal());
    }

    /**
     * @test
     * @dataProvider getReturnsEmptyArrayIfNoSitemapsAreCachedDataProvider
     * @param false|array<string, mixed> $cacheValue
     */
    public function getReturnsEmptyArrayIfNoSitemapsAreCached($cacheValue): void
    {
        $this->cacheProphecy->require(CacheManager::CACHE_IDENTIFIER)->willReturn($cacheValue);

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
    public function getReturnsCachedSitemapForDefaultLanguage(): void
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
        self::assertSame('baz', $this->subject->get($site, $site->getDefaultLanguage()));
    }

    /**
     * @test
     */
    public function getReturnsCachedSitemapForGivenLanguage(): void
    {
        $site = new Site('foo', 1, []);
        $siteLanguage = new SiteLanguage(1, 'de_DE.UTF-8', new Uri('https://example.com'), []);

        $this->cacheProphecy->require(CacheManager::CACHE_IDENTIFIER)->willReturn([
            'sitemaps' => [
                'foo' => [
                    '1' => 'baz',
                ],
            ],
        ]);

        self::assertSame('baz', $this->subject->get($site, $siteLanguage));
    }

    /**
     * @return \Generator<string, array{bool|array{sitemaps?: array{}}}>
     */
    public function getReturnsEmptyArrayIfNoSitemapsAreCachedDataProvider(): \Generator
    {
        yield 'no cache' => [false];
        yield 'empty cache' => [[]];
        yield 'no sitemaps' => [['sitemaps' => []]];
    }
}
