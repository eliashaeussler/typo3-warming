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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Crawler;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use Exception;
use PHPUnit\Framework;
use Psr\Log;
use Symfony\Component\Console;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * OutputtingUserAgentCrawlerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Crawler\OutputtingUserAgentCrawler::class)]
final class OutputtingUserAgentCrawlerTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\Functional\ClientMockTrait;

    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'warming',
    ];

    protected bool $initializeDatabase = false;

    private Console\Output\BufferedOutput $output;
    private Src\Crawler\OutputtingUserAgentCrawler $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guzzleClientFactory = new Tests\Functional\Fixtures\Classes\DummyGuzzleClientFactory();
        $this->output = new Console\Output\BufferedOutput();

        Core\Utility\GeneralUtility::addInstance(
            Src\Http\Client\ClientFactory::class,
            new Src\Http\Client\ClientFactory($this->guzzleClientFactory),
        );

        $this->subject = new Src\Crawler\OutputtingUserAgentCrawler();
        $this->subject->setOutput($this->output);
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
    public function crawlUsesCompactProgressBarHandlerOnNonConsoleOutput(): void
    {
        $this->guzzleClientFactory->handler->append(
            new Core\Http\Response(),
            new Core\Http\Response(),
        );

        $urls = [
            new Core\Http\Uri('https://typo3-testing.local/'),
            new Core\Http\Uri('https://typo3-testing.local/de/'),
        ];

        $this->subject->crawl($urls);

        $output = $this->output->fetch();

        self::assertStringContainsString('1/2', $output);
        self::assertStringContainsString('2/2', $output);
        self::assertStringContainsString('-- no failures', $output);
    }

    #[Framework\Attributes\Test]
    public function crawlUsesVerboseProgressBarHandlerOnVerboseConsoleOutput(): void
    {
        $output = new Tests\Functional\BufferedConsoleOutput();
        $output->setVerbosity(Console\Output\OutputInterface::VERBOSITY_VERBOSE);

        $this->subject->setOutput($output);

        $this->guzzleClientFactory->handler->append(
            new Core\Http\Response(),
            new Core\Http\Response(),
        );

        $urls = [
            new Core\Http\Uri('https://typo3-testing.local/'),
            new Core\Http\Uri('https://typo3-testing.local/de/'),
        ];

        $this->subject->crawl($urls);

        self::assertStringContainsString('DONE  https://typo3-testing.local/', $output->fetch());
        self::assertStringContainsString('DONE  https://typo3-testing.local/de/', $output->fetch());
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
    public function crawlLogsCrawlingResults(): void
    {
        $logger = new Tests\Functional\Fixtures\Classes\DummyLogger();

        $this->guzzleClientFactory->handler->append(
            new Core\Http\Response(),
            new Exception()
        );

        $urls = [
            new Core\Http\Uri('https://typo3-testing.local/'),
            new Core\Http\Uri('https://typo3-testing.local/de/'),
        ];

        $this->subject->setLogger($logger);

        $this->subject->crawl($urls);

        self::assertCount(1, $logger->log[Log\LogLevel::ERROR]);
        self::assertCount(1, $logger->log[Log\LogLevel::INFO]);
    }
}
