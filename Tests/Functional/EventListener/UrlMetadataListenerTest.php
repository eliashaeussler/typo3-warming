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
    protected array $coreExtensionsToLoad = [
        'belog',
    ];

    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'typed_extconf',
        'warming',
    ];

    private Src\Http\Message\UrlMetadataFactory $urlMetadataFactory;
    private Src\EventListener\UrlMetadataListener $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/pages.csv');

        $this->urlMetadataFactory = $this->get(Src\Http\Message\UrlMetadataFactory::class);
        $this->subject = $this->get(Src\EventListener\UrlMetadataListener::class);

        $serverRequest = new Core\Http\ServerRequest('https://typo3-testing.local');
        $serverRequest = $serverRequest->withAttribute('applicationType', Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_BE);

        $GLOBALS['TYPO3_REQUEST'] = $serverRequest;
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
                'pageActions' => [],
            ],
        );

        $this->subject->onSuccess($event);

        self::assertEquals($expected, $event->result());
    }

    #[Framework\Attributes\Test]
    public function onSuccessDoesNotAddPageActionsIfNoPageIdIsAvailableInUrlMetadata(): void
    {
        $response = $this->createEnrichedResponse(null);
        $event = $this->createSucceededEvent($response);

        $expected = CacheWarmup\Result\CrawlingResult::createSuccessful(
            $event->result()->getUri(),
            [
                'urlMetadata' => new Src\Http\Message\UrlMetadata(null, '0', 1),
                'pageActions' => [],
            ],
        );

        $this->subject->onSuccess($event);

        self::assertEquals($expected, $event->result());
    }

    #[Framework\Attributes\Test]
    public function onSuccessDoesNotAddPageActionsIfNoRequestIsAvailable(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);

        $response = $this->createEnrichedResponse();
        $event = $this->createSucceededEvent($response);

        $expected = CacheWarmup\Result\CrawlingResult::createSuccessful(
            $event->result()->getUri(),
            [
                'urlMetadata' => new Src\Http\Message\UrlMetadata(1, '0', 1),
                'pageActions' => [],
            ],
        );

        $this->subject->onSuccess($event);

        self::assertEquals($expected, $event->result());
    }

    #[Framework\Attributes\Test]
    public function onSuccessDoesNotAddPageActionsIfRequestIsNotInBackendContext(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = $GLOBALS['TYPO3_REQUEST']->withAttribute(
            'applicationType',
            Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_FE,
        );

        $response = $this->createEnrichedResponse();
        $event = $this->createSucceededEvent($response);

        $expected = CacheWarmup\Result\CrawlingResult::createSuccessful(
            $event->result()->getUri(),
            [
                'urlMetadata' => new Src\Http\Message\UrlMetadata(1, '0', 1),
                'pageActions' => [],
            ],
        );

        $this->subject->onSuccess($event);

        self::assertEquals($expected, $event->result());
    }

    /**
     * @param list<string> $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('onSuccessAddsPageActionsToResultDataDataProvider')]
    public function onSuccessAddsPageActionsToResultData(int $backendUser, array $expected): void
    {
        $this->setUpBackendUser($backendUser);

        $event = $this->createSucceededEvent();

        $this->subject->onSuccess($event);

        $actual = array_keys($event->result()->getData()['pageActions'] ?? []);

        self::assertEqualsCanonicalizing($expected, $actual);
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
                'pageActions' => [],
            ],
        );

        $this->subject->onFailure($event);

        self::assertEquals($expected, $event->result());
    }

    #[Framework\Attributes\Test]
    public function onFailureDoesNotAddPageActionsIfNoPageIdIsAvailableInUrlMetadata(): void
    {
        $exception = $this->createRequestException(null);
        $event = $this->createFailedEvent($exception);

        $expected = CacheWarmup\Result\CrawlingResult::createFailed(
            $event->result()->getUri(),
            [
                'urlMetadata' => new Src\Http\Message\UrlMetadata(null, '0', 1),
                'pageActions' => [],
            ],
        );

        $this->subject->onFailure($event);

        self::assertEquals($expected, $event->result());
    }

    #[Framework\Attributes\Test]
    public function onFailureDoesNotAddPageActionsIfNoRequestIsAvailable(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);

        $response = $this->createRequestException();
        $event = $this->createFailedEvent($response);

        $expected = CacheWarmup\Result\CrawlingResult::createFailed(
            $event->result()->getUri(),
            [
                'urlMetadata' => new Src\Http\Message\UrlMetadata(1, '0', 1),
                'pageActions' => [],
            ],
        );

        $this->subject->onFailure($event);

        self::assertEquals($expected, $event->result());
    }

    #[Framework\Attributes\Test]
    public function onFailureDoesNotAddPageActionsIfRequestIsNotInBackendContext(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = $GLOBALS['TYPO3_REQUEST']->withAttribute(
            'applicationType',
            Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_FE,
        );

        $response = $this->createRequestException();
        $event = $this->createFailedEvent($response);

        $expected = CacheWarmup\Result\CrawlingResult::createFailed(
            $event->result()->getUri(),
            [
                'urlMetadata' => new Src\Http\Message\UrlMetadata(1, '0', 1),
                'pageActions' => [],
            ],
        );

        $this->subject->onFailure($event);

        self::assertEquals($expected, $event->result());
    }

    /**
     * @param list<string> $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('onFailureAddsPageActionsToResultDataDataProvider')]
    public function onFailureAddsPageActionsToResultData(int $backendUser, array $expected): void
    {
        $this->setUpBackendUser($backendUser);

        $event = $this->createFailedEvent();

        $this->subject->onFailure($event);

        $actual = array_keys($event->result()->getData()['pageActions'] ?? []);

        self::assertEqualsCanonicalizing($expected, $actual);
    }

    /**
     * @return \Generator<string, array{int, list<string>}>
     */
    public static function onSuccessAddsPageActionsToResultDataDataProvider(): \Generator
    {
        yield 'admin user' => [3, ['editRecord', 'viewLog']];
        yield 'user with web_layout access' => [2, ['editRecord']];
        yield 'user with system_log access' => [1, ['viewLog']];
    }

    /**
     * @return \Generator<string, array{int, list<string>}>
     */
    public static function onFailureAddsPageActionsToResultDataDataProvider(): \Generator
    {
        yield 'admin user' => [3, ['editRecord', 'viewLog']];
        yield 'user with web_layout access' => [2, ['editRecord']];
        yield 'user with system_log access' => [1, ['viewLog']];
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
        $exception ??= $this->createRequestException();
        $result = CacheWarmup\Result\CrawlingResult::createFailed($uri);

        return new CacheWarmup\Event\Crawler\UrlCrawlingFailed($uri, $exception, $result);
    }

    private function createEnrichedResponse(?int $pageId = 1): Core\Http\Response
    {
        return $this->urlMetadataFactory->enrichResponse(
            new Core\Http\Response(),
            new Src\Http\Message\UrlMetadata($pageId, '0', 1),
        );
    }

    private function createRequestException(?int $pageId = 1): Exception\RequestException
    {
        return new Exception\RequestException(
            'Something went wrong',
            new Core\Http\Request(),
            $this->createEnrichedResponse($pageId),
        );
    }
}
