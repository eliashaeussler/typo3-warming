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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Crawler;

use EliasHaeussler\Typo3Warming\Configuration\Configuration;
use EliasHaeussler\Typo3Warming\Crawler\OutputtingUserAgentCrawler;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Console\Output\NullOutput;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * OutputtingUserAgentCrawlerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class OutputtingUserAgentCrawlerTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/warming',
    ];

    /**
     * @var MockHandler
     */
    protected $mockHandler;

    /**
     * @var OutputtingUserAgentCrawler
     */
    protected $subject;

    /**
     * @var Configuration
     */
    protected $configuration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();

        GeneralUtility::addInstance(Client::class, new Client(['handler' => $this->mockHandler]));

        $this->subject = new OutputtingUserAgentCrawler();
        $this->subject->setOutput(new NullOutput());
        $this->configuration = GeneralUtility::makeInstance(Configuration::class);
    }

    /**
     * @test
     */
    public function crawlIncludesCustomUserAgentHeaderInRequests(): void
    {
        $this->mockHandler->append(new Response());

        $this->subject->crawl([new Uri('https://www.example.com')]);

        $lastRequest = $this->mockHandler->getLastRequest();

        self::assertInstanceOf(RequestInterface::class, $lastRequest);
        self::assertSame($this->configuration->getUserAgent(), $lastRequest->getHeader('User-Agent')[0]);
    }

    /**
     * @test
     */
    public function crawlUsesGetMethodInRequests(): void
    {
        $this->mockHandler->append(new Response());

        $this->subject->crawl([new Uri('https://www.example.com')]);

        $lastRequest = $this->mockHandler->getLastRequest();

        self::assertInstanceOf(RequestInterface::class, $lastRequest);
        self::assertSame('GET', $lastRequest->getMethod());
    }
}
