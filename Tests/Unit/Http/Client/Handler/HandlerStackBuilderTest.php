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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Http\Client\Handler;

use EliasHaeussler\Typo3Warming as Src;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * HandlerStackBuilderTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Client\Handler\HandlerStackBuilder::class)]
final class HandlerStackBuilderTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Http\Client\Handler\HandlerStackBuilder $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Http\Client\Handler\HandlerStackBuilder();
    }

    #[Framework\Attributes\Test]
    public function buildFromClientOrRequestOptionsReturnsDefaultHandlerStackIfNoHandlerIsCurrentlyRegistered(): void
    {
        $clientStub = $this->createMock(ClientInterface::class);
        $handler = static fn() => new Promise\Promise();

        self::assertEquals(
            HandlerStack::create($handler),
            $this->subject->buildFromClientOrRequestOptions($clientStub, [], $handler),
        );
    }

    #[Framework\Attributes\Test]
    public function buildFromClientOrRequestOptionsReturnsDefaultHandlerStackIfHandlerFromRequestOptionsIsNotCallable(): void
    {
        $clientStub = $this->createMock(ClientInterface::class);
        $handler = static fn() => new Promise\Promise();

        self::assertEquals(
            HandlerStack::create($handler),
            $this->subject->buildFromClientOrRequestOptions($clientStub, ['handler' => 'foo'], $handler),
        );
    }

    #[Framework\Attributes\Test]
    public function buildFromClientOrRequestOptionsReturnsDefaultHandlerStackIfClientHandlerIsNotCallable(): void
    {
        $client = new Client();
        $clientReflection = new \ReflectionObject($client);
        $clientReflection->getProperty('config')->setValue($client, [
            'handler' => 'foo',
        ]);
        $handler = static fn() => new Promise\Promise();

        self::assertEquals(
            HandlerStack::create($handler),
            $this->subject->buildFromClientOrRequestOptions($client, [], $handler),
        );
    }

    #[Framework\Attributes\Test]
    public function buildFromClientOrRequestOptionsReturnsDefaultHandlerStackIfClientHandlerIsNotAHandlerStack(): void
    {
        $handler = static fn() => new Promise\Promise();
        $client = new Client(['handler' => $handler]);

        self::assertEquals(
            HandlerStack::create($handler),
            $this->subject->buildFromClientOrRequestOptions($client, []),
        );
    }

    #[Framework\Attributes\Test]
    public function buildFromClientOrRequestOptionsReturnsDefaultHandlerStackIfHandlerFromRequestOptionsIsNotAHandlerStack(): void
    {
        $clientStub = $this->createMock(ClientInterface::class);
        $currentHandler = static fn() => new Promise\Promise(static fn() => '');
        $newHandler = static fn() => new Promise\Promise(static fn() => 'foo');

        self::assertEquals(
            HandlerStack::create($newHandler),
            $this->subject->buildFromClientOrRequestOptions($clientStub, ['handler' => $currentHandler], $newHandler),
        );
    }

    #[Framework\Attributes\Test]
    public function buildFromClientOrRequestOptionsReturnsDefaultHandlerStackWithGivenHandlerIfClientHandlerIsNotAHandlerStack(): void
    {
        $currentHandler = static fn() => new Promise\Promise(static fn() => '');
        $newHandler = static fn() => new Promise\Promise(static fn() => 'foo');
        $client = new Client(['handler' => $currentHandler]);

        self::assertEquals(
            HandlerStack::create($newHandler),
            $this->subject->buildFromClientOrRequestOptions($client, [], $newHandler),
        );
    }

    #[Framework\Attributes\Test]
    public function buildFromClientOrRequestOptionsOverridesHandler(): void
    {
        $currentHandler = static fn() => new Promise\Promise(static fn() => '');
        $newHandler = static fn() => new Promise\Promise(static fn() => 'foo');
        $handlerStack = HandlerStack::create($currentHandler);
        $client = new Client(['handler' => $handlerStack]);

        $actual = $this->subject->buildFromClientOrRequestOptions($client, [], $newHandler);

        self::assertSame($handlerStack, $actual);

        $clientReflection = new \ReflectionObject($actual);

        self::assertSame($newHandler, $clientReflection->getProperty('handler')->getValue($actual));
    }
}
