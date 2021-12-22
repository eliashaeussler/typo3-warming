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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Sitemap;

use EliasHaeussler\Typo3Warming\Cache\CacheManager;
use EliasHaeussler\Typo3Warming\Exception\UnsupportedConfigurationException;
use EliasHaeussler\Typo3Warming\Exception\UnsupportedSiteException;
use EliasHaeussler\Typo3Warming\Sitemap\Provider\DefaultProvider;
use EliasHaeussler\Typo3Warming\Sitemap\SiteAwareSitemap;
use EliasHaeussler\Typo3Warming\Sitemap\SitemapLocator;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * SitemapLocatorTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class SitemapLocatorTest extends UnitTestCase
{
    use ProphecyTrait;

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
     * @dataProvider locateBySiteReturnsCachedSitemapDataProvider
     * @param SiteLanguage|null $siteLanguage
     * @param string $expectedUrl
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function locateBySiteReturnsCachedSitemap(?SiteLanguage $siteLanguage, string $expectedUrl): void
    {
        $site = $this->getSite([]);

        $this->cacheManagerProphecy->get($site, $siteLanguage)->willReturn($expectedUrl);

        $expected = new SiteAwareSitemap(new Uri($expectedUrl), $site, $siteLanguage);

        self::assertEquals($expected, $this->subject->locateBySite($site, $siteLanguage));
    }

    /**
     * @test
     * @dataProvider locateBySiteThrowsExceptionIfSiteBaseHasNoHostnameConfiguredDataProvider
     * @param SiteLanguage|null $siteLanguage
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function locateBySiteThrowsExceptionIfSiteBaseHasNoHostnameConfigured(?SiteLanguage $siteLanguage): void
    {
        $site = $this->getSite([]);

        $this->cacheManagerProphecy->get($site, $siteLanguage)->willReturn(null);

        $this->expectException(UnsupportedConfigurationException::class);
        $this->expectExceptionCode(1619168965);

        $this->subject->locateBySite($site, $siteLanguage);
    }

    /**
     * @test
     * @dataProvider locateBySiteThrowsExceptionIfProvidersCannotResolveSitemapDataProvider
     * @param SiteLanguage|null $siteLanguage
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function locateBySiteThrowsExceptionIfProvidersCannotResolveSitemap(?SiteLanguage $siteLanguage): void
    {
        $site = $this->getSite();
        $subject = new SitemapLocator(new RequestFactory(), $this->cacheManagerProphecy->reveal(), []);

        $this->cacheManagerProphecy->get($site, $siteLanguage)->willReturn(null);

        $this->expectException(UnsupportedSiteException::class);
        $this->expectExceptionCode(1619369771);

        $subject->locateBySite($site, $siteLanguage);
    }

    /**
     * @test
     * @dataProvider locateBySiteReturnsLocatedSitemapDataProvider
     * @param SiteLanguage|null $siteLanguage
     * @param string $expectedUrl
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function locateBySiteReturnsLocatedSitemap(?SiteLanguage $siteLanguage, string $expectedUrl): void
    {
        $site = $this->getSite();

        $this->cacheManagerProphecy->get($site, $siteLanguage)->willReturn(null);
        $this->cacheManagerProphecy->set(Argument::type(SiteAwareSitemap::class))->shouldBeCalledOnce();

        $expected = new SiteAwareSitemap(new Uri($expectedUrl), $site, $siteLanguage);

        self::assertEquals($expected, $this->subject->locateBySite($site, $siteLanguage));
    }

    /**
     * @return \Generator<string, array{SiteLanguage|null, string}>
     */
    public function locateBySiteReturnsCachedSitemapDataProvider(): \Generator
    {
        yield 'no site language' => [null, 'https://www.example.com/sitemap.xml'];
        yield 'site language' => [$this->getSiteLanguage(), 'https://www.example.com/sitemap.xml'];
    }

    /**
     * @return \Generator<string, array{SiteLanguage|null}>
     */
    public function locateBySiteThrowsExceptionIfSiteBaseHasNoHostnameConfiguredDataProvider(): \Generator
    {
        yield 'no site language' => [null];
        yield 'site language' => [$this->getSiteLanguage('')];
    }

    /**
     * @return \Generator<string, array{SiteLanguage|null}>
     */
    public function locateBySiteThrowsExceptionIfProvidersCannotResolveSitemapDataProvider(): \Generator
    {
        yield 'no site language' => [null];
        yield 'site language' => [$this->getSiteLanguage()];
    }

    /**
     * @return \Generator<string, array{SiteLanguage|null, string}>
     */
    public function locateBySiteReturnsLocatedSitemapDataProvider(): \Generator
    {
        yield 'no site language' => [null, 'https://www.example.com/sitemap.xml'];
        yield 'site language' => [$this->getSiteLanguage(), 'https://www.example.com/de/sitemap.xml'];
    }

    /**
     * @param array<string, mixed> $configuration
     * @return Site
     */
    private function getSite(array $configuration = ['base' => 'https://www.example.com/']): Site
    {
        return new Site('foo', 1, $configuration);
    }

    private function getSiteLanguage(string $baseUrl = 'https://www.example.com/de/'): SiteLanguage
    {
        return new SiteLanguage(1, 'de_DE.UTF-8', new Uri($baseUrl), []);
    }
}
