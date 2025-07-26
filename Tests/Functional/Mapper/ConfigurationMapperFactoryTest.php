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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Mapper;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming as Src;
use mteu\TypedExtConf;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * ConfigurationMapperFactoryTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Mapper\ConfigurationMapperFactory::class)]
final class ConfigurationMapperFactoryTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'typed_extconf',
        'warming',
    ];

    protected bool $initializeDatabase = false;

    private Src\Mapper\ConfigurationMapperFactory $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(Src\Mapper\ConfigurationMapperFactory::class);
    }

    /**
     * @param array<string, mixed> $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('createReturnsMapperWithRegisteredConverterForOptionsDataProvider')]
    public function createReturnsMapperWithRegisteredConverterForOptions(string $options, array $expected): void
    {
        $actual = $this->subject->create();

        self::assertSame(
            $expected,
            $actual->map('array<string, mixed>', $options),
        );
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('createReturnsMapperWithRegisteredConverterForCrawlingStrategyDataProvider')]
    public function createReturnsMapperWithRegisteredConverterForCrawlingStrategy(
        string $strategy,
        ?CacheWarmup\Crawler\Strategy\CrawlingStrategy $expected,
    ): void {
        $actual = $this->subject->create();

        self::assertEquals(
            $expected,
            $actual->map(CacheWarmup\Crawler\Strategy\CrawlingStrategy::class . '|null', $strategy),
        );
    }

    #[Framework\Attributes\Test]
    public function createReturnsMapperWithRegisteredConverterForIntegerList(): void
    {
        $actual = $this->subject->create();

        self::assertEquals(
            [0, 1, 2],
            $actual->map('list<int>', '0, 1,,, 2'),
        );
    }

    #[Framework\Attributes\Test]
    public function createReturnsMapperWithRegisteredConverterForStringList(): void
    {
        $actual = $this->subject->create();

        self::assertEquals(
            ['foo', 'baz', 'bar'],
            $actual->map('list<non-empty-string>', 'foo, baz,,, bar'),
        );
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DoesNotPerformAssertions]
    public function createReturnsMapperWithCapabilitiesToMapExtensionConfiguration(): void
    {
        $provider = new TypedExtConf\Provider\TypedExtensionConfigurationProvider(
            $this->get(Core\Configuration\ExtensionConfiguration::class),
            $this->subject,
        );

        // If no exception is thrown, everything is fine.
        // No need to perform any assertions here.

        $provider->get(Src\Configuration\Configuration::class);
    }

    /**
     * @return \Generator<string, array{string, array<string, mixed>}>
     */
    public static function createReturnsMapperWithRegisteredConverterForOptionsDataProvider(): \Generator
    {
        yield 'empty string' => [
            '',
            [],
        ];
        yield 'JSON-encoded array' => [
            '{"foo":"baz"}',
            ['foo' => 'baz'],
        ];
    }

    /**
     * @return \Generator<string, array{string, CacheWarmup\Crawler\Strategy\CrawlingStrategy|null}>
     */
    public static function createReturnsMapperWithRegisteredConverterForCrawlingStrategyDataProvider(): \Generator
    {
        yield 'invalid strategy' => [
            'foo',
            null,
        ];
        yield 'valid strategy' => [
            CacheWarmup\Crawler\Strategy\SortByPriorityStrategy::getName(),
            new CacheWarmup\Crawler\Strategy\SortByPriorityStrategy(),
        ];
    }
}
