<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2026 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Command;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use mteu\TypedExtConf\Provider\TypedExtensionConfigurationProvider;
use PHPUnit\Framework;
use Symfony\Component\Console;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * WarmupCommandTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Command\WarmupCommand::class)]
final class WarmupCommandTest extends TestingFramework\Core\Functional\FunctionalTestCase
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
                'verboseCrawler' => Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::class,
            ],
        ],
    ];

    private Core\Site\Entity\Site $site;
    private Typo3SitemapLocator\Cache\SitemapsCache $cache;
    private Core\Configuration\ExtensionConfiguration $extensionConfiguration;
    private Tests\Functional\Fixtures\Classes\DummyEventDispatcher $eventDispatcher;
    private Core\Site\SiteFinder $siteFinder;
    private Console\Tester\CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/pages.csv');

        // Create site configuration
        $this->site = $this->createSite();

        // Set up backend user
        $backendUser = $this->setUpBackendUser(3);
        $GLOBALS['LANG'] = $this->get(Core\Localization\LanguageServiceFactory::class)
            ->createFromUserPreferences($backendUser);

        $this->createMockHandler();

        $this->cache = $this->get(Typo3SitemapLocator\Cache\SitemapsCache::class);
        $this->extensionConfiguration = $this->get(Core\Configuration\ExtensionConfiguration::class);
        $this->eventDispatcher = new Tests\Functional\Fixtures\Classes\DummyEventDispatcher();
        $this->siteFinder = $this->get(Core\Site\SiteFinder::class);
        $this->commandTester = $this->createCommandTester();

        // Inject client mock handler when config is resolved
        $this->eventDispatcher->addListener(
            CacheWarmup\Event\Config\ConfigResolved::class,
            function (CacheWarmup\Event\Config\ConfigResolved $event) {
                $event->config()->setClientOption('handler', $this->handler);
            },
        );
    }

    #[Framework\Attributes\Test]
    public function executeThrowsExceptionIfNeitherPagesNorSitesArePassed(): void
    {
        $this->expectException(Console\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Neither sitemaps nor URLs are defined.');

        $this->commandTester->execute([]);
    }

    #[Framework\Attributes\Test]
    public function executeCrawlsGivenPages(): void
    {
        $expected = [
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/de/'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/fr/'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-1'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/de/subsite-1-l-1'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-2'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-2/subsite-2-1'),
        ];

        $this->commandTester->execute([
            '--pages' => ['1', '2', '3', '4'],
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $this->commandTester->getStatusCode());
        self::assertEquals($expected, Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function executeCrawlsGivenSitesByRootPageId(): void
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

        $this->commandTester->execute([
            '--sites' => ['1'],
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $this->commandTester->getStatusCode());
        self::assertEquals($expected, Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function executeCrawlsGivenSitesByIdentifier(): void
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

        $this->commandTester->execute([
            '--sites' => ['test-site'],
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $this->commandTester->getStatusCode());
        self::assertEquals($expected, Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function executeCrawlsAllAvailableSites(): void
    {
        // First site
        $this->mockSitemapResponse('en', 'de', 'fr');

        // Second site
        $this->createSite('https://typo3-testing.local/foo/', 'test-site-2');
        $this->mockSitemapResponse('en_2', 'de_2', 'fr_2');

        // Force cache recreation after second site was created
        $this->siteFinder->getAllSites(false);

        $this->commandTester->execute([
            '--sites' => ['all'],
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $this->commandTester->getStatusCode());
        self::assertCount(12, Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function executeRespectsConfigurationFile(): void
    {
        $this->commandTester->execute([
            '--pages' => ['1', '2', '3', '4'],
            // Provides exclude pattern for all sitemaps and URLs
            '--config' => 'EXT:warming/Tests/Functional/Fixtures/Files/cache-warmup.yaml',
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $this->commandTester->getStatusCode());
        self::assertSame([], Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function executeRespectsGivenLanguages(): void
    {
        $expected = [
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/de/'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/de/subsite-1-l-1'),
        ];

        $this->commandTester->execute([
            '--languages' => ['1'],
            '--pages' => ['1', '2', '3', '4'],
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $this->commandTester->getStatusCode());
        self::assertEquals($expected, Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function executeCrawlsAllLanguages(): void
    {
        $expected = [
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/de/'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/fr/'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-1'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/de/subsite-1-l-1'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-2'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-2/subsite-2-1'),
        ];

        $this->commandTester->execute([
            '--languages' => ['1', '-1'],
            '--pages' => ['1', '2', '3', '4'],
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $this->commandTester->getStatusCode());
        self::assertEquals($expected, Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function executeRespectsLimit(): void
    {
        $expected = [
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/'),
        ];

        $this->commandTester->execute([
            '--limit' => 1,
            '--pages' => ['1', '2'],
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $this->commandTester->getStatusCode());
        self::assertEquals($expected, Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function executeRespectsExcludePatterns(): void
    {
        $originalConfiguration = $this->extensionConfiguration->get(Src\Extension::KEY);
        $newConfiguration = $originalConfiguration;
        $newConfiguration['exclude'] = '*/de/*, #/subsite-2-1$#';

        $this->extensionConfiguration->set(Src\Extension::KEY, $newConfiguration);

        $expected = [
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/fr/'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-1'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-2'),
        ];

        $commandTester = $this->createCommandTester();
        $commandTester->execute([
            '--pages' => ['1', '2', '3', '4'],
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $commandTester->getStatusCode());
        self::assertEquals($expected, Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::$crawledUrls);

        $this->extensionConfiguration->set(Src\Extension::KEY, $originalConfiguration);
    }

    #[Framework\Attributes\Test]
    public function executeRespectsCrawlerOptions(): void
    {
        $originalConfiguration = $this->extensionConfiguration->get(Src\Extension::KEY);
        $newConfiguration = $originalConfiguration;
        $newConfiguration['verboseCrawlerOptions'] = '{"foo":"baz"}';

        $this->extensionConfiguration->set(Src\Extension::KEY, $newConfiguration);

        $commandTester = $this->createCommandTester();
        $commandTester->execute([
            '--pages' => ['1'],
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $commandTester->getStatusCode());
        self::assertSame(['foo' => 'baz'], Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::$options);

        $this->extensionConfiguration->set(Src\Extension::KEY, $originalConfiguration);
    }

    #[Framework\Attributes\Test]
    public function executeRespectsParserOptions(): void
    {
        $this->mockSitemapResponse('en');

        $originalConfiguration = $this->extensionConfiguration->get(Src\Extension::KEY);
        $newConfiguration = $originalConfiguration;
        $newConfiguration['parserOptions'] = '{"request_options":{"auth":["username","password"]}}';

        $this->extensionConfiguration->set(Src\Extension::KEY, $newConfiguration);

        $commandTester = $this->createCommandTester();
        $commandTester->execute([
            '--sites' => ['1'],
            '--languages' => ['0'],
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $commandTester->getStatusCode());
        self::assertSame(['username', 'password'], $this->handler->getLastOptions()['auth'] ?? null);

        $this->extensionConfiguration->set(Src\Extension::KEY, $originalConfiguration);
    }

    #[Framework\Attributes\Test]
    public function executeRespectsGivenStrategy(): void
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

        $this->commandTester->execute([
            '--sites' => ['1'],
            '--strategy' => CacheWarmup\Crawler\Strategy\SortByPriorityStrategy::getName(),
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $this->commandTester->getStatusCode());
        self::assertEquals($expected, Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function executeRespectsCustomStrategy(): void
    {
        $this->mockSitemapResponse('en', 'de', 'fr');

        $this->commandTester->execute([
            '--sites' => ['1'],
            '--strategy' => Tests\Unit\Fixtures\DummyCrawlingStrategy::getName(),
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $this->commandTester->getStatusCode());
        self::assertStringContainsString(
            'Successfully warmed up caches for 6 URLs.',
            $this->commandTester->getDisplay(),
        );
    }

    #[Framework\Attributes\Test]
    public function executeRespectsConfiguredStrategy(): void
    {
        $this->mockSitemapResponse('en', 'de', 'fr');

        // Overwrite extension configuration
        $this->extensionConfiguration->set(Src\Extension::KEY, [
            'strategy' => CacheWarmup\Crawler\Strategy\SortByPriorityStrategy::getName(),
            'verboseCrawler' => Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::class,
        ]);

        // Recreate command tester to reconfigure command
        $this->commandTester = $this->createCommandTester();

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

        $this->commandTester->execute([
            '--sites' => ['1'],
            '--strategy' => CacheWarmup\Crawler\Strategy\SortByPriorityStrategy::getName(),
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $this->commandTester->getStatusCode());
        self::assertEquals($expected, Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::$crawledUrls);

        // Reset extension configuration
        $this->extensionConfiguration->set(Src\Extension::KEY, [
            'verboseCrawler' => Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::class,
        ]);
    }

    #[Framework\Attributes\Test]
    public function executeHandsOverEventDispatcher(): void
    {
        $this->commandTester->execute([
            '--pages' => ['1'],
        ]);

        $dispatchedEvents = $this->eventDispatcher->dispatchedEvents;

        self::assertCount(9, $dispatchedEvents);
        self::assertInstanceOf(CacheWarmup\Event\Config\ConfigResolved::class, $dispatchedEvents[0]);
        self::assertInstanceOf(CacheWarmup\Event\Http\ClientConstructed::class, $dispatchedEvents[1]);
        self::assertInstanceOf(CacheWarmup\Event\Crawler\CrawlerConstructed::class, $dispatchedEvents[2]);
        self::assertInstanceOf(CacheWarmup\Event\Parser\ParserConstructed::class, $dispatchedEvents[3]);
        self::assertInstanceOf(CacheWarmup\Event\Parser\UrlAdded::class, $dispatchedEvents[4]);
        self::assertInstanceOf(CacheWarmup\Event\Parser\UrlAdded::class, $dispatchedEvents[5]);
        self::assertInstanceOf(CacheWarmup\Event\Parser\UrlAdded::class, $dispatchedEvents[6]);
        self::assertInstanceOf(CacheWarmup\Event\Crawler\CrawlingStarted::class, $dispatchedEvents[7]);
        self::assertInstanceOf(CacheWarmup\Event\Crawler\CrawlingFinished::class, $dispatchedEvents[8]);
    }

    #[Framework\Attributes\Test]
    public function executeFailsOnCacheWarmupFailureWithStrictModeEnable(): void
    {
        Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::$failOnNextIteration = true;

        $this->commandTester->execute([
            '--pages' => ['1'],
            '--strict' => true,
        ]);

        self::assertGreaterThan(0, $this->commandTester->getStatusCode());
    }

    protected function tearDown(): void
    {
        $this->cache->remove($this->site, $this->site->getLanguageById(0));
        $this->cache->remove($this->site, $this->site->getLanguageById(1));
        $this->cache->remove($this->site, $this->site->getLanguageById(2));

        Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::reset();

        parent::tearDown();
    }

    private function createCommandTester(): Console\Tester\CommandTester
    {
        $extensionConfigurationProvider = new TypedExtensionConfigurationProvider(
            $this->extensionConfiguration,
            $this->get(Src\Mapper\ConfigurationMapperFactory::class),
        );

        return new Console\Tester\CommandTester(
            new Src\Command\WarmupCommand(
                $extensionConfigurationProvider->get(Src\Configuration\Configuration::class),
                new CacheWarmup\Crawler\Strategy\CrawlingStrategyFactory([
                    CacheWarmup\Crawler\Strategy\SortByChangeFrequencyStrategy::class,
                    CacheWarmup\Crawler\Strategy\SortByLastModificationDateStrategy::class,
                    CacheWarmup\Crawler\Strategy\SortByPriorityStrategy::class,
                    Tests\Unit\Fixtures\DummyCrawlingStrategy::class,
                ]),
                new Typo3SitemapLocator\Sitemap\SitemapLocator(
                    $this->get(Typo3SitemapLocator\Http\Client\ClientFactory::class),
                    $this->cache,
                    $this->eventDispatcher,
                    [new Typo3SitemapLocator\Sitemap\Provider\DefaultProvider()],
                ),
                $this->get(Src\Domain\Repository\SiteRepository::class),
                $this->get(Src\Domain\Repository\SiteLanguageRepository::class),
                $this->eventDispatcher,
                $this->get(Core\Package\PackageManager::class),
                $this->get(Src\Http\Message\PageUriBuilder::class),
                $this->get(Src\Http\Message\Request\RequestOptions::class),
            ),
        );
    }
}
