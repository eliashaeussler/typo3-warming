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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Http\Client\Handler;

use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use GuzzleHttp\Exception;
use GuzzleHttp\Promise;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * SubRequestHandlerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Client\Handler\SubRequestHandler::class)]
final class SubRequestHandlerTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\Functional\SiteTrait;

    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'warming',
    ];

    private Frontend\Http\Application&Framework\MockObject\MockObject $applicationMock;
    private Src\Http\Message\UrlMetadataFactory $urlMetadataFactory;
    private Src\Http\Client\Handler\SubRequestHandler $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__, 3) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__, 3) . '/Fixtures/Database/pages.csv');

        $this->createSite();
        $this->setUpBackendUser(3);

        $this->applicationMock = $this->createMock(Frontend\Http\Application::class);
        $this->urlMetadataFactory = new Src\Http\Message\UrlMetadataFactory();
        $this->subject = new Src\Http\Client\Handler\SubRequestHandler(
            $this->applicationMock,
            $this->urlMetadataFactory,
            $this->get(Core\Routing\SiteMatcher::class),
        );
    }

    #[Framework\Attributes\Test]
    public function invokeUsesFallbackHandlerIfNoMatchingSitesAreAvailable(): void
    {
        $request = new Core\Http\Request('https://typo3-fake.local/', body: 'php://temp');

        $this->applicationMock->expects(self::never())->method('handle');

        ($this->subject)($request, [])->cancel();
    }

    #[Framework\Attributes\Test]
    public function invokeReturnsFulfilledPromiseWithResponseFromApplication(): void
    {
        $request = new Core\Http\Request('https://typo3-testing.local/', body: 'php://temp');
        $response = new Core\Http\Response();

        $this->applicationMock->method('handle')->willReturn($response);

        $actual = ($this->subject)($request, [])->wait();

        self::assertSame($response, $actual);
    }

    #[Framework\Attributes\Test]
    public function invokeReturnsRejectedPromiseWithExceptionFromApplication(): void
    {
        $request = new Core\Http\Request('https://typo3-testing.local/', body: 'php://temp');
        $exception = new \Exception('something went wrong');

        $this->applicationMock->method('handle')->willThrowException($exception);

        $expected = Promise\Create::rejectionFor(
            new Exception\RequestException('something went wrong', $request),
        );

        self::assertEquals($expected, ($this->subject)($request, []));
    }

    #[Framework\Attributes\Test]
    public function invokeReturnsRejectedPromiseWithEnrichedImmediateResponseExceptionFromApplication(): void
    {
        $request = new Core\Http\Request('https://typo3-testing.local/', body: 'php://temp');
        $response = new Core\Http\Response();
        $exception = new Core\Http\ImmediateResponseException($response);

        $this->applicationMock->method('handle')->willThrowException($exception);

        $expected = Promise\Create::rejectionFor(
            new Exception\RequestException('', $request, $response),
        );

        self::assertEquals($expected, ($this->subject)($request, []));
    }

    #[Framework\Attributes\Test]
    public function invokeReturnsRejectedPromiseWithEnrichedStatusExceptionFromApplication(): void
    {
        $request = new Core\Http\Request('https://typo3-testing.local/', body: 'php://temp');
        $urlMetadata = new Src\Http\Message\UrlMetadata(1, '0', 1);
        $exception = new Core\Error\Http\StatusException([], 'Something went wrong');
        $actual = null;

        $this->urlMetadataFactory->enrichException($exception, $urlMetadata);

        $this->applicationMock->method('handle')->willThrowException($exception);

        try {
            ($this->subject)($request, [])->wait();
        } catch (Exception\RequestException $actual) {
        }

        self::assertInstanceOf(Exception\RequestException::class, $actual);
        self::assertNotNull($actual->getResponse());

        $response = $this->urlMetadataFactory->enrichResponse(
            new Core\Http\Response($actual->getResponse()->getBody(), 500),
            $urlMetadata,
        );
        $expected = new Exception\RequestException('Something went wrong', $request, $response);

        self::assertEquals($expected, $actual);
    }
}
