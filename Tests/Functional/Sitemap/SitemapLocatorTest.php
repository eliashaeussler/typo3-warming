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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Sitemap;

use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use Exception;
use Generator;
use PHPUnit\Framework;
use stdClass;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * SitemapLocatorTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Sitemap\SitemapLocator::class)]
final class SitemapLocatorTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'warming',
    ];

    protected Src\Cache\SitemapsCache $cache;
    protected Tests\Unit\Fixtures\DummyRequestFactory $requestFactory;
    protected Src\Sitemap\SitemapLocator $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->get(Src\Cache\SitemapsCache::class);
        $this->requestFactory = new Tests\Unit\Fixtures\DummyRequestFactory();
        $this->subject = new Src\Sitemap\SitemapLocator(
            $this->requestFactory,
            $this->cache,
            [new Src\Sitemap\Provider\DefaultProvider()],
        );

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');

        /** @var Core\Cache\Frontend\PhpFrontend $cacheFrontend */
        $cacheFrontend = $this->get('cache.warming');
        $cacheFrontend->flush();
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfGivenProviderIsNoObject(): void
    {
        $providers = [
            'foo',
        ];

        $this->expectExceptionObject(
            Src\Exception\InvalidProviderException::forInvalidType('foo'),
        );

        new Src\Sitemap\SitemapLocator($this->requestFactory, $this->cache, $providers);
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfGivenProviderIsNoValidObject(): void
    {
        $providers = [
            new stdClass(),
        ];

        $this->expectExceptionObject(
            Src\Exception\InvalidProviderException::create(new stdClass()),
        );

        new Src\Sitemap\SitemapLocator($this->requestFactory, $this->cache, $providers);
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('locateBySiteReturnsCachedSitemapDataProvider')]
    public function locateBySiteReturnsCachedSitemap(?Core\Site\Entity\SiteLanguage $siteLanguage, string $expectedUrl): void
    {
        $site = self::getSite([]);
        $sitemap = new Src\Sitemap\SiteAwareSitemap(
            new Core\Http\Uri($expectedUrl),
            $site,
            $siteLanguage ?? $site->getDefaultLanguage(),
        );

        $this->cache->set($sitemap);

        self::assertEquals($sitemap, $this->subject->locateBySite($site, $siteLanguage));
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('locateBySiteThrowsExceptionIfSiteBaseHasNoHostnameConfiguredDataProvider')]
    public function locateBySiteThrowsExceptionIfSiteBaseHasNoHostnameConfigured(?Core\Site\Entity\SiteLanguage $siteLanguage): void
    {
        $site = self::getSite([]);

        $this->expectExceptionObject(
            Src\Exception\UnsupportedConfigurationException::forBaseUrl(''),
        );

        $this->subject->locateBySite($site, $siteLanguage);
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('locateBySiteThrowsExceptionIfProvidersCannotResolveSitemapDataProvider')]
    public function locateBySiteThrowsExceptionIfProvidersCannotResolveSitemap(?Core\Site\Entity\SiteLanguage $siteLanguage): void
    {
        $site = self::getSite();
        $subject = new Src\Sitemap\SitemapLocator(
            $this->requestFactory,
            $this->cache,
            []
        );

        $this->expectExceptionObject(
            Src\Exception\UnsupportedSiteException::forMissingSitemap($site),
        );

        $subject->locateBySite($site, $siteLanguage);
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('locateBySiteReturnsLocatedSitemapDataProvider')]
    public function locateBySiteReturnsLocatedSitemap(?Core\Site\Entity\SiteLanguage $siteLanguage, string $expectedUrl): void
    {
        $site = self::getSite();
        $sitemap = new Src\Sitemap\SiteAwareSitemap(
            new Core\Http\Uri($expectedUrl),
            $site,
            $siteLanguage ?? $site->getDefaultLanguage(),
        );

        self::assertNull($this->cache->get($site, $siteLanguage));
        self::assertEquals($sitemap, $this->subject->locateBySite($site, $siteLanguage));
        self::assertEquals($sitemap, $this->cache->get($site, $siteLanguage));
    }

    #[Framework\Attributes\Test]
    public function locateAllBySiteExcludesDisabledLanguages(): void
    {
        $GLOBALS['BE_USER'] = $this->setUpBackendUser(2);

        $site = self::getSite([
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
                    self::getSiteLanguage()->toArray(),
                    ['enabled' => false]
                ),
            ],
        ]);

        self::assertSame([], $this->subject->locateAllBySite($site));
    }

    #[Framework\Attributes\Test]
    public function locateAllBySiteExcludesInaccessibleLanguages(): void
    {
        $GLOBALS['BE_USER'] = $this->setUpBackendUser(2);

        $site = self::getSite([
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
                1 => self::getSiteLanguage()->toArray(),
            ],
        ]);
        $sitemap = new Src\Sitemap\SiteAwareSitemap(
            new Core\Http\Uri('https://www.example.com/'),
            $site,
            $site->getLanguageById(1),
        );

        $this->cache->set($sitemap);

        self::assertEquals([1 => $sitemap], $this->subject->locateAllBySite($site));
    }

    #[Framework\Attributes\Test]
    public function siteContainsSitemapReturnsFalseOnInaccessibleSitemap(): void
    {
        $this->requestFactory->exception = new Exception();

        self::assertFalse($this->subject->siteContainsSitemap(self::getSite()));
    }

    #[Framework\Attributes\Test]
    public function siteContainsSitemapReturnsFalseOnFailedRequest(): void
    {
        $this->requestFactory->response = new Core\Http\Response(null, 404);

        self::assertFalse($this->subject->siteContainsSitemap(self::getSite()));
    }

    #[Framework\Attributes\Test]
    public function siteContainsSitemapReturnsTrueOnSuccessfulRequest(): void
    {
        $this->requestFactory->response = new Core\Http\Response();

        self::assertTrue($this->subject->siteContainsSitemap(self::getSite()));
    }

    /**
     * @return Generator<string, array{Core\Site\Entity\SiteLanguage|null, string}>
     */
    public static function locateBySiteReturnsCachedSitemapDataProvider(): Generator
    {
        yield 'no site language' => [null, 'https://www.example.com/sitemap.xml'];
        yield 'site language' => [self::getSiteLanguage(), 'https://www.example.com/sitemap.xml'];
    }

    /**
     * @return Generator<string, array{Core\Site\Entity\SiteLanguage|null}>
     */
    public static function locateBySiteThrowsExceptionIfSiteBaseHasNoHostnameConfiguredDataProvider(): Generator
    {
        yield 'no site language' => [null];
        yield 'site language' => [self::getSiteLanguage('')];
    }

    /**
     * @return Generator<string, array{Core\Site\Entity\SiteLanguage|null}>
     */
    public static function locateBySiteThrowsExceptionIfProvidersCannotResolveSitemapDataProvider(): Generator
    {
        yield 'no site language' => [null];
        yield 'site language' => [self::getSiteLanguage()];
    }

    /**
     * @return Generator<string, array{Core\Site\Entity\SiteLanguage|null, string}>
     */
    public static function locateBySiteReturnsLocatedSitemapDataProvider(): Generator
    {
        yield 'no site language' => [null, 'https://www.example.com/sitemap.xml'];
        yield 'site language' => [self::getSiteLanguage(), 'https://www.example.com/de/sitemap.xml'];
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private static function getSite(array $configuration = ['base' => 'https://www.example.com/']): Core\Site\Entity\Site
    {
        return new Core\Site\Entity\Site('foo', 1, $configuration);
    }

    private static function getSiteLanguage(string $baseUrl = 'https://www.example.com/de/'): Core\Site\Entity\SiteLanguage
    {
        return new Core\Site\Entity\SiteLanguage(1, 'de_DE.UTF-8', new Core\Http\Uri($baseUrl), []);
    }
}
