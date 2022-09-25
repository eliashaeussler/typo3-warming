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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Request;

use EliasHaeussler\CacheWarmup\CrawlingState;
use EliasHaeussler\Typo3Warming\Controller\CacheWarmupController;
use EliasHaeussler\Typo3Warming\Request\WarmupRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * WarmupRequestTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class WarmupRequestTest extends UnitTestCase
{
    /**
     * @var WarmupRequest
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new WarmupRequest('foo', CacheWarmupController::MODE_SITE, 1, 7);
    }

    /**
     * @test
     */
    public function getIdReturnsId(): void
    {
        self::assertSame('foo', $this->subject->getId());
    }

    /**
     * @test
     */
    public function getModeReturnsMode(): void
    {
        self::assertSame(CacheWarmupController::MODE_SITE, $this->subject->getMode());
    }

    /**
     * @test
     */
    public function getLanguageIdReturnsLanguageId(): void
    {
        self::assertSame(1, $this->subject->getLanguageId());
    }

    /**
     * @test
     */
    public function getPageIdReturnsPageId(): void
    {
        self::assertSame(7, $this->subject->getPageId());
    }

    /**
     * @test
     */
    public function getTotalReturnsTotalNumberOfRequestedUrls(): void
    {
        $this->subject->setRequestedUrls([
            new Uri('https://www.example.com'),
            new Uri('https://www.example.com/foo'),
            new Uri('https://www.example.com/baz'),
        ]);

        self::assertSame(3, $this->subject->getTotal());
    }

    /**
     * @test
     */
    public function getProcessedReturnsNumberOfProcessedUrls(): void
    {
        self::assertSame(0, $this->subject->getProcessed());

        $this->subject->addCrawlingState(CrawlingState::createSuccessful(new Uri('https://www.example.com')));
        $this->subject->addCrawlingState(CrawlingState::createSuccessful(new Uri('https://www.example.com/foo')));
        $this->subject->addCrawlingState(CrawlingState::createSuccessful(new Uri('https://www.example.com/baz')));

        self::assertSame(3, $this->subject->getProcessed());
    }

    /**
     * @test
     */
    public function isSuccessfulReturnsOverallCrawlingState(): void
    {
        self::assertTrue($this->subject->isSuccessful());

        $this->subject->addCrawlingState(CrawlingState::createSuccessful(new Uri('https://www.example.com')));
        $this->subject->addCrawlingState(CrawlingState::createSuccessful(new Uri('https://www.example.com/foo')));
        $this->subject->addCrawlingState(CrawlingState::createSuccessful(new Uri('https://www.example.com/baz')));

        self::assertTrue($this->subject->isSuccessful());

        $this->subject->addCrawlingState(CrawlingState::createFailed(new Uri('https://www.example.com/error')));

        self::assertFalse($this->subject->isSuccessful());
    }

    /**
     * @test
     */
    public function getRequestedUrlsReturnsRequestedUrls(): void
    {
        $requestedUrls = [
            new Uri('https://www.example.com'),
            new Uri('https://www.example.com/foo'),
            new Uri('https://www.example.com/baz'),
        ];
        $this->subject->setRequestedUrls($requestedUrls);

        self::assertSame($requestedUrls, $this->subject->getRequestedUrls());
    }

    /**
     * @test
     */
    public function getCrawlingStatesReturnsCrawlingStates(): void
    {
        $this->subject->addCrawlingState($crawlingState1 = CrawlingState::createSuccessful(new Uri('https://www.example.com')));
        $this->subject->addCrawlingState($crawlingState2 = CrawlingState::createSuccessful(new Uri('https://www.example.com/foo')));
        $this->subject->addCrawlingState($crawlingState3 = CrawlingState::createSuccessful(new Uri('https://www.example.com/baz')));
        $this->subject->addCrawlingState($crawlingState4 = CrawlingState::createFailed(new Uri('https://www.example.com/error')));

        $expected = [
            $crawlingState1,
            $crawlingState2,
            $crawlingState3,
            $crawlingState4,
        ];

        self::assertSame($expected, $this->subject->getCrawlingStates());
    }

    /**
     * @test
     */
    public function addCrawlingStateAddsCrawlingStateAndTriggersUpdate(): void
    {
        $updateTriggered = false;
        $crawlingState = CrawlingState::createSuccessful(new Uri('https://www.example.com'));
        $this->subject->setUpdateCallback(function () use (&$updateTriggered): void {
            $updateTriggered = true;
        });
        $this->subject->addCrawlingState($crawlingState);

        self::assertSame([$crawlingState], $this->subject->getCrawlingStates());
        self::assertTrue($updateTriggered);
    }

    /**
     * @test
     */
    public function getSuccessfulCrawlsReturnsSuccessfullyCrawledUrls(): void
    {
        $this->subject->addCrawlingState($crawlingState1 = CrawlingState::createSuccessful(new Uri('https://www.example.com')));
        $this->subject->addCrawlingState($crawlingState2 = CrawlingState::createSuccessful(new Uri('https://www.example.com/foo')));
        $this->subject->addCrawlingState($crawlingState3 = CrawlingState::createSuccessful(new Uri('https://www.example.com/baz')));
        $this->subject->addCrawlingState(CrawlingState::createFailed(new Uri('https://www.example.com/error')));

        $expected = [
            $crawlingState1,
            $crawlingState2,
            $crawlingState3,
        ];

        self::assertSame($expected, iterator_to_array($this->subject->getSuccessfulCrawls()));
    }

    /**
     * @test
     */
    public function getFailedCrawlsReturnsFailedUrls(): void
    {
        $this->subject->addCrawlingState(CrawlingState::createSuccessful(new Uri('https://www.example.com')));
        $this->subject->addCrawlingState(CrawlingState::createSuccessful(new Uri('https://www.example.com/foo')));
        $this->subject->addCrawlingState(CrawlingState::createSuccessful(new Uri('https://www.example.com/baz')));
        $this->subject->addCrawlingState($crawlingState4 = CrawlingState::createFailed(new Uri('https://www.example.com/error')));

        $expected = [
            $crawlingState4,
        ];

        self::assertSame($expected, iterator_to_array($this->subject->getFailedCrawls()));
    }

    /**
     * @test
     */
    public function getSiteReturnsSite(): void
    {
        self::assertNull($this->subject->getSite());

        $site = new Site('foo', 7, []);
        $this->subject->setSite($site);

        self::assertSame($site, $this->subject->getSite());
    }

    /**
     * @test
     */
    public function getUpdateCallbackReturnsUpdateCallback(): void
    {
        self::assertNull($this->subject->getUpdateCallback());

        $callback = function (): bool {
            return true;
        };
        $this->subject->setUpdateCallback($callback);

        self::assertIsCallable($this->subject->getUpdateCallback());
        self::assertSame($callback, $this->subject->getUpdateCallback());
    }
}
