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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Queue;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * CacheWarmupQueueTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Queue\CacheWarmupQueue::class)]
final class CacheWarmupQueueTest extends TestingFramework\Core\Functional\FunctionalTestCase
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
            ],
        ],
    ];

    private Core\Site\Entity\Site $site;
    private Src\Queue\CacheWarmupQueue $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/pages.csv');

        // Create site configuration
        $this->site = $this->createSite();

        // Set up backend user
        $backendUser = $this->setUpBackendUser(3);
        $GLOBALS['LANG'] = $this->get(Core\Localization\LanguageServiceFactory::class)->createFromUserPreferences($backendUser);

        $eventDispatcher = new Core\EventDispatcher\NoopEventDispatcher();

        $this->subject = new Src\Queue\CacheWarmupQueue(
            new Src\Service\CacheWarmupService(
                new CacheWarmup\Http\Client\ClientFactory($eventDispatcher, $this->getClientOptions()),
                $this->get(Src\Configuration\Configuration::class),
                $eventDispatcher,
                $this->get(Typo3SitemapLocator\Sitemap\SitemapLocator::class),
                $this->get(Src\Http\Message\PageUriBuilder::class),
            ),
        );
    }

    #[Framework\Attributes\Test]
    public function enqueueAddsSiteWarmupRequestToQueue(): void
    {
        $request = new Src\ValueObject\Request\SiteWarmupRequest($this->site);

        $this->subject->enqueue($request);

        $actual = $this->subject->wrapInWarmupRequest();

        self::assertSame([$request], $actual->getSites());
        self::assertSame([], $actual->getPages());
    }

    #[Framework\Attributes\Test]
    public function enqueueAddsPageWarmupRequestToQueue(): void
    {
        $request = new Src\ValueObject\Request\PageWarmupRequest(1);

        $this->subject->enqueue($request);

        $actual = $this->subject->wrapInWarmupRequest();

        self::assertSame([], $actual->getSites());
        self::assertSame([$request], $actual->getPages());
    }

    #[Framework\Attributes\Test]
    public function processDoesNothingIfQueueIsEmpty(): void
    {
        $this->subject->process();

        self::assertSame([], Tests\Functional\Fixtures\Classes\DummyCrawler::$crawledUrls);
    }

    #[Framework\Attributes\Test]
    public function processRunsCacheWarmupForQueuedSitesAndPages(): void
    {
        $this->mockSitemapResponse('de');

        $this->subject->enqueue(new Src\ValueObject\Request\SiteWarmupRequest($this->site));
        $this->subject->enqueue(new Src\ValueObject\Request\PageWarmupRequest(1));

        $this->subject->process();

        self::assertSame(
            [
                'https://typo3-testing.local/de/',
                'https://typo3-testing.local/de/subsite-1-l-1',
                'https://typo3-testing.local/',
            ],
            array_map('strval', Tests\Functional\Fixtures\Classes\DummyCrawler::$crawledUrls),
        );
        self::assertTrue($this->subject->isEmpty());
    }

    protected function tearDown(): void
    {
        Tests\Functional\Fixtures\Classes\DummyCrawler::reset();

        parent::tearDown();
    }
}
