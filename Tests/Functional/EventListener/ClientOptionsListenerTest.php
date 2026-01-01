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
use EliasHaeussler\DeepClosureComparator;
use EliasHaeussler\Typo3SitemapLocator;
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
        'EXTENSIONS' => [
            'warming' => [
                'clientOptions' => '{"auth":["username","password"]}',
            ],
        ],
        'HTTP' => [
            'handler' => [
                // Must be a callable
                'foo' => 'trim',
            ],
            RequestOptions::VERIFY => false,
        ],
        'SYS' => [
            'encryptionKey' => '0b84531802b4bff53a8cc152b8c5b9965fb33deb897a60130349109fbcb6f7d39e5d125d6d27a89b6e16b66a811fca42',
        ],
    ];

    protected bool $initializeDatabase = false;

    private Src\EventListener\ClientOptionsListener $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(Src\EventListener\ClientOptionsListener::class);
    }

    #[Framework\Attributes\Test]
    public function processConfigMergesClientConfigWithTYPO3SpecificClientConfig(): void
    {
        $event = new CacheWarmup\Event\Config\ConfigResolved(
            new CacheWarmup\Config\CacheWarmupConfig(clientOptions: [
                RequestOptions::AUTH => ['username', 'password'],
            ]),
        );

        $this->subject->processConfig($event);

        $clientOptions = $event->config()->getClientOptions();

        $handlerStack = HandlerStack::create();
        /* @phpstan-ignore argument.type */
        $handlerStack->push('trim', 'foo');

        DeepClosureComparator\DeepClosureAssert::assertEquals($handlerStack, $clientOptions['handler'] ?? null);
        self::assertSame(['username', 'password'], $clientOptions[RequestOptions::AUTH] ?? null);
        self::assertFalse($clientOptions[RequestOptions::VERIFY] ?? null);
    }

    #[Framework\Attributes\Test]
    public function processClientMergesGlobalConfigWithExtensionConfig(): void
    {
        $event = new Typo3SitemapLocator\Event\BeforeClientConfiguredEvent([
            RequestOptions::VERIFY => false,
        ]);

        $expected = [
            RequestOptions::VERIFY => false,
            RequestOptions::AUTH => ['username', 'password'],
            RequestOptions::HEADERS => [
                'User-Agent' => 'TYPO3/tx_warming_crawlercbca109427154aa0b126274755477f4337ecd56d',
            ],
        ];

        $this->subject->processClient($event);

        self::assertSame($expected, $event->getOptions());
    }
}
