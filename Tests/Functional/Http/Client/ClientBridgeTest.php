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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Http\Client;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * ClientBridgeTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Client\ClientBridge::class)]
final class ClientBridgeTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'warming',
    ];

    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'warming' => [
                'clientOptions' => '{"auth":["username","password"]}',
            ],
        ],
        'HTTP' => [
            RequestOptions::VERIFY => false,
        ],
    ];

    protected bool $initializeDatabase = false;

    private Tests\Functional\Fixtures\Classes\DummyEventDispatcher $eventDispatcher;
    private Src\Http\Client\ClientBridge $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->eventDispatcher = new Tests\Functional\Fixtures\Classes\DummyEventDispatcher();
        $this->subject = new Src\Http\Client\ClientBridge(
            $this->get(Src\Configuration\Configuration::class),
            $this->get(Core\Http\Client\GuzzleClientFactory::class),
            $this->eventDispatcher,
        );
    }

    #[Framework\Attributes\Test]
    public function getClientFactoryReturnsClientFactoryWithTYPO3AndExtensionSpecificClientOptions(): void
    {
        $actual = $this->subject->getClientFactory();

        $client = $actual->get();
        $config = $this->getClientConfigViaReflection($client);

        self::assertSame(['username', 'password'], $config[RequestOptions::AUTH] ?? null);
        self::assertFalse($config[RequestOptions::VERIFY] ?? null);
    }

    #[Framework\Attributes\Test]
    public function getClientFactoryReturnsClientFactoryWithGlobalEventDispatcherAttached(): void
    {
        $actual = $this->subject->getClientFactory();

        // Triggers event dispatcher
        $actual->get();

        $dispatchedEvents = $this->eventDispatcher->dispatchedEvents;

        self::assertCount(1, $dispatchedEvents);
        self::assertInstanceOf(CacheWarmup\Event\Http\ClientConstructed::class, $dispatchedEvents[0]);
    }

    #[Framework\Attributes\Test]
    public function getClientConfigReturnsTYPO3AndExtensionSpecificClientOptions(): void
    {
        $actual = $this->subject->getClientConfig();

        self::assertSame(['username', 'password'], $actual[RequestOptions::AUTH] ?? null);
        self::assertFalse($actual[RequestOptions::VERIFY] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    private function getClientConfigViaReflection(ClientInterface $client): array
    {
        self::assertInstanceOf(Client::class, $client);

        $reflection = new \ReflectionObject($client);
        $config = $reflection->getProperty('config')->getValue($client);

        self::assertIsArray($config);

        return $config;
    }
}
