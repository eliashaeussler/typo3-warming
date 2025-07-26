<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2025 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Service;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * CacheWarmupServiceTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Service\CacheWarmupService::class)]
final class CacheWarmupServiceTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\Functional\ClientMockTrait;
    use Tests\Functional\SiteTrait;

    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'typed_extconf',
        'warming',
    ];

    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'warming' => [
                'crawler' => Tests\Functional\Fixtures\Classes\DummyCrawler::class,
                'parserOptions' => '{"request_options":{"auth":["username","password"]}}',
            ],
        ],
    ];

    private Core\Site\Entity\Site $site;
    private Typo3SitemapLocator\Cache\SitemapsCache $cache;
    private Tests\Functional\Fixtures\Classes\DummyEventDispatcher $eventDispatcher;
    private Src\Service\CacheWarmupService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/pages.csv');

        // Create site configuration
        $this->site = $this->createSite();

        // Set up backend user
        $backendUser = $this->setUpBackendUser(3);
        $GLOBALS['LANG'] = $this->get(Core\Localization\LanguageServiceFactory::class)->createFromUserPreferences($backendUser);

        $this->cache = $this->get(Typo3SitemapLocator\Cache\SitemapsCache::class);
        $this->eventDispatcher = new Tests\Functional\Fixtures\Classes\DummyEventDispatcher();
        $this->subject = new Src\Service\CacheWarmupService(
            new CacheWarmup\Http\Client\ClientFactory($this->eventDispatcher, $this->getClientOptions()),
            $this->get(Src\Configuration\Configuration::class),
            $this->eventDispatcher,
            new Typo3SitemapLocator\Sitemap\SitemapLocator(
                $this->get(Core\Http\RequestFactory::class),
                $this->cache,
                $this->eventDispatcher,
                [new Typo3SitemapLocator\Sitemap\Provider\DefaultProvider()],
            ),
            $this->get(Src\Http\Message\PageUriBuilder::class),
        );
    }

    #[Framework\Attributes\Test]
    public function warmupDoesNothingIfNeitherSitesNorPagesAreGiven(): void
    {
        $expected = new Src\Result\CacheWarmupResult(
            new CacheWarmup\Result\CacheWarmupResult(),
        );

        $actual = $this->subject->warmup();

        self::assertEquals($expected, $actual);
        self::assertNull($this->handler->getLastRequest());
    }

    #[Framework\Attributes\Test]
    public function warmupWarmsUpCachesOfGivenSites(): void
    {
        $this->mockSitemapResponse('en', 'de', 'fr');

        $originEN = new Src\Domain\Model\SiteAwareSitemap(
            new Core\Http\Uri('https://typo3-testing.local/sitemap.xml'),
            $this->site,
            $this->site->getDefaultLanguage(),
        );
        $originDE = new Src\Domain\Model\SiteAwareSitemap(
            new Core\Http\Uri('https://typo3-testing.local/de/sitemap.xml'),
            $this->site,
            $this->site->getLanguageById(1),
        );

        $expected = [
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/', 1.0, origin: $originEN),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-1', 0.5, origin: $originEN),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-2', 0.7, origin: $originEN),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-2/subsite-2-1', 0.5, origin: $originEN),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/de/', 1.0, origin: $originDE),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/de/subsite-1-l-1', 0.5, origin: $originDE),
        ];

        $cacheWarmupResult = new CacheWarmup\Result\CacheWarmupResult();

        foreach ($expected as $url) {
            $cacheWarmupResult->addResult(
                CacheWarmup\Result\CrawlingResult::createSuccessful($url),
            );
        }

        $actual = $this->subject->warmup([
            new Src\ValueObject\Request\SiteWarmupRequest($this->site, [0, 1]),
        ]);

        self::assertEquals(new Src\Result\CacheWarmupResult($cacheWarmupResult), $actual);
        self::assertEquals($expected, Tests\Functional\Fixtures\Classes\DummyCrawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function warmupWarmsUpCachesOfGivenPages(): void
    {
        $expected = [
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/de/'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-1'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/de/subsite-1-l-1'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-2'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-2/subsite-2-1'),
        ];

        $cacheWarmupResult = new CacheWarmup\Result\CacheWarmupResult();

        foreach ($expected as $url) {
            $cacheWarmupResult->addResult(
                CacheWarmup\Result\CrawlingResult::createSuccessful($url),
            );
        }

        $actual = $this->subject->warmup(
            pages: [
                new Src\ValueObject\Request\PageWarmupRequest(1, [0, 1]),
                new Src\ValueObject\Request\PageWarmupRequest(2, [0, 1]),
                new Src\ValueObject\Request\PageWarmupRequest(3, [0, 1]),
                new Src\ValueObject\Request\PageWarmupRequest(4, [0, 1]),
            ],
        );

        self::assertEquals(new Src\Result\CacheWarmupResult($cacheWarmupResult), $actual);
        self::assertEquals($expected, Tests\Functional\Fixtures\Classes\DummyCrawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function warmupRespectsLimit(): void
    {
        $expected = [
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/'),
        ];

        $cacheWarmupResult = new CacheWarmup\Result\CacheWarmupResult();

        foreach ($expected as $url) {
            $cacheWarmupResult->addResult(
                CacheWarmup\Result\CrawlingResult::createSuccessful($url),
            );
        }

        $actual = $this->subject->warmup(
            pages: [
                new Src\ValueObject\Request\PageWarmupRequest(1, [0, 1]),
                new Src\ValueObject\Request\PageWarmupRequest(2, [0, 1]),
            ],
            limit: 1,
        );

        self::assertEquals(new Src\Result\CacheWarmupResult($cacheWarmupResult), $actual);
        self::assertEquals($expected, Tests\Functional\Fixtures\Classes\DummyCrawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function warmupRespectsParserOptions(): void
    {
        $this->mockSitemapResponse('en');

        $this->subject->warmup([
            new Src\ValueObject\Request\SiteWarmupRequest($this->site),
        ]);

        self::assertSame(['username', 'password'], $this->handler->getLastOptions()['auth'] ?? null);
    }

    #[Framework\Attributes\Test]
    public function warmupRespectsStrategy(): void
    {
        $this->mockSitemapResponse('en', 'de', 'fr');

        $originEN = new Src\Domain\Model\SiteAwareSitemap(
            new Core\Http\Uri('https://typo3-testing.local/sitemap.xml'),
            $this->site,
            $this->site->getDefaultLanguage(),
        );
        $originDE = new Src\Domain\Model\SiteAwareSitemap(
            new Core\Http\Uri('https://typo3-testing.local/de/sitemap.xml'),
            $this->site,
            $this->site->getLanguageById(1),
        );

        $expected = [
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/', 1.0, origin: $originEN),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/de/', 1.0, origin: $originDE),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-2', 0.7, origin: $originEN),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-1', 0.5, origin: $originEN),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-2/subsite-2-1', 0.5, origin: $originEN),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/de/subsite-1-l-1', 0.5, origin: $originDE),
        ];

        $cacheWarmupResult = new CacheWarmup\Result\CacheWarmupResult();

        foreach ($expected as $url) {
            $cacheWarmupResult->addResult(
                CacheWarmup\Result\CrawlingResult::createSuccessful($url),
            );
        }

        $actual = $this->subject->warmup(
            [
                new Src\ValueObject\Request\SiteWarmupRequest($this->site, [0, 1]),
            ],
            strategy: new CacheWarmup\Crawler\Strategy\SortByPriorityStrategy(),
        );

        self::assertEquals(new Src\Result\CacheWarmupResult($cacheWarmupResult), $actual);
        self::assertEquals($expected, Tests\Functional\Fixtures\Classes\DummyCrawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function warmupDispatchesBeforeCacheWarmupEvent(): void
    {
        $this->mockSitemapResponse('en');

        $site = new Src\ValueObject\Request\SiteWarmupRequest($this->site);
        $page = new Src\ValueObject\Request\PageWarmupRequest(6);

        $this->subject->warmup(
            [$site],
            [$page],
            50,
            new CacheWarmup\Crawler\Strategy\SortByPriorityStrategy(),
        );

        self::assertCount(13, $this->eventDispatcher->dispatchedEvents);

        $actual = $this->eventDispatcher->dispatchedEvents[8];

        self::assertInstanceOf(Src\Event\BeforeCacheWarmupEvent::class, $actual);
        self::assertSame([$site], $actual->getSites());
        self::assertSame([$page], $actual->getPages());
        self::assertInstanceOf(CacheWarmup\Crawler\Strategy\SortByPriorityStrategy::class, $actual->getCrawlingStrategy());
        self::assertInstanceOf(Tests\Functional\Fixtures\Classes\DummyCrawler::class, $actual->getCrawler());
        self::assertSame(50, $actual->getCacheWarmer()->getLimit());
    }

    #[Framework\Attributes\Test]
    public function warmupDispatchesAfterCacheWarmupEvent(): void
    {
        $this->mockSitemapResponse('en');

        $origin = new Src\Domain\Model\SiteAwareSitemap(
            new Core\Http\Uri('https://typo3-testing.local/sitemap.xml'),
            $this->site,
            $this->site->getDefaultLanguage(),
        );

        $expected = [
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/', 1.0, origin: $origin),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-2', 0.7, origin: $origin),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-1', 0.5, origin: $origin),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-2/subsite-2-1', 0.5, origin: $origin),
        ];

        $cacheWarmupResult = new CacheWarmup\Result\CacheWarmupResult();

        foreach ($expected as $url) {
            $cacheWarmupResult->addResult(
                CacheWarmup\Result\CrawlingResult::createSuccessful($url),
            );
        }

        $this->subject->warmup(
            [
                new Src\ValueObject\Request\SiteWarmupRequest($this->site),
            ],
            [
                new Src\ValueObject\Request\PageWarmupRequest(6),
            ],
            50,
            new CacheWarmup\Crawler\Strategy\SortByPriorityStrategy(),
        );

        self::assertCount(13, $this->eventDispatcher->dispatchedEvents);

        $actual = $this->eventDispatcher->dispatchedEvents[12];

        self::assertInstanceOf(Src\Event\AfterCacheWarmupEvent::class, $actual);
        self::assertEquals($cacheWarmupResult, $actual->getResult()->getResult());
        self::assertInstanceOf(Tests\Functional\Fixtures\Classes\DummyCrawler::class, $actual->getCrawler());
        self::assertSame(50, $actual->getCacheWarmer()->getLimit());
    }

    #[Framework\Attributes\Test]
    public function getCrawlerReturnsGloballyConfiguredCrawler(): void
    {
        self::assertInstanceOf(
            Tests\Functional\Fixtures\Classes\DummyCrawler::class,
            $this->subject->getCrawler(),
        );
    }

    protected function tearDown(): void
    {
        $this->cache->remove($this->site, $this->site->getLanguageById(0));
        $this->cache->remove($this->site, $this->site->getLanguageById(1));
        $this->cache->remove($this->site, $this->site->getLanguageById(2));

        Tests\Functional\Fixtures\Classes\DummyCrawler::reset();

        parent::tearDown();
    }
}
