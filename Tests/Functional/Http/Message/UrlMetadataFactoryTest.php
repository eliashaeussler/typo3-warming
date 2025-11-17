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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Http\Message;

use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * UrlMetadataFactoryTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Message\UrlMetadataFactory::class)]
final class UrlMetadataFactoryTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'typed_extconf',
        'warming',
    ];

    protected bool $initializeDatabase = false;

    private Src\Http\Message\UrlMetadataFactory $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(Src\Http\Message\UrlMetadataFactory::class);
    }

    #[Framework\Attributes\Test]
    public function createForRequestReturnsNullIfHeaderIsMissing(): void
    {
        $request = new Core\Http\ServerRequest('https://typo3-testing.local');

        self::assertNull($this->subject->createForRequest($request));
    }

    #[Framework\Attributes\Test]
    public function createForRequestReturnsNullOnUnauthorizedRequest(): void
    {
        $request = new Core\Http\ServerRequest('https://typo3-testing.local', headers: [
            'X-Warming-Request-Claim' => 'foo',
        ]);

        self::assertNull($this->subject->createForRequest($request));
    }

    #[Framework\Attributes\Test]
    public function createForRequestReturnsEmptyUrlMetadataObjectOnAuthorizedRequest(): void
    {
        $request = $this->subject->enrichRequest(new Core\Http\ServerRequest('https://typo3-testing.local'));

        self::assertEquals(new Src\Http\Message\UrlMetadata(), $this->subject->createForRequest($request));
    }

    #[Framework\Attributes\Test]
    public function createFromResponseReturnsNullIfHeaderIsMissing(): void
    {
        $response = new Core\Http\Response();

        self::assertNull($this->subject->createFromResponse($response));
    }

    #[Framework\Attributes\Test]
    public function createFromResponseReturnsNullOnInvalidHash(): void
    {
        $response = new Core\Http\Response(headers: [
            'X-Warming-Url-Metadata' => '   ',
        ]);

        self::assertNull($this->subject->createFromResponse($response));
    }

    #[Framework\Attributes\Test]
    public function createFromResponseReturnsNullOnUnsupportedResponse(): void
    {
        $response = new Core\Http\Response(headers: [
            'X-Warming-Url-Metadata' => 'foo',
        ]);

        self::assertNull($this->subject->createFromResponse($response));
    }

    #[Framework\Attributes\Test]
    public function createFromResponseReturnsNullOnInvalidResponse(): void
    {
        $headerValue = $this->buildExpectedHeaderValue('{"foo":"baz"}');
        $response = new Core\Http\Response(headers: [
            'X-Warming-Url-Metadata' => \base64_encode($headerValue),
        ]);

        self::assertNull($this->subject->createFromResponse($response));
    }

    #[Framework\Attributes\Test]
    public function createFromResponseReturnsHydratedUrlMetadataObjectOnValidResponse(): void
    {
        $urlMetadata = new Src\Http\Message\UrlMetadata(1, '0', 1);
        $response = $this->subject->enrichResponse(new Core\Http\Response(), $urlMetadata);

        self::assertEquals($urlMetadata, $this->subject->createFromResponse($response));
    }

    #[Framework\Attributes\Test]
    public function createFromResponseHeadersReturnsNullIfHeaderIsMissing(): void
    {
        self::assertNull($this->subject->createFromResponseHeaders([]));
    }

    #[Framework\Attributes\Test]
    public function createFromResponseHeaderReturnsNullOnUnsupportedResponseHeader(): void
    {
        $headers = [
            'X-Warming-Url-Metadata: foo',
        ];

        self::assertNull($this->subject->createFromResponseHeaders($headers));
    }

    #[Framework\Attributes\Test]
    public function createFromResponseHeadersReturnsNullOnInvalidResponseHeader(): void
    {
        $headerValue = $this->buildExpectedHeaderValue('{"foo":"baz"}');
        $headers = [
            'X-Warming-Url-Metadata: ' . \base64_encode($headerValue),
        ];

        self::assertNull($this->subject->createFromResponseHeaders($headers));
    }

    #[Framework\Attributes\Test]
    public function createFromResponseHeadersReturnsHydratedUrlMetadataObjectOnValidResponseHeader(): void
    {
        $urlMetadata = new Src\Http\Message\UrlMetadata(1, '0', 1);
        $response = $this->subject->enrichResponse(new Core\Http\Response(), $urlMetadata);
        $headers = [
            'X-Warming-Url-Metadata: ' . $response->getHeader('X-Warming-Url-Metadata')[0],
        ];

        self::assertEquals($urlMetadata, $this->subject->createFromResponseHeaders($headers));
    }

    #[Framework\Attributes\Test]
    public function enrichRequestAddsRequestHeader(): void
    {
        $requestUrl = 'https://typo3-testing.local';
        $request = new Core\Http\ServerRequest($requestUrl);
        $headerValue = $this->buildExpectedHeaderValue($requestUrl);

        $expected = $request->withHeader(
            'X-Warming-Request-Claim',
            \base64_encode($headerValue),
        );

        self::assertEquals($expected, $this->subject->enrichRequest($request));
        self::assertNotNull($this->subject->createForRequest($expected));
    }

    #[Framework\Attributes\Test]
    public function enrichResponseAddsResponseHeader(): void
    {
        $urlMetadata = new Src\Http\Message\UrlMetadata(1, '0', 1);
        $response = new Core\Http\Response();
        $headerValue = $this->buildExpectedHeaderValue(\json_encode($urlMetadata, JSON_THROW_ON_ERROR));

        $expected = $response->withHeader(
            'X-Warming-Url-Metadata',
            \base64_encode($headerValue),
        );

        self::assertEquals($expected, $this->subject->enrichResponse($response, $urlMetadata));
        self::assertEquals($urlMetadata, $this->subject->createFromResponse($expected));
    }

    #[Framework\Attributes\Test]
    public function enrichExceptionInjectsEnrichedResponseIntoImmediateResponseException(): void
    {
        $urlMetadata = new Src\Http\Message\UrlMetadata(1, '0', 1);
        $response = new Core\Http\Response();
        $exception = new Core\Http\ImmediateResponseException($response);
        $headerValue = $this->buildExpectedHeaderValue(\json_encode($urlMetadata, JSON_THROW_ON_ERROR));

        $expected = $response->withHeader(
            'X-Warming-Url-Metadata',
            \base64_encode($headerValue),
        );

        $this->subject->enrichException($exception, $urlMetadata);

        self::assertEquals($expected, $exception->getResponse());
        self::assertEquals($urlMetadata, $this->subject->createFromResponse($exception->getResponse()));
    }

    #[Framework\Attributes\Test]
    public function enrichExceptionInjectsEnrichedResponseHeadersIntoStatusException(): void
    {
        $urlMetadata = new Src\Http\Message\UrlMetadata(1, '0', 1);
        $exception = new Core\Error\Http\StatusException([], ' Something went wrong');
        $headerValue = $this->buildExpectedHeaderValue(\json_encode($urlMetadata, JSON_THROW_ON_ERROR));

        $expected = [
            'X-Warming-Url-Metadata: ' . \base64_encode($headerValue),
        ];

        $this->subject->enrichException($exception, $urlMetadata);

        self::assertEquals($expected, $exception->getStatusHeaders());
        self::assertEquals($urlMetadata, $this->subject->createFromResponseHeaders($exception->getStatusHeaders()));
    }

    private function buildExpectedHeaderValue(string $value): string
    {
        return Core\Utility\GeneralUtility::makeInstance(Core\Crypto\HashService::class)
            ->appendHmac($value, Src\Http\Message\UrlMetadataFactory::class)
        ;
    }
}
