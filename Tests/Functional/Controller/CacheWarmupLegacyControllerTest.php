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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Controller;

use CuyZ\Valinor;
use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use Psr\Log;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * CacheWarmupLegacyControllerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Controller\CacheWarmupLegacyController::class)]
final class CacheWarmupLegacyControllerTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\Functional\ClientMockTrait;
    use Tests\Functional\SiteTrait;

    protected array $testExtensionsToLoad = [
        'warming',
    ];

    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'warming' => [
                'crawler' => Tests\Functional\Fixtures\Classes\DummyCrawler::class,
            ],
        ],
    ];

    protected Core\Site\Entity\Site $site;
    protected Tests\Functional\Fixtures\Classes\DummyGuzzleClientFactory $guzzleClientFactory;
    protected Src\Controller\CacheWarmupLegacyController $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/pages.csv');

        // Set up backend user
        $this->setUpBackendUser(3);
        Core\Core\Bootstrap::initializeLanguageObject();

        $this->site = $this->createSite();
        $this->guzzleClientFactory = new Tests\Functional\Fixtures\Classes\DummyGuzzleClientFactory();
        $this->subject = new Src\Controller\CacheWarmupLegacyController(
            new Log\NullLogger(),
            $this->get(Valinor\Mapper\TreeMapper::class),
            $this->get(Src\Http\Message\ResponseFactory::class),
            new Src\Service\CacheWarmupService(
                new Src\Http\Client\ClientFactory($this->guzzleClientFactory),
                $this->get(Src\Configuration\Configuration::class),
                $this->get(CacheWarmup\Crawler\CrawlerFactory::class),
                $this->get(Src\Crawler\Strategy\CrawlingStrategyFactory::class),
                new Core\EventDispatcher\NoopEventDispatcher(),
                $this->get(Src\Sitemap\SitemapLocator::class),
            ),
        );
    }

    #[Framework\Attributes\Test]
    public function controllerReturnsBadRequestResponseIfRequestParametersAreInvalid(): void
    {
        $request = new Core\Http\ServerRequest();
        $request = $request->withQueryParams(['sites' => 'foo']);

        $actual = ($this->subject)($request);

        self::assertEquals(
            new Core\Http\Response(null, 400, [], 'Invalid request parameters'),
            $actual,
        );
    }

    #[Framework\Attributes\Test]
    public function controllerPerformsCacheWarmupAndReturnsJsonResponse(): void
    {
        $this->mockSitemapResponse('en', 'de', 'fr');

        $request = new Core\Http\ServerRequest();
        $request = $request->withQueryParams([
            'sites' => [
                [
                    'site' => $this->site->getIdentifier(),
                ],
            ],
        ]);

        $actual = ($this->subject)($request);

        self::assertCount(4, Tests\Functional\Fixtures\Classes\DummyCrawler::$crawledUrls);
        self::assertInstanceOf(Core\Http\JsonResponse::class, $actual);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Tests\Functional\Fixtures\Classes\DummyCrawler::reset();
    }
}
