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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\EventListener;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming as Src;
use GuzzleHttp\Exception;
use PHPUnit\Framework;
use Psr\Http\Message;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * UrlMetadataListenerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\EventListener\UrlMetadataListener::class)]
final class UrlMetadataListenerTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    private Src\Http\Message\UrlMetadataFactory $urlMetadataFactory;
    private Src\EventListener\UrlMetadataListener $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->urlMetadataFactory = new Src\Http\Message\UrlMetadataFactory();
        $this->subject = new Src\EventListener\UrlMetadataListener($this->urlMetadataFactory);
    }

    #[Framework\Attributes\Test]
    public function onSuccessDoesNothingIfResponseDoesNotProvideUrlMetadata(): void
    {
        $event = $this->createSucceededEvent(new Core\Http\Response());

        $expected = $event->result();

        $this->subject->onSuccess($event);

        self::assertSame($expected, $event->result());
    }

    #[Framework\Attributes\Test]
    public function onSuccessAddsUrlMetadataToResultData(): void
    {
        $event = $this->createSucceededEvent();

        $expected = CacheWarmup\Result\CrawlingResult::createSuccessful(
            $event->result()->getUri(),
            [
                'urlMetadata' => new Src\Http\Message\UrlMetadata(1, '0', 1),
            ],
        );

        $this->subject->onSuccess($event);

        self::assertEquals($expected, $event->result());
    }

    #[Framework\Attributes\Test]
    public function onFailureDoesNothingIfExceptionIsNotSupported(): void
    {
        $event = $this->createFailedEvent(new \Exception());

        $expected = $event->result();

        $this->subject->onFailure($event);

        self::assertSame($expected, $event->result());
    }

    #[Framework\Attributes\Test]
    public function onFailureDoesNothingIfExceptionHasNoResponseAttached(): void
    {
        $event = $this->createFailedEvent(
            new Exception\RequestException('Something went wrong', new Core\Http\Request()),
        );

        $expected = $event->result();

        $this->subject->onFailure($event);

        self::assertSame($expected, $event->result());
    }

    #[Framework\Attributes\Test]
    public function onFailureDoesNothingIfResponseFromExceptionDoesNotProvideUrlMetadata(): void
    {
        $event = $this->createFailedEvent(
            new Exception\RequestException('Something went wrong', new Core\Http\Request(), new Core\Http\Response()),
        );

        $expected = $event->result();

        $this->subject->onFailure($event);

        self::assertSame($expected, $event->result());
    }

    #[Framework\Attributes\Test]
    public function onFailureAddsUrlMetadataToResultData(): void
    {
        $event = $this->createFailedEvent();

        $expected = CacheWarmup\Result\CrawlingResult::createFailed(
            $event->result()->getUri(),
            [
                'urlMetadata' => new Src\Http\Message\UrlMetadata(1, '0', 1),
            ],
        );

        $this->subject->onFailure($event);

        self::assertEquals($expected, $event->result());
    }

    private function createSucceededEvent(
        ?Message\ResponseInterface $response = null,
    ): CacheWarmup\Event\Crawler\UrlCrawlingSucceeded {
        $uri = new Core\Http\Uri('https://typo3-testing.local/');
        $response ??= $this->createEnrichedResponse();
        $result = CacheWarmup\Result\CrawlingResult::createSuccessful($uri);

        return new CacheWarmup\Event\Crawler\UrlCrawlingSucceeded($uri, $response, $result);
    }

    private function createFailedEvent(?\Throwable $exception = null): CacheWarmup\Event\Crawler\UrlCrawlingFailed
    {
        $uri = new Core\Http\Uri('https://typo3-testing.local/');
        $exception ??= new Exception\RequestException(
            'Something went wrong',
            new Core\Http\Request(),
            $this->createEnrichedResponse(),
        );
        $result = CacheWarmup\Result\CrawlingResult::createFailed($uri);

        return new CacheWarmup\Event\Crawler\UrlCrawlingFailed($uri, $exception, $result);
    }

    private function createEnrichedResponse(): Core\Http\Response
    {
        return $this->urlMetadataFactory->enrichResponse(
            new Core\Http\Response(),
            new Src\Http\Message\UrlMetadata(1, '0', 1),
        );
    }
}
