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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Sitemap\Provider;

use EliasHaeussler\Typo3Warming as Src;
use Generator;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * SiteProviderTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Sitemap\Provider\SiteProvider::class)]
final class SiteProviderTest extends TestingFramework\Core\Unit\UnitTestCase
{
    protected Src\Sitemap\Provider\SiteProvider $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new Src\Sitemap\Provider\SiteProvider();
    }

    #[Framework\Attributes\Test]
    public function getReturnsEmptyArrayIfSitemapPathIsNotConfiguredInSite(): void
    {
        $site = new Core\Site\Entity\Site('foo', 1, []);

        self::assertSame([], $this->subject->get($site));
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('getReturnsSitemapWithUrlPathFromSiteDataProvider')]
    public function getReturnsSitemapWithUrlPathFromSite(string $path, string $expected): void
    {
        $site = new Core\Site\Entity\Site('foo', 1, [
            'base' => 'https://www.example.com/',
            'xml_sitemap_path' => $path,
        ]);
        $sitemaps = [
            new Src\Sitemap\SiteAwareSitemap(
                new Core\Http\Uri($expected),
                $site,
                $site->getDefaultLanguage(),
            ),
        ];

        self::assertEquals($sitemaps, $this->subject->get($site));
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('getReturnsSitemapWithUrlPathFromSiteLanguageDataProvider')]
    public function getReturnsSitemapWithUrlPathFromSiteLanguage(string $path, string $expected): void
    {
        $site = new Core\Site\Entity\Site('foo', 1, [
            'base' => 'https://www.example.com/',
        ]);
        $siteLanguage = new Core\Site\Entity\SiteLanguage(
            1,
            'de_DE.UTF-8',
            new Core\Http\Uri('https://www.example.com/de'),
            [
                'xml_sitemap_path' => $path,
            ],
        );
        $sitemaps = [
            new Src\Sitemap\SiteAwareSitemap(
                new Core\Http\Uri($expected),
                $site,
                $siteLanguage,
            ),
        ];

        self::assertEquals($sitemaps, $this->subject->get($site, $siteLanguage));
    }

    /**
     * @return Generator<string, array{string, string}>
     */
    public static function getReturnsSitemapWithUrlPathFromSiteDataProvider(): Generator
    {
        yield 'path only' => ['baz.xml', 'https://www.example.com/baz.xml'];
        yield 'path with query string' => ['baz.xml?foo=baz', 'https://www.example.com/baz.xml?foo=baz'];
    }

    /**
     * @return Generator<string, array{string, string}>
     */
    public static function getReturnsSitemapWithUrlPathFromSiteLanguageDataProvider(): Generator
    {
        yield 'path only' => ['baz.xml', 'https://www.example.com/de/baz.xml'];
        yield 'path with query string' => ['baz.xml?foo=baz', 'https://www.example.com/de/baz.xml?foo=baz'];
    }
}
