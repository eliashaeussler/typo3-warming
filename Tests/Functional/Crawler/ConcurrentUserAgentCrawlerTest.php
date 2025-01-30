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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Crawler;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\TransientLogger;
use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * ConcurrentUserAgentCrawlerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Crawler\ConcurrentUserAgentCrawler::class)]
final class ConcurrentUserAgentCrawlerTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\Functional\ClientMockTrait;
    use Tests\Functional\SiteTrait;

    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'warming',
    ];

    private Frontend\Http\Application&Framework\MockObject\MockObject $applicationMock;
    private Src\Crawler\ConcurrentUserAgentCrawler $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/pages.csv');

        $this->createSite();
        $this->setUpBackendUser(3);

        $this->guzzleClientFactory = new Tests\Functional\Fixtures\Classes\DummyGuzzleClientFactory();
        $this->applicationMock = $this->createMock(Frontend\Http\Application::class);

        Core\Utility\GeneralUtility::addInstance(
            Src\Http\Client\ClientFactory::class,
            new Src\Http\Client\ClientFactory($this->guzzleClientFactory),
        );

        // Mock frontend application for sub request handling tests
        $container = $this->getContainer();
        self::assertInstanceOf(DependencyInjection\ContainerInterface::class, $container);
        $container->set(Frontend\Http\Application::class, $this->applicationMock);

        $this->subject = new Src\Crawler\ConcurrentUserAgentCrawler();
    }

    #[Framework\Attributes\Test]
    public function crawlCrawlsGivenUrlsAndReturnsResult(): void
    {
        $this->guzzleClientFactory->handler->append(
            $response1 = new Core\Http\Response(),
            $response2 = new Core\Http\Response(),
        );

        $urls = [
            $url1 = new Core\Http\Uri('https://typo3-testing.local/'),
            $url2 = new Core\Http\Uri('https://typo3-testing.local/de/'),
        ];

        $expected = new CacheWarmup\Result\CacheWarmupResult();
        $expected->addResult(CacheWarmup\Result\CrawlingResult::createSuccessful($url1, ['response' => $response1]));
        $expected->addResult(CacheWarmup\Result\CrawlingResult::createSuccessful($url2, ['response' => $response2]));

        self::assertEquals($expected, $this->subject->crawl($urls));
    }

    #[Framework\Attributes\Test]
    public function crawlSendsCustomUserAgentHeader(): void
    {
        $this->guzzleClientFactory->handler->append(new Core\Http\Response());

        $urls = [
            new Core\Http\Uri('https://typo3-testing.local/'),
        ];

        $this->subject->crawl($urls);

        self::assertSame(
            [$this->get(Src\Configuration\Configuration::class)->getUserAgent()],
            $this->guzzleClientFactory->handler->getLastRequest()?->getHeader('User-Agent'),
        );
    }

    #[Framework\Attributes\Test]
    public function crawlCrawlsGivenUrlsAndStreamsResponses(): void
    {
        $eventStream = new Tests\Unit\Fixtures\DummyEventStream();

        $this->subject->setStream($eventStream);

        $this->guzzleClientFactory->handler->append(
            new Core\Http\Response(),
            new Core\Http\Response(),
        );

        $urls = [
            new Core\Http\Uri('https://typo3-testing.local/'),
            new Core\Http\Uri('https://typo3-testing.local/de/'),
        ];

        $expected = [
            new Src\Http\Message\Event\WarmupProgressEvent(
                'https://typo3-testing.local/',
                [
                    'https://typo3-testing.local/',
                ],
                [],
                2,
            ),
            new Src\Http\Message\Event\WarmupProgressEvent(
                'https://typo3-testing.local/de/',
                [
                    'https://typo3-testing.local/',
                    'https://typo3-testing.local/de/',
                ],
                [],
                2,
            ),
        ];

        $this->subject->crawl($urls);

        self::assertEquals($expected, $eventStream->receivedEvents);
    }

    #[Framework\Attributes\Test]
    public function crawlLogsCrawlingResults(): void
    {
        $logger = new TransientLogger\TransientLogger();

        $this->guzzleClientFactory->handler->append(
            new Core\Http\Response(),
            new \Exception()
        );

        $urls = [
            new Core\Http\Uri('https://typo3-testing.local/'),
            new Core\Http\Uri('https://typo3-testing.local/de/'),
        ];

        $this->subject->setLogger($logger);

        $this->subject->crawl($urls);

        self::assertCount(1, $logger->getByLogLevel(TransientLogger\Log\LogLevel::Error));
        self::assertCount(1, $logger->getByLogLevel(TransientLogger\Log\LogLevel::Info));
    }

    #[Framework\Attributes\Test]
    public function crawlUsesSubRequestHandler(): void
    {
        $this->subject->setOptions(['perform_subrequests' => true]);

        $urls = [
            new Core\Http\Uri('https://typo3-testing.local/'),
            new Core\Http\Uri('https://typo3-testing.local/de/'),
        ];
        $response = new Core\Http\Response();

        $this->applicationMock->method('handle')->willReturn($response);

        $actual = $this->subject->crawl($urls);

        self::assertTrue($actual->isSuccessful());
        self::assertCount(2, $actual->getSuccessful());
        self::assertSame(['response' => $response], $actual->getSuccessful()[0]->getData());
        self::assertSame(['response' => $response], $actual->getSuccessful()[1]->getData());
    }
}
