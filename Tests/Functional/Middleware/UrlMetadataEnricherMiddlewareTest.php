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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Middleware;

use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use Psr\Http\Server;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * UrlMetadataEnricherMiddlewareTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Middleware\UrlMetadataEnricherMiddleware::class)]
final class UrlMetadataEnricherMiddlewareTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\Functional\SiteTrait;

    protected bool $initializeDatabase = false;

    private Src\Http\Message\UrlMetadataFactory $urlMetadataFactory;
    private Src\Middleware\UrlMetadataEnricherMiddleware $subject;
    private Core\Http\ServerRequest $request;
    private Server\RequestHandlerInterface&Framework\MockObject\Stub $handlerStub;

    public function setUp(): void
    {
        parent::setUp();

        $this->urlMetadataFactory = new Src\Http\Message\UrlMetadataFactory();
        $this->subject = new Src\Middleware\UrlMetadataEnricherMiddleware($this->urlMetadataFactory);
        $this->request = $this->urlMetadataFactory->enrichRequest(new Core\Http\ServerRequest('https://typo3-testing.local'))
            ->withAttribute('routing', new Core\Routing\PageArguments(1, '0', []))
            ->withAttribute('language', $this->createSite()->getLanguageById(1))
        ;
        $this->handlerStub = self::createStub(Server\RequestHandlerInterface::class);
    }

    #[Framework\Attributes\Test]
    public function processDoesNothingOnUnauthorizedRequest(): void
    {
        $request = new Core\Http\ServerRequest('https://typo3-testing.local');
        $response = new Core\Http\Response();

        $this->handlerStub->method('handle')->willReturn($response);

        self::assertSame($response, $this->subject->process($request, $this->handlerStub));
    }

    #[Framework\Attributes\Test]
    public function processEnrichesImmediateResponseException(): void
    {
        $exception = null;
        $response = new Core\Http\Response();

        $this->handlerStub->method('handle')->willThrowException(
            new Core\Http\ImmediateResponseException($response),
        );

        try {
            $this->subject->process($this->request, $this->handlerStub);
        } catch (Core\Http\ImmediateResponseException $exception) {
        }

        $expected = new Src\Http\Message\UrlMetadata(1, '0', 1);

        self::assertInstanceOf(Core\Http\ImmediateResponseException::class, $exception);
        self::assertEquals($expected, $this->urlMetadataFactory->createFromResponse($exception->getResponse()));
    }

    #[Framework\Attributes\Test]
    public function processEnrichesStatusException(): void
    {
        $exception = null;

        $this->handlerStub->method('handle')->willThrowException(
            new Core\Error\Http\StatusException([], 'Something went wrong'),
        );

        try {
            $this->subject->process($this->request, $this->handlerStub);
        } catch (Core\Error\Http\StatusException $exception) {
        }

        $expected = new Src\Http\Message\UrlMetadata(1, '0', 1);

        self::assertInstanceOf(Core\Error\Http\StatusException::class, $exception);
        self::assertEquals($expected, $this->urlMetadataFactory->createFromResponseHeaders($exception->getStatusHeaders()));
    }

    #[Framework\Attributes\Test]
    public function processEnrichesResponse(): void
    {
        $this->handlerStub->method('handle')->willReturn(new Core\Http\Response());

        $actual = $this->subject->process($this->request, $this->handlerStub);

        $expected = new Src\Http\Message\UrlMetadata(1, '0', 1);

        self::assertEquals($expected, $this->urlMetadataFactory->createFromResponse($actual));
    }
}
