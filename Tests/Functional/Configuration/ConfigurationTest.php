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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Configuration;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * ConfigurationTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\BackupGlobals(true)]
#[Framework\Attributes\CoversClass(Src\Configuration\Configuration::class)]
final class ConfigurationTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'typed_extconf',
        'warming',
    ];

    protected array $configurationToUseInTestInstance = [
        'SYS' => [
            'encryptionKey' => '0b84531802b4bff53a8cc152b8c5b9965fb33deb897a60130349109fbcb6f7d39e5d125d6d27a89b6e16b66a811fca42',
        ],
    ];

    protected bool $initializeDatabase = false;

    private Src\Configuration\Configuration $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Configuration\Configuration(
            Tests\Functional\Fixtures\Classes\DummyCrawler::class,
            [
                'foo' => 'baz',
            ],
            Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::class,
            [
                'another' => 'foo',
            ],
            [
                'parser' => 'options',
            ],
            [
                'client' => 'options',
            ],
            100,
            [
                'foo',
            ],
            new CacheWarmup\Crawler\Strategy\SortByPriorityStrategy(),
            false,
            [
                Core\Domain\Repository\PageRepository::DOKTYPE_LINK,
            ],
            false,
        );
    }

    #[Framework\Attributes\Test]
    public function getCrawlerReturnsConfiguredCrawler(): void
    {
        $actual = $this->subject->getCrawler();

        self::assertInstanceOf(Tests\Functional\Fixtures\Classes\DummyCrawler::class, $actual);
        self::assertSame(['foo' => 'baz'], $actual::$options);
    }

    #[Framework\Attributes\Test]
    public function getVerboseCrawlerReturnsConfiguredVerboseCrawler(): void
    {
        $actual = $this->subject->getVerboseCrawler();

        self::assertInstanceOf(Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::class, $actual);
        self::assertSame(['another' => 'foo'], $actual::$options);
    }

    /**
     * @todo Remove with v5.0
     */
    #[Framework\Attributes\Test]
    public function getUserAgentTriggersDeprecationNotice(): void
    {
        $deprecationMessage = null;

        set_error_handler(
            static function (int $severity, string $message) use (&$deprecationMessage) {
                $deprecationMessage = $message;

                return true;
            },
            E_USER_DEPRECATED,
        );

        try {
            /* @phpstan-ignore method.deprecated */
            $this->subject->getUserAgent();
        } finally {
            restore_error_handler();
        }

        self::assertSame(
            \sprintf(
                'Method "%s::getUserAgent()" is deprecated and will be removed in v5.0. Access User-Agent header via "%s::getUserAgent()" method instead.',
                Src\Configuration\Configuration::class,
                Src\Http\Message\Request\RequestOptions::class,
            ),
            $deprecationMessage,
        );
    }

    /**
     * @todo Remove with v5.0
     */
    #[Framework\Attributes\Test]
    public function getUserAgentReturnsCorrectlyGeneratedUserAgent(): void
    {
        // Silence deprecations
        set_error_handler(
            static fn(int $severity, string $message) => true,
            E_USER_DEPRECATED,
        );

        if ((new Core\Information\Typo3Version())->getMajorVersion() >= 13) {
            $expected = 'TYPO3/tx_warming_crawleref503f61d0e736e783384fd63c5ea03da19f23a4';
        } else {
            $expected = 'TYPO3/tx_warming_crawler2cdfe0c134f3796954daf9395c034c39b542ca57';
        }

        try {
            /* @phpstan-ignore method.deprecated */
            self::assertSame($expected, $this->subject->getUserAgent());
        } finally {
            restore_error_handler();
        }
    }

    /**
     * @todo Remove with v5.0
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('deprecatedMethodCallTriggersDeprecationNoticeDataProvider')]
    public function deprecatedMethodCallTriggersDeprecationNotice(string $methodName, string $expected): void
    {
        $deprecationMessage = null;

        set_error_handler(
            static function (int $severity, string $message) use (&$deprecationMessage) {
                $deprecationMessage = $message;

                return true;
            },
            E_USER_DEPRECATED,
        );

        try {
            /* @phpstan-ignore argument.type */
            call_user_func([$this->subject, $methodName]);
        } finally {
            restore_error_handler();
        }

        self::assertSame($expected, $deprecationMessage);
    }

    /**
     * @todo Remove with v5.0
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('deprecatedMethodCallReturnsPropertyValueDataProvider')]
    public function deprecatedMethodCallReturnsPropertyValue(string $methodName, mixed $expected): void
    {
        // Silence deprecations
        set_error_handler(
            static fn(int $severity, string $message) => true,
            E_USER_DEPRECATED,
        );

        try {
            /* @phpstan-ignore argument.type */
            $actual = call_user_func([$this->subject, $methodName]);
        } finally {
            restore_error_handler();
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * @todo Remove with v5.0
     */
    #[Framework\Attributes\Test]
    public function unsupportedMethodCallThrowsException(): void
    {
        $this->expectExceptionObject(
            new \BadMethodCallException('Unknown method "foo".', 1753475960),
        );

        /* @phpstan-ignore method.notFound */
        $this->subject->foo();
    }

    /**
     * @return \Generator<string, array{string, string}>
     */
    public static function deprecatedMethodCallTriggersDeprecationNoticeDataProvider(): \Generator
    {
        $message = static fn(string $method, string $property) => \sprintf(
            'Method "%s::%s()" is deprecated and will be removed in v5.0. Access class property "$%s" directly.',
            Src\Configuration\Configuration::class,
            $method,
            $property,
        );

        yield 'crawler options' => [
            'getCrawlerOptions',
            $message('getCrawlerOptions', 'crawlerOptions'),
        ];
        yield 'verbose crawler options' => [
            'getVerboseCrawlerOptions',
            $message('getVerboseCrawlerOptions', 'verboseCrawlerOptions'),
        ];
        yield 'parser options' => [
            'getParserOptions',
            $message('getParserOptions', 'parserOptions'),
        ];
        yield 'client options' => [
            'getClientOptions',
            $message('getClientOptions', 'clientOptions'),
        ];
        yield 'limit' => [
            'getLimit',
            $message('getLimit', 'limit'),
        ];
        yield 'exclude patterns' => [
            'getExcludePatterns',
            $message('getExcludePatterns', 'excludePatterns'),
        ];
        yield 'strategy' => [
            'getStrategy',
            $message('getStrategy', 'crawlingStrategy'),
        ];
        yield 'enabled in page tree' => [
            'isEnabledInPageTree',
            $message('isEnabledInPageTree', 'enabledInPageTree'),
        ];
        yield 'supported doktypes' => [
            'getSupportedDoktypes',
            $message('getSupportedDoktypes', 'supportedDoktypes'),
        ];
        yield 'enabled in toolbar' => [
            'isEnabledInToolbar',
            $message('isEnabledInToolbar', 'enabledInToolbar'),
        ];
    }

    /**
     * @return \Generator<string, array{string, mixed}>
     */
    public static function deprecatedMethodCallReturnsPropertyValueDataProvider(): \Generator
    {
        yield 'crawler options' => [
            'getCrawlerOptions',
            [
                'foo' => 'baz',
            ],
        ];
        yield 'verbose crawler options' => [
            'getVerboseCrawlerOptions',
            [
                'another' => 'foo',
            ],
        ];
        yield 'parser options' => [
            'getParserOptions',
            [
                'parser' => 'options',
            ],
        ];
        yield 'client options' => [
            'getClientOptions',
            [
                'client' => 'options',
            ],
        ];
        yield 'limit' => [
            'getLimit',
            100,
        ];
        yield 'exclude patterns' => [
            'getExcludePatterns',
            [
                'foo',
            ],
        ];
        yield 'strategy' => [
            'getStrategy',
            new CacheWarmup\Crawler\Strategy\SortByPriorityStrategy(),
        ];
        yield 'enabled in page tree' => [
            'isEnabledInPageTree',
            false,
        ];
        yield 'supported doktypes' => [
            'getSupportedDoktypes',
            [
                Core\Domain\Repository\PageRepository::DOKTYPE_LINK,
            ],
        ];
        yield 'enabled in toolbar' => [
            'isEnabledInToolbar',
            false,
        ];
    }

    protected function tearDown(): void
    {
        Tests\Functional\Fixtures\Classes\DummyCrawler::$options = [];
        Tests\Functional\Fixtures\Classes\DummyVerboseCrawler::$options = [];

        parent::tearDown();
    }
}
