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
        'warming',
    ];

    protected array $configurationToUseInTestInstance = [
        'SYS' => [
            'encryptionKey' => '0b84531802b4bff53a8cc152b8c5b9965fb33deb897a60130349109fbcb6f7d39e5d125d6d27a89b6e16b66a811fca42',
        ],
    ];

    protected bool $initializeDatabase = false;

    private Core\Configuration\ExtensionConfiguration $extensionConfiguration;
    private Src\Configuration\Configuration $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extensionConfiguration = $this->get(Core\Configuration\ExtensionConfiguration::class);
        $this->subject = $this->get(Src\Configuration\Configuration::class);
    }

    #[Framework\Attributes\Test]
    public function getCrawlerReturnsDefaultCrawlerIfNoCrawlerIsConfigured(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY);

        self::assertInstanceOf(Src\Crawler\ConcurrentUserAgentCrawler::class, $this->subject->getCrawler());
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('getCrawlerReturnsDefaultCrawlerIfConfiguredCrawlerIsInvalidDataProvider')]
    public function getCrawlerReturnsDefaultCrawlerIfConfiguredCrawlerIsInvalid(string|bool $crawler): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['crawler' => $crawler]);

        self::assertInstanceOf(Src\Crawler\ConcurrentUserAgentCrawler::class, $this->subject->getCrawler());
    }

    #[Framework\Attributes\Test]
    public function getCrawlerReturnsConfiguredCrawler(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['crawler' => CacheWarmup\Crawler\ConcurrentCrawler::class]);

        self::assertInstanceOf(CacheWarmup\Crawler\ConcurrentCrawler::class, $this->subject->getCrawler());
    }

    #[Framework\Attributes\Test]
    public function getCrawlerOptionsReturnsEmptyArrayIfNoCrawlerOptionsAreConfigured(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY);

        self::assertSame([], $this->subject->getCrawlerOptions());
    }

    #[Framework\Attributes\Test]
    public function getCrawlerOptionsReturnsEmptyArrayIfConfiguredCrawlerOptionsAreOfInvalidType(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['crawlerOptions' => ['foo' => 'baz']]);

        self::assertSame([], $this->subject->getCrawlerOptions());
    }

    #[Framework\Attributes\Test]
    public function getCrawlerOptionsThrowsExceptionIfConfiguredCrawlerOptionsAreOfInvalidJson(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['crawlerOptions' => '"foo"']);

        $this->expectExceptionObject(
            new CacheWarmup\Exception\OptionsAreMalformed('"foo"'),
        );

        $this->subject->getCrawlerOptions();
    }

    #[Framework\Attributes\Test]
    public function getCrawlerOptionsReturnsConfiguredCrawlerOptions(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['crawlerOptions' => '{"foo":"baz"}']);

        self::assertSame(['foo' => 'baz'], $this->subject->getCrawlerOptions());
    }

    #[Framework\Attributes\Test]
    public function getVerboseCrawlerReturnsDefaultVerboseCrawlerIfNoVerboseCrawlerIsConfigured(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY);

        self::assertInstanceOf(Src\Crawler\OutputtingUserAgentCrawler::class, $this->subject->getVerboseCrawler());
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('getVerboseCrawlerReturnsDefaultVerboseCrawlerIfConfiguredVerboseCrawlerIsInvalidDataProvider')]
    public function getVerboseCrawlerReturnsDefaultVerboseCrawlerIfConfiguredVerboseCrawlerIsInvalid(string|bool $verboseCrawler): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['verboseCrawler' => $verboseCrawler]);

        self::assertInstanceOf(Src\Crawler\OutputtingUserAgentCrawler::class, $this->subject->getVerboseCrawler());
    }

    #[Framework\Attributes\Test]
    public function getVerboseCrawlerReturnsConfiguredVerboseCrawler(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['verboseCrawler' => CacheWarmup\Crawler\OutputtingCrawler::class]);

        self::assertInstanceOf(CacheWarmup\Crawler\OutputtingCrawler::class, $this->subject->getVerboseCrawler());
    }

    #[Framework\Attributes\Test]
    public function getVerboseCrawlerOptionsReturnsEmptyArrayIfNoVerboseCrawlerOptionsAreConfigured(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY);

        self::assertSame([], $this->subject->getVerboseCrawlerOptions());
    }

    #[Framework\Attributes\Test]
    public function getVerboseCrawlerOptionsReturnsEmptyArrayIfConfiguredVerboseCrawlerOptionsAreOfInvalidType(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['verboseCrawlerOptions' => ['foo' => 'baz']]);

        self::assertSame([], $this->subject->getVerboseCrawlerOptions());
    }

    #[Framework\Attributes\Test]
    public function getVerboseCrawlerOptionsThrowsExceptionIfConfiguredVerboseCrawlerOptionsAreOfInvalidJson(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['verboseCrawlerOptions' => '"foo"']);

        $this->expectExceptionObject(
            new CacheWarmup\Exception\OptionsAreMalformed('"foo"'),
        );

        $this->subject->getVerboseCrawlerOptions();
    }

    #[Framework\Attributes\Test]
    public function getVerboseCrawlerOptionsReturnsConfiguredVerboseCrawlerOptions(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['verboseCrawlerOptions' => '{"foo":"baz"}']);

        self::assertSame(['foo' => 'baz'], $this->subject->getVerboseCrawlerOptions());
    }

    #[Framework\Attributes\Test]
    public function getParserOptionsReturnsEmptyArrayIfNoParserOptionsAreConfigured(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY);

        self::assertSame([], $this->subject->getParserOptions());
    }

    #[Framework\Attributes\Test]
    public function getParserOptionsReturnsEmptyArrayIfConfiguredParserOptionsAreOfInvalidType(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['parserOptions' => ['foo' => 'baz']]);

        self::assertSame([], $this->subject->getParserOptions());
    }

    #[Framework\Attributes\Test]
    public function getParserOptionsThrowsExceptionIfConfiguredParserOptionsAreOfInvalidJson(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['parserOptions' => '"foo"']);

        $this->expectExceptionObject(
            new CacheWarmup\Exception\OptionsAreMalformed('"foo"'),
        );

        $this->subject->getParserOptions();
    }

    #[Framework\Attributes\Test]
    public function getParserOptionsReturnsConfiguredParserOptions(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['parserOptions' => '{"foo":"baz"}']);

        self::assertSame(['foo' => 'baz'], $this->subject->getParserOptions());
    }

    #[Framework\Attributes\Test]
    public function getClientOptionsReturnsEmptyArrayIfNoClientOptionsAreConfigured(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY);

        self::assertSame([], $this->subject->getClientOptions());
    }

    #[Framework\Attributes\Test]
    public function getClientOptionsReturnsEmptyArrayIfConfiguredClientOptionsAreOfInvalidType(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['clientOptions' => ['foo' => 'baz']]);

        self::assertSame([], $this->subject->getClientOptions());
    }

    #[Framework\Attributes\Test]
    public function getClientOptionsThrowsExceptionIfConfiguredClientOptionsAreOfInvalidJson(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['clientOptions' => '"foo"']);

        $this->expectExceptionObject(
            new CacheWarmup\Exception\OptionsAreMalformed('"foo"'),
        );

        $this->subject->getClientOptions();
    }

    #[Framework\Attributes\Test]
    public function getClientOptionsReturnsConfiguredClientOptions(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['clientOptions' => '{"foo":"baz"}']);

        self::assertSame(['foo' => 'baz'], $this->subject->getClientOptions());
    }

    #[Framework\Attributes\Test]
    public function getLimitReturnsDefaultLimitIfNoLimitIsConfigured(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY);

        self::assertSame(250, $this->subject->getLimit());
    }

    #[Framework\Attributes\Test]
    public function getLimitReturnsDefaultLimitIfConfiguredLimitIsNotNumeric(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['limit' => 'foo']);

        self::assertSame(250, $this->subject->getLimit());
    }

    #[Framework\Attributes\Test]
    public function getLimitReturnsNonNegativeIntegerValue(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['limit' => -1]);

        self::assertSame(0, $this->subject->getLimit());
    }

    #[Framework\Attributes\Test]
    public function getLimitReturnsConfiguredLimit(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['limit' => 350]);

        self::assertSame(350, $this->subject->getLimit());
    }

    #[Framework\Attributes\Test]
    public function getExcludePatternsReturnsEmptyArrayIfNoExcludePatternsAreConfigured(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY);

        self::assertSame([], $this->subject->getExcludePatterns());
    }

    #[Framework\Attributes\Test]
    public function getExcludePatternsReturnsEmptyArrayIfConfiguredExcludePatternsAreOfInvalidType(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['exclude' => false]);

        self::assertSame([], $this->subject->getExcludePatterns());
    }

    #[Framework\Attributes\Test]
    public function getExcludePatternsReturnsEmptyArrayIfConfiguredExcludePatternsAreEmpty(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['exclude' => '']);

        self::assertSame([], $this->subject->getExcludePatterns());
    }

    #[Framework\Attributes\Test]
    public function getExcludePatternsReturnsConfiguredExcludePatternsAsArray(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['exclude' => 'foo,,baz']);

        self::assertSame(['foo', 'baz'], $this->subject->getExcludePatterns());
    }

    #[Framework\Attributes\Test]
    public function getStrategyReturnsNullIfNoStrategyIsConfigured(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY);

        self::assertNull($this->subject->getStrategy());
    }

    #[Framework\Attributes\Test]
    public function getStrategyReturnsNullIfConfiguredStrategyIsOfInvalidType(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['strategy' => false]);

        self::assertNull($this->subject->getStrategy());
    }

    #[Framework\Attributes\Test]
    public function getStrategyReturnsNullIfConfiguredStrategyIsEmpty(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['strategy' => '']);

        self::assertNull($this->subject->getStrategy());
    }

    #[Framework\Attributes\Test]
    public function getStrategyReturnsNullIfConfiguredStrategyIsNotSupported(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['strategy' => 'foo']);

        self::assertNull($this->subject->getStrategy());
    }

    #[Framework\Attributes\Test]
    public function getStrategyReturnsConfiguredStrategy(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, [
            'strategy' => CacheWarmup\Crawler\Strategy\SortByPriorityStrategy::getName(),
        ]);

        self::assertInstanceOf(
            CacheWarmup\Crawler\Strategy\SortByPriorityStrategy::class,
            $this->subject->getStrategy(),
        );
    }

    #[Framework\Attributes\Test]
    public function isEnabledInPageTreeReturnsTrueIfNoValueIsConfigured(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY);

        self::assertTrue($this->subject->isEnabledInPageTree());
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('isEnabledInPageTreeReturnsConfiguredValueDataProvider')]
    public function isEnabledInPageTreeReturnsConfiguredValue(bool $enabled): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['enablePageTree' => $enabled]);

        self::assertSame($enabled, $this->subject->isEnabledInPageTree());
    }

    #[Framework\Attributes\Test]
    public function getSupportedDoktypesReturnsDefaultDoktypesIfNoDoktypesAreConfigured(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY);

        $expected = [
            Core\Domain\Repository\PageRepository::DOKTYPE_DEFAULT,
        ];

        self::assertSame($expected, $this->subject->getSupportedDoktypes());
    }

    #[Framework\Attributes\Test]
    public function getSupportedDoktypesReturnsDefaultDoktypesIfConfiguredDoktypesAreInvalid(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['supportedDoktypes' => false]);

        $expected = [
            Core\Domain\Repository\PageRepository::DOKTYPE_DEFAULT,
        ];

        self::assertSame($expected, $this->subject->getSupportedDoktypes());
    }

    #[Framework\Attributes\Test]
    public function getSupportedDoktypesReturnsConfiguredDoktypes(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['supportedDoktypes' => '1,100,200']);

        self::assertSame([1, 100, 200], $this->subject->getSupportedDoktypes());
    }

    #[Framework\Attributes\Test]
    public function isEnabledInToolbarReturnsTrueIfNoValueIsConfigured(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY);

        self::assertTrue($this->subject->isEnabledInToolbar());
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('isEnabledInToolbarReturnsConfiguredValueDataProvider')]
    public function isEnabledInToolbarReturnsConfiguredValue(bool $enabled): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, ['enableToolbar' => $enabled]);

        self::assertSame($enabled, $this->subject->isEnabledInToolbar());
    }

    #[Framework\Attributes\Test]
    public function getUserAgentReturnsCorrectlyGeneratedUserAgent(): void
    {
        if ((new Core\Information\Typo3Version())->getMajorVersion() >= 13) {
            $expected = 'TYPO3/tx_warming_crawleref503f61d0e736e783384fd63c5ea03da19f23a4';
        } else {
            // @todo Remove once support for TYPO3 v12 is dropped
            $expected = 'TYPO3/tx_warming_crawler2cdfe0c134f3796954daf9395c034c39b542ca57';
        }

        self::assertSame($expected, $this->subject->getUserAgent());
    }

    /**
     * @return \Generator<string, array{string|bool}>
     */
    public static function getCrawlerReturnsDefaultCrawlerIfConfiguredCrawlerIsInvalidDataProvider(): \Generator
    {
        yield 'empty string' => [''];
        yield 'invalid type' => [false];
        yield 'invalid class name' => ['foo'];
    }

    /**
     * @return \Generator<string, array{string|bool}>
     */
    public static function getVerboseCrawlerReturnsDefaultVerboseCrawlerIfConfiguredVerboseCrawlerIsInvalidDataProvider(): \Generator
    {
        yield 'empty string' => [''];
        yield 'invalid type' => [false];
        yield 'invalid class name' => ['foo'];
    }

    /**
     * @return \Generator<string, array{bool}>
     */
    public static function isEnabledInPageTreeReturnsConfiguredValueDataProvider(): \Generator
    {
        yield 'enabled' => [true];
        yield 'disabled' => [false];
    }

    /**
     * @return \Generator<string, array{bool}>
     */
    public static function isEnabledInToolbarReturnsConfiguredValueDataProvider(): \Generator
    {
        yield 'enabled' => [true];
        yield 'disabled' => [false];
    }
}
