<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Sitemap;

use EliasHaeussler\Typo3Warming\Cache\CacheManager;
use EliasHaeussler\Typo3Warming\Exception\UnsupportedConfigurationException;
use EliasHaeussler\Typo3Warming\Exception\UnsupportedSiteException;
use EliasHaeussler\Typo3Warming\Sitemap\Provider\DefaultProvider;
use EliasHaeussler\Typo3Warming\Sitemap\SiteAwareSitemap;
use EliasHaeussler\Typo3Warming\Sitemap\SitemapLocator;
use EliasHaeussler\Typo3Warming\Tests\Unit\Fixtures\DummyRequestFactory;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheManager as CoreCacheManager;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * SitemapLocatorTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class SitemapLocatorTest extends FunctionalTestCase
{
    /**
     * @var DummyRequestFactory
     */
    protected $requestFactory;

    /**
     * @var PhpFrontend
     */
    protected $cache;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var SitemapLocator
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $cache = GeneralUtility::makeInstance(CoreCacheManager::class)->getCache('core');

        self::assertInstanceOf(PhpFrontend::class, $cache);

        $this->requestFactory = new DummyRequestFactory();
        $this->cache = $cache;
        $this->cacheManager = new CacheManager($this->cache);
        $this->subject = new SitemapLocator($this->requestFactory, $this->cacheManager, [new DefaultProvider()]);

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_groups.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');
        $this->setUpBackendUser(2);
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

        /* @phpstan-ignore-next-line */
        new SitemapLocator(new RequestFactory(), $this->cacheManager, $providers);
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

        /* @phpstan-ignore-next-line */
        new SitemapLocator(new RequestFactory(), $this->cacheManager, $providers);
    }

    /**
     * @test
     * @dataProvider locateBySiteReturnsCachedSitemapDataProvider
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function locateBySiteReturnsCachedSitemap(?SiteLanguage $siteLanguage, string $expectedUrl): void
    {
        $site = $this->getSite([]);
        $sitemap = new SiteAwareSitemap(new Uri($expectedUrl), $site, $siteLanguage);

        $this->cacheManager->set($sitemap);

        self::assertEquals($sitemap, $this->subject->locateBySite($site, $siteLanguage));
    }

    /**
     * @test
     * @dataProvider locateBySiteThrowsExceptionIfSiteBaseHasNoHostnameConfiguredDataProvider
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function locateBySiteThrowsExceptionIfSiteBaseHasNoHostnameConfigured(?SiteLanguage $siteLanguage): void
    {
        $site = $this->getSite([]);

        $this->populateInvalidCacheForSite($site);

        $this->expectException(UnsupportedConfigurationException::class);
        $this->expectExceptionCode(1619168965);

        $this->subject->locateBySite($site, $siteLanguage);
    }

    /**
     * @test
     * @dataProvider locateBySiteThrowsExceptionIfProvidersCannotResolveSitemapDataProvider
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function locateBySiteThrowsExceptionIfProvidersCannotResolveSitemap(?SiteLanguage $siteLanguage): void
    {
        $site = $this->getSite();
        $subject = new SitemapLocator(new RequestFactory(), $this->cacheManager, []);

        $this->populateInvalidCacheForSite($site);

        $this->expectException(UnsupportedSiteException::class);
        $this->expectExceptionCode(1619369771);

        $subject->locateBySite($site, $siteLanguage);
    }

    /**
     * @test
     * @dataProvider locateBySiteReturnsLocatedSitemapDataProvider
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function locateBySiteReturnsLocatedSitemap(?SiteLanguage $siteLanguage, string $expectedUrl): void
    {
        $site = $this->getSite();

        $this->populateInvalidCacheForSite($site);

        $expected = new SiteAwareSitemap(new Uri($expectedUrl), $site, $siteLanguage);

        self::assertEquals($expected, $this->subject->locateBySite($site, $siteLanguage));
        self::assertSame($expectedUrl, $this->cacheManager->get($site, $siteLanguage));
    }

    /**
     * @test
     */
    public function locateAllBySiteExcludesDisabledLanguages(): void
    {
        $site = $this->getSite([
            'base' => 'https://www.example.com/',
            'languages' => [
                0 => [
                    'languageId' => 0,
                    'title' => 'Default',
                    'navigationTitle' => '',
                    'typo3Language' => 'default',
                    'flag' => 'us',
                    'locale' => 'en_US.UTF-8',
                    'iso-639-1' => 'en',
                    'hreflang' => 'en-US',
                    'direction' => '',
                    'enabled' => false,
                ],
                1 => array_merge(
                    $this->getSiteLanguage()->toArray(),
                    ['enabled' => false]
                ),
            ],
        ]);

        self::assertSame([], $this->subject->locateAllBySite($site));
    }

    /**
     * @test
     */
    public function locateAllBySiteExcludesInaccessibleLanguages(): void
    {
        $site = $this->getSite([
            'base' => 'https://www.example.com/',
            'languages' => [
                0 => [
                    'languageId' => 0,
                    'title' => 'Default',
                    'navigationTitle' => '',
                    'typo3Language' => 'default',
                    'flag' => 'us',
                    'locale' => 'en_US.UTF-8',
                    'iso-639-1' => 'en',
                    'hreflang' => 'en-US',
                    'direction' => '',
                ],
                1 => $this->getSiteLanguage()->toArray(),
            ],
        ]);
        $sitemap = new SiteAwareSitemap(new Uri('https://www.example.com/'), $site, $site->getLanguageById(1));

        $this->cacheManager->set($sitemap);

        self::assertEquals([1 => $sitemap], $this->subject->locateAllBySite($site));
    }

    /**
     * @test
     */
    public function siteContainsSitemapReturnsFalseIfSiteCannotBeLocated(): void
    {
        $site = $this->getSite([]);

        $this->populateInvalidCacheForSite($site);

        self::assertFalse($this->subject->siteContainsSitemap($site));
    }

    /**
     * @test
     * @dataProvider siteContainsSitemapReturnsTrueIfLocatedSitemapIsAvailableDataProvider
     */
    public function siteContainsSitemapReturnsTrueIfLocatedSitemapIsAvailable(
        ResponseInterface $response,
        bool $expected
    ): void {
        $site = $this->getSite();

        $this->requestFactory->responseStack[] = $response;

        self::assertSame($expected, $this->subject->siteContainsSitemap($site));
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
     * @return \Generator<string, array{ResponseInterface, bool}>
     */
    public function siteContainsSitemapReturnsTrueIfLocatedSitemapIsAvailableDataProvider(): \Generator
    {
        yield 'valid response' => [new Response(), true];
        yield 'invalid response' => [new Response(null, 404), false];
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function getSite(array $configuration = ['base' => 'https://www.example.com/']): Site
    {
        return new Site('foo', 1, $configuration);
    }

    private function getSiteLanguage(string $baseUrl = 'https://www.example.com/de/'): SiteLanguage
    {
        return new SiteLanguage(1, 'de_DE.UTF-8', new Uri($baseUrl), []);
    }

    private function populateInvalidCacheForSite(Site $site): void
    {
        $this->cache->set(
            CacheManager::CACHE_IDENTIFIER,
            sprintf(
                'return %s;',
                var_export([
                    'sitemaps' => [
                        $site->getIdentifier() => [],
                    ],
                ], true)
            )
        );
    }

    protected function tearDown(): void
    {
        $this->cache->remove(CacheManager::CACHE_IDENTIFIER);

        parent::tearDown();
    }
}
