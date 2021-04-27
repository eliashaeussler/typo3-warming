<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "cache_warmup".
 *
 * Copyright (C) 2021 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace EliasHaeussler\Typo3CacheWarmup\Tests\Unit\Sitemap;

use EliasHaeussler\CacheWarmup\Sitemap;
use EliasHaeussler\Typo3CacheWarmup\Cache\CacheManager;
use EliasHaeussler\Typo3CacheWarmup\Exception\UnsupportedConfigurationException;
use EliasHaeussler\Typo3CacheWarmup\Exception\UnsupportedSiteException;
use EliasHaeussler\Typo3CacheWarmup\Sitemap\Provider\DefaultProvider;
use EliasHaeussler\Typo3CacheWarmup\Sitemap\SitemapLocator;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * SitemapLocatorTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class SitemapLocatorTest extends UnitTestCase
{
    /**
     * @var ObjectProphecy|CacheManager
     */
    protected $cacheManagerProphecy;

    /**
     * @var SitemapLocator
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheManagerProphecy = $this->prophesize(CacheManager::class);
        $this->subject = new SitemapLocator(new RequestFactory(), $this->cacheManagerProphecy->reveal(), [new DefaultProvider()]);
    }

    /**
     * @test
     */
    public function constructorThrowsExceptionIfGivenProviderIsNoObject(): void
    {
        $providers = [
            'foo',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1619525071);

        /* @noinspection PhpParamsInspection */
        /* @phpstan-ignore-next-line */
        new SitemapLocator(new RequestFactory(), $this->cacheManagerProphecy->reveal(), $providers);
    }

    /**
     * @test
     */
    public function constructorThrowsExceptionIfGivenProviderIsNoValidObject(): void
    {
        $providers = [
            new \stdClass(),
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1619524996);

        /* @noinspection PhpParamsInspection */
        /* @phpstan-ignore-next-line */
        new SitemapLocator(new RequestFactory(), $this->cacheManagerProphecy->reveal(), $providers);
    }

    /**
     * @test
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function locateBySiteReturnsCacheSitemap(): void
    {
        $site = $this->getSite([]);

        $this->cacheManagerProphecy->get($site)->willReturn('https://www.example.com/sitemap.xml');

        $expected = new Sitemap(new Uri('https://www.example.com/sitemap.xml'));

        self::assertEquals($expected, $this->subject->locateBySite($site));
    }

    /**
     * @test
     * @throws UnsupportedSiteException
     */
    public function locateBySiteThrowsExceptionIfSiteBaseHasNoHostnameConfigured(): void
    {
        $site = $this->getSite([]);

        $this->cacheManagerProphecy->get($site)->willReturn(null);

        $this->expectException(UnsupportedConfigurationException::class);
        $this->expectExceptionCode(1619168965);

        $this->subject->locateBySite($site);
    }

    /**
     * @test
     * @throws UnsupportedConfigurationException
     */
    public function locateBySiteThrowsExceptionIfProvidersCannotResolveSitemap(): void
    {
        $site = $this->getSite();
        $subject = new SitemapLocator(new RequestFactory(), $this->cacheManagerProphecy->reveal(), []);

        $this->cacheManagerProphecy->get($site)->willReturn(null);

        $this->expectException(UnsupportedSiteException::class);
        $this->expectExceptionCode(1619369771);

        $subject->locateBySite($site);
    }

    /**
     * @test
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function locateBySiteReturnsLocatedSitemap(): void
    {
        $site = $this->getSite();

        $this->cacheManagerProphecy->get($site)->willReturn(null);
        $this->cacheManagerProphecy->set($site, Argument::type(Sitemap::class))->shouldBeCalledOnce();

        $expected = new Sitemap(new Uri('https://www.example.com/sitemap.xml'));

        self::assertEquals($expected, $this->subject->locateBySite($site));
    }

    /**
     * @param array<string, mixed> $configuration
     * @return Site
     */
    private function getSite(array $configuration = ['base' => 'https://www.example.com/']): Site
    {
        return new Site('foo', 1, $configuration);
    }
}
