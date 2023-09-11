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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Cache;

use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * SitemapsCacheTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Cache\SitemapsCache::class)]
final class SitemapsCacheTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'warming',
    ];

    protected bool $initializeDatabase = false;

    protected Core\Cache\Frontend\PhpFrontend $cache;
    protected Src\Cache\SitemapsCache $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->get('cache.warming');
        $this->subject = new Src\Cache\SitemapsCache($this->cache);

        $this->cache->flush();
    }

    #[Framework\Attributes\Test]
    public function getReturnsEmptyArrayIfCacheIsMissing(): void
    {
        $site = new Core\Site\Entity\Site('baz', 1, []);

        self::assertFalse($this->cache->has('baz'));
        self::assertSame([], $this->subject->get($site));
    }

    #[Framework\Attributes\Test]
    public function getReturnsEmptyArrayIfCacheDataIsInvalid(): void
    {
        $site = new Core\Site\Entity\Site('baz', 1, []);

        $this->cache->set('baz', 'return "foo";');

        self::assertSame([], $this->subject->get($site));
    }

    #[Framework\Attributes\Test]
    public function getReturnsEmptyArrayIfCacheOfGivenSiteIsEmpty(): void
    {
        $site = new Core\Site\Entity\Site('baz', 1, []);

        $this->cache->set('baz', 'return [];');

        self::assertSame([], $this->subject->get($site));
    }

    #[Framework\Attributes\Test]
    public function getReturnsCachedSitemapsForDefaultLanguage(): void
    {
        $site = new Core\Site\Entity\Site('foo', 1, []);

        $this->cache->set(
            'foo',
            sprintf(
                'return %s;',
                var_export(
                    [
                        0 => [
                            'https://www.example.com/baz',
                            'https://www.example.com/bar',
                        ],
                    ],
                    true,
                ),
            ),
        );

        $expected = [
            new Src\Sitemap\SiteAwareSitemap(
                new Core\Http\Uri('https://www.example.com/baz'),
                $site,
                $site->getDefaultLanguage(),
            ),
            new Src\Sitemap\SiteAwareSitemap(
                new Core\Http\Uri('https://www.example.com/bar'),
                $site,
                $site->getDefaultLanguage(),
            ),
        ];

        self::assertEquals($expected, $this->subject->get($site));
        self::assertEquals($expected, $this->subject->get($site, $site->getDefaultLanguage()));
    }

    #[Framework\Attributes\Test]
    public function getReturnsCachedSitemapForGivenLanguage(): void
    {
        $site = new Core\Site\Entity\Site('foo', 1, []);
        $siteLanguage = new Core\Site\Entity\SiteLanguage(1, 'de_DE.UTF-8', new Core\Http\Uri('https://example.com'), []);

        $this->cache->set(
            'foo',
            sprintf(
                'return %s;',
                var_export(
                    [
                        1 => [
                            'https://www.example.com/baz',
                            'https://www.example.com/bar',
                        ],
                    ],
                    true,
                ),
            ),
        );

        $expected = [
            new Src\Sitemap\SiteAwareSitemap(
                new Core\Http\Uri('https://www.example.com/baz'),
                $site,
                $siteLanguage,
            ),
            new Src\Sitemap\SiteAwareSitemap(
                new Core\Http\Uri('https://www.example.com/bar'),
                $site,
                $siteLanguage,
            ),
        ];

        self::assertEquals($expected, $this->subject->get($site, $siteLanguage));
    }

    #[Framework\Attributes\Test]
    public function setInitializesCacheIfCacheIsMissing(): void
    {
        self::assertFalse($this->cache->has('foo'));

        $site = new Core\Site\Entity\Site('foo', 1, []);
        $sitemaps = [
            new Src\Sitemap\SiteAwareSitemap(
                new Core\Http\Uri('https://www.example.com/baz'),
                $site,
                $site->getDefaultLanguage(),
            ),
            new Src\Sitemap\SiteAwareSitemap(
                new Core\Http\Uri('https://www.example.com/bar'),
                $site,
                $site->getDefaultLanguage(),
            ),
        ];

        $this->subject->set($sitemaps);

        self::assertEquals($sitemaps, $this->subject->get($site, $site->getDefaultLanguage()));
        self::assertTrue($this->cache->has('foo'));
    }

    #[Framework\Attributes\Test]
    public function setStoresGivenSitemapInCache(): void
    {
        self::assertFalse($this->cache->has('foo'));

        $site = new Core\Site\Entity\Site('foo', 1, []);
        $sitemaps = [
            new Src\Sitemap\SiteAwareSitemap(
                new Core\Http\Uri('https://www.example.com/baz'),
                $site,
                $site->getDefaultLanguage(),
            ),
            new Src\Sitemap\SiteAwareSitemap(
                new Core\Http\Uri('https://www.example.com/bar'),
                $site,
                $site->getDefaultLanguage(),
            ),
        ];

        $this->subject->set($sitemaps);

        self::assertEquals($sitemaps, $this->subject->get($site, $site->getDefaultLanguage()));
        self::assertTrue($this->cache->has('foo'));
    }
}
