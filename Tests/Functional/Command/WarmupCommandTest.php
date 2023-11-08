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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Command;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
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
    private Src\Configuration\Configuration $configuration;
    private Core\Configuration\ExtensionConfiguration $extensionConfiguration;
    private Console\Tester\CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/pages.csv');

        // Set up backend user
        $this->setUpBackendUser(3);
        Core\Core\Bootstrap::initializeLanguageObject();

        // Create site configuration
        $this->site = $this->createSite();

        $this->configuration = $this->get(Src\Configuration\Configuration::class);
        $this->extensionConfiguration = $this->get(Core\Configuration\ExtensionConfiguration::class);
        $this->guzzleClientFactory = new Tests\Functional\Fixtures\Classes\DummyGuzzleClientFactory();
        $this->commandTester = new Console\Tester\CommandTester(
            new Src\Command\WarmupCommand(
                new Src\Http\Client\ClientFactory(
                    $this->guzzleClientFactory,
                ),
                $this->configuration,
                $this->get(Src\Crawler\Strategy\CrawlingStrategyFactory::class),
                $this->get(Src\Sitemap\SitemapLocator::class),
                $this->get(Core\Site\SiteFinder::class),
            ),
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

        $originEN = new Src\Sitemap\SiteAwareSitemap(
            new Core\Http\Uri('https://typo3-testing.local/sitemap.xml'),
            $this->site,
            $this->site->getDefaultLanguage(),
        );
        $originDE = new Src\Sitemap\SiteAwareSitemap(
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

        $originEN = new Src\Sitemap\SiteAwareSitemap(
            new Core\Http\Uri('https://typo3-testing.local/sitemap.xml'),
            $this->site,
            $this->site->getDefaultLanguage(),
        );
        $originDE = new Src\Sitemap\SiteAwareSitemap(
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
            '--sites' => [self::$testSiteIdentifier],
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $this->commandTester->getStatusCode());
        self::assertEquals($expected, Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::$crawledUrls);
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
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-1'),
            new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-2'),
        ];

        $this->commandTester->execute([
            '--pages' => ['1', '2', '3', '4'],
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $this->commandTester->getStatusCode());
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

        $this->commandTester->execute([
            '--pages' => ['1'],
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $this->commandTester->getStatusCode());
        self::assertEquals(['foo' => 'baz'], Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::$options);

        $this->extensionConfiguration->set(Src\Extension::KEY, $originalConfiguration);
    }

    #[Framework\Attributes\Test]
    public function executeRespectsParserClientOptions(): void
    {
        $originalConfiguration = $this->extensionConfiguration->get(Src\Extension::KEY);
        $newConfiguration = $originalConfiguration;
        $newConfiguration['parserClientOptions'] = '{"foo":"baz"}';

        $this->extensionConfiguration->set(Src\Extension::KEY, $newConfiguration);

        $this->commandTester->execute([
            '--pages' => ['1'],
        ]);

        self::assertSame(Console\Command\Command::SUCCESS, $this->commandTester->getStatusCode());
        self::assertEquals('baz', $this->guzzleClientFactory->lastOptions['foo'] ?? null);

        $this->extensionConfiguration->set(Src\Extension::KEY, $originalConfiguration);
    }

    #[Framework\Attributes\Test]
    public function executeRespectsStrategy(): void
    {
        $this->mockSitemapResponse('en', 'de', 'fr');

        $originEN = new Src\Sitemap\SiteAwareSitemap(
            new Core\Http\Uri('https://typo3-testing.local/sitemap.xml'),
            $this->site,
            $this->site->getDefaultLanguage(),
        );
        $originDE = new Src\Sitemap\SiteAwareSitemap(
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
        parent::tearDown();

        Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::reset();
    }
}
