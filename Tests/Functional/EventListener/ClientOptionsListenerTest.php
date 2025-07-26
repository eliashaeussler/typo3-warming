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
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * ClientOptionsListenerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\EventListener\ClientOptionsListener::class)]
final class ClientOptionsListenerTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'typed_extconf',
        'warming',
    ];

    protected array $configurationToUseInTestInstance = [
        'HTTP' => [
            'handler' => [
                // Must be a callable
                'foo' => 'trim',
            ],
            RequestOptions::VERIFY => false,
        ],
    ];

    protected bool $initializeDatabase = false;

    private Src\EventListener\ClientOptionsListener $subject;
    private CacheWarmup\Event\Config\ConfigResolved $event;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(Src\EventListener\ClientOptionsListener::class);
        $this->event = new CacheWarmup\Event\Config\ConfigResolved(
            new CacheWarmup\Config\CacheWarmupConfig(clientOptions: [
                RequestOptions::AUTH => ['username', 'password'],
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function invokeMergesClientConfigWithTYPO3SpecificClientConfig(): void
    {
        ($this->subject)($this->event);

        $clientOptions = $this->event->config()->getClientOptions();

        $handlerStack = HandlerStack::create();
        /* @phpstan-ignore argument.type */
        $handlerStack->push('trim', 'foo');

        self::assertEquals($handlerStack, $clientOptions['handler'] ?? null);
        self::assertSame(['username', 'password'], $clientOptions[RequestOptions::AUTH] ?? null);
        self::assertFalse($clientOptions[RequestOptions::VERIFY] ?? null);
    }
}
