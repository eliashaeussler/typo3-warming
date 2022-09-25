<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Configuration;

use EliasHaeussler\CacheWarmup\Crawler\ConcurrentCrawler;
use EliasHaeussler\CacheWarmup\Crawler\OutputtingCrawler;
use EliasHaeussler\Typo3Warming\Configuration\Configuration;
use EliasHaeussler\Typo3Warming\Configuration\Extension;
use EliasHaeussler\Typo3Warming\Crawler\ConcurrentUserAgentCrawler;
use EliasHaeussler\Typo3Warming\Crawler\OutputtingUserAgentCrawler;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * ConfigurationTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConfigurationTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/warming',
    ];

    /**
     * @var string
     */
    protected $backedUpEncryptionKey;

    /**
     * @var array<string, mixed>
     */
    protected $backedUpExtensionConfiguration;

    /**
     * @var ExtensionConfiguration
     */
    protected $extensionConfiguration;

    /**
     * @var Configuration
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = '0b84531802b4bff53a8cc152b8c5b9965fb33deb897a60130349109fbcb6f7d39e5d125d6d27a89b6e16b66a811fca42';

        $this->backedUpEncryptionKey = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
        $this->backedUpExtensionConfiguration = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][Extension::KEY];
        $this->extensionConfiguration = new ExtensionConfiguration();
        $this->extensionConfiguration->synchronizeExtConfTemplateWithLocalConfiguration(Extension::KEY);
        $this->subject = new Configuration($this->extensionConfiguration, new HashService());
    }

    /**
     * @test
     */
    public function getLimitReturnsDefaultLimitIfNoLimitIsSet(): void
    {
        $this->setExtensionConfiguration();

        self::assertSame(250, $this->subject->getLimit());
    }

    /**
     * @test
     */
    public function getLimitReturnsDefaultLimitIfDefinedLimitIsNotNumeric(): void
    {
        $this->setExtensionConfiguration(['limit' => 'foo']);

        self::assertSame(250, $this->subject->getLimit());
    }

    /**
     * @test
     */
    public function getLimitReturnsAbsoluteValue(): void
    {
        $this->setExtensionConfiguration(['limit' => -1]);

        self::assertSame(1, $this->subject->getLimit());
    }

    /**
     * @test
     */
    public function getLimitReturnsDefinedLimit(): void
    {
        $this->setExtensionConfiguration(['limit' => 350]);

        self::assertSame(350, $this->subject->getLimit());
    }

    /**
     * @test
     */
    public function getCrawlerReturnsDefaultCrawlerIfNoCrawlerIsSet(): void
    {
        $this->setExtensionConfiguration();

        self::assertSame(ConcurrentUserAgentCrawler::class, $this->subject->getCrawler());
    }

    /**
     * @test
     */
    public function getCrawlerReturnsDefaultCrawlerIfDefinedCrawlerIsEmpty(): void
    {
        $this->setExtensionConfiguration(['crawler' => '']);

        self::assertSame(ConcurrentUserAgentCrawler::class, $this->subject->getCrawler());
    }

    /**
     * @test
     */
    public function getCrawlerReturnsDefaultCrawlerIfDefinedCrawlerIsInvalid(): void
    {
        $this->setExtensionConfiguration(['crawler' => 'foo']);

        self::assertSame(ConcurrentUserAgentCrawler::class, $this->subject->getCrawler());
    }

    /**
     * @test
     */
    public function getCrawlerReturnsDefinedCrawler(): void
    {
        $this->setExtensionConfiguration(['crawler' => ConcurrentCrawler::class]);

        self::assertSame(ConcurrentCrawler::class, $this->subject->getCrawler());
    }

    /**
     * @test
     */
    public function getCrawlerOptionsReturnsEmptyArrayIfNoCrawlerOptionsAreSet(): void
    {
        $this->setExtensionConfiguration();

        self::assertSame([], $this->subject->getCrawlerOptions());
    }

    /**
     * @test
     */
    public function getCrawlerOptionsReturnsEmptyArrayIfDefinedCrawlerOptionsAreOfInvalidType(): void
    {
        $this->setExtensionConfiguration(['crawlerOptions' => ['foo' => 'baz']]);

        self::assertSame([], $this->subject->getCrawlerOptions());
    }

    /**
     * @test
     */
    public function getCrawlerOptionsReturnsEmptyArrayIfDefinedCrawlerOptionsAreOfInvalidJson(): void
    {
        $this->setExtensionConfiguration(['crawlerOptions' => '"foo"']);

        self::assertSame([], $this->subject->getCrawlerOptions());
    }

    /**
     * @test
     */
    public function getCrawlerOptionsReturnsDefinedCrawlerOptions(): void
    {
        $this->setExtensionConfiguration(['crawlerOptions' => '{"foo":"baz"}']);

        self::assertSame(['foo' => 'baz'], $this->subject->getCrawlerOptions());
    }

    /**
     * @test
     */
    public function getVerboseCrawlerReturnsDefaultVerboseCrawlerIfNoVerboseCrawlerIsSet(): void
    {
        $this->setExtensionConfiguration();

        self::assertSame(OutputtingUserAgentCrawler::class, $this->subject->getVerboseCrawler());
    }

    /**
     * @test
     */
    public function getVerboseCrawlerReturnsDefaultVerboseCrawlerIfDefinedVerboseCrawlerIsEmpty(): void
    {
        $this->setExtensionConfiguration(['verboseCrawler' => '']);

        self::assertSame(OutputtingUserAgentCrawler::class, $this->subject->getVerboseCrawler());
    }

    /**
     * @test
     */
    public function getVerboseCrawlerReturnsDefaultVerboseCrawlerIfDefinedVerboseCrawlerIsInvalid(): void
    {
        $this->setExtensionConfiguration(['verboseCrawler' => 'foo']);

        self::assertSame(OutputtingUserAgentCrawler::class, $this->subject->getVerboseCrawler());
    }

    /**
     * @test
     */
    public function getVerboseCrawlerReturnsDefinedVerboseCrawler(): void
    {
        $this->setExtensionConfiguration(['verboseCrawler' => OutputtingCrawler::class]);

        self::assertSame(OutputtingCrawler::class, $this->subject->getVerboseCrawler());
    }

    /**
     * @test
     */
    public function getVerboseCrawlerOptionsReturnsEmptyArrayIfNoVerboseCrawlerOptionsAreSet(): void
    {
        $this->setExtensionConfiguration();

        self::assertSame([], $this->subject->getVerboseCrawlerOptions());
    }

    /**
     * @test
     */
    public function getVerboseCrawlerOptionsReturnsEmptyArrayIfDefinedVerboseCrawlerOptionsAreOfInvalidType(): void
    {
        $this->setExtensionConfiguration(['verboseCrawlerOptions' => ['foo' => 'baz']]);

        self::assertSame([], $this->subject->getVerboseCrawlerOptions());
    }

    /**
     * @test
     */
    public function getVerboseCrawlerOptionsReturnsEmptyArrayIfDefinedVerboseCrawlerOptionsAreOfInvalidJson(): void
    {
        $this->setExtensionConfiguration(['verboseCrawlerOptions' => '"foo"']);

        self::assertSame([], $this->subject->getVerboseCrawlerOptions());
    }

    /**
     * @test
     */
    public function getVerboseCrawlerOptionsReturnsDefinedVerboseCrawlerOptions(): void
    {
        $this->setExtensionConfiguration(['verboseCrawlerOptions' => '{"foo":"baz"}']);

        self::assertSame(['foo' => 'baz'], $this->subject->getVerboseCrawlerOptions());
    }

    /**
     * @test
     */
    public function getUserAgentReturnsCorrectlyGeneratedUserAgent(): void
    {
        $expected = 'TYPO3/tx_warming_crawler2cdfe0c134f3796954daf9395c034c39b542ca57';

        self::assertSame($expected, $this->subject->getUserAgent());
    }

    /**
     * @test
     */
    public function getAllReturnsCompleteExtensionConfiguration(): void
    {
        $configuration = [
            'crawler' => 'foo',
            'crawlerOptions' => '{"foo":"baz"}',
            'limit' => 'baz',
            'verboseCrawler' => 'hello',
            'verboseCrawlerOptions' => '{"foo":"baz"}',
        ];

        $this->setExtensionConfiguration($configuration);

        self::assertSame($configuration, $this->subject->getAll());
    }

    /**
     * @param array<string, mixed>|null $configuration
     */
    private function setExtensionConfiguration(array $configuration = null): void
    {
        $typo3Version = new Typo3Version();

        if ($configuration === null) {
            $this->extensionConfiguration->set(Extension::KEY);
            return;
        }

        if ($typo3Version->getMajorVersion() > 10) {
            $this->extensionConfiguration->set(Extension::KEY, $configuration);
            return;
        }

        foreach ($configuration as $key => $value) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            /** @phpstan-ignore-next-line */
            $this->extensionConfiguration->set(Extension::KEY, $key, $value);
        }
    }

    protected function tearDown(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = $this->backedUpEncryptionKey;
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][Extension::KEY] = $this->backedUpExtensionConfiguration;

        parent::tearDown();
    }
}
