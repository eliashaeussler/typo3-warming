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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Service;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use Generator;
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
        'warming',
    ];

    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'warming' => [
                'crawler' => Tests\Functional\Fixtures\Classes\DummyCrawler::class,
                'parserClientOptions' => '{"foo":"baz"}',
            ],
        ],
    ];

    private Core\Site\Entity\Site $site;
    private Tests\Functional\Fixtures\Classes\DummyEventDispatcher $eventDispatcher;
    private Tests\Functional\Fixtures\Classes\DummyGuzzleClientFactory $guzzleClientFactory;
    private Src\Service\CacheWarmupService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/pages.csv');

        // Set up backend user
        $this->setUpBackendUser(3);
        Core\Core\Bootstrap::initializeLanguageObject();

        $this->site = $this->createSite();
        $this->eventDispatcher = new Tests\Functional\Fixtures\Classes\DummyEventDispatcher();
        $this->guzzleClientFactory = new Tests\Functional\Fixtures\Classes\DummyGuzzleClientFactory();
        $this->subject = new Src\Service\CacheWarmupService(
            new Src\Http\Client\ClientFactory($this->guzzleClientFactory),
            $this->get(Src\Configuration\Configuration::class),
            $this->get(CacheWarmup\Crawler\CrawlerFactory::class),
            $this->get(Src\Crawler\Strategy\CrawlingStrategyFactory::class),
            $this->eventDispatcher,
            $this->get(Typo3SitemapLocator\Sitemap\SitemapLocator::class),
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
        self::assertNull($this->guzzleClientFactory->handler->getLastRequest());
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
    public function warmupRespectsParserClientOptions(): void
    {
        $this->mockSitemapResponse('en');

        $this->subject->warmup([
            new Src\ValueObject\Request\SiteWarmupRequest($this->site),
        ]);

        self::assertSame('baz', $this->guzzleClientFactory->lastOptions['foo'] ?? null);
    }

    #[Framework\Attributes\Test]
    public function warmupRespectsStrategy(): void
    {
        $this->mockSitemapResponse('en', 'de', 'fr');

        $originEN = new Src\Domain\Model\SiteAwareSitemap(
            new Core\Http\Uri('https://typo3-testing.local/sitemap.xml'),
            $this->site,
            $this->site->getDefaultLanguage(),
            true,
        );
        $originDE = new Src\Domain\Model\SiteAwareSitemap(
            new Core\Http\Uri('https://typo3-testing.local/de/sitemap.xml'),
            $this->site,
            $this->site->getLanguageById(1),
            true,
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
            strategy: CacheWarmup\Crawler\Strategy\SortByPriorityStrategy::getName(),
        );

        self::assertEquals(new Src\Result\CacheWarmupResult($cacheWarmupResult), $actual);
        self::assertEquals($expected, Tests\Functional\Fixtures\Classes\DummyCrawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function warmupDispatchesBeforeCacheWarmupEvent(): void
    {
        $this->mockSitemapResponse('en');

        $site = new Src\ValueObject\Request\SiteWarmupRequest($this->site);
        $page = new Src\ValueObject\Request\PageWarmupRequest(1);

        $this->subject->warmup(
            [$site],
            [$page],
            50,
            CacheWarmup\Crawler\Strategy\SortByPriorityStrategy::getName(),
        );

        self::assertCount(2, $this->eventDispatcher->dispatchedEvents);

        $actual = $this->eventDispatcher->dispatchedEvents[0];

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
            true,
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
                new Src\ValueObject\Request\PageWarmupRequest(1),
            ],
            50,
            CacheWarmup\Crawler\Strategy\SortByPriorityStrategy::getName(),
        );

        self::assertCount(2, $this->eventDispatcher->dispatchedEvents);

        $actual = $this->eventDispatcher->dispatchedEvents[1];

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

    /**
     * @param class-string<CacheWarmup\Crawler\CrawlerInterface>|CacheWarmup\Crawler\CrawlerInterface $crawler
     * @param array<string, mixed> $options
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('setCrawlerSetsGivenCrawlerDataProvider')]
    public function setCrawlerSetsGivenCrawler(
        string|CacheWarmup\Crawler\CrawlerInterface $crawler,
        array $options,
        CacheWarmup\Crawler\CrawlerInterface $expected,
    ): void {
        $this->subject->setCrawler($crawler, $options);

        self::assertEquals($expected, $this->subject->getCrawler());
    }

    /**
     * @return Generator<string, array{
     *     class-string<CacheWarmup\Crawler\CrawlerInterface>|CacheWarmup\Crawler\CrawlerInterface,
     *     array<string, mixed>,
     *     CacheWarmup\Crawler\CrawlerInterface,
     * }>
     */
    public static function setCrawlerSetsGivenCrawlerDataProvider(): Generator
    {
        $crawler = new Tests\Functional\Fixtures\Classes\DummyVerboseCrawler();

        $crawlerWithOptions = clone $crawler;
        $crawlerWithOptions->setOptions(['foo' => 'baz']);

        yield 'crawler by class name, without options' => [
            Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::class,
            [],
            $crawler,
        ];
        yield 'crawler by class name, with options' => [
            Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::class,
            ['foo' => 'baz'],
            $crawlerWithOptions,
        ];
        yield 'crawler by object, without options' => [
            $crawler,
            [],
            $crawler,
        ];
        yield 'crawler by object, with options' => [
            $crawler,
            ['foo' => 'baz'],
            $crawlerWithOptions,
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Tests\Functional\Fixtures\Classes\DummyCrawler::reset();
    }
}
