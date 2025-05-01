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

namespace EliasHaeussler\Typo3Warming\Configuration;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming\Crawler;
use EliasHaeussler\Typo3Warming\Extension;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;

/**
 * Configuration
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\Autoconfigure(public: true)]
final class Configuration
{
    private const DEFAULT_CRAWLER = Crawler\ConcurrentUserAgentCrawler::class;
    private const DEFAULT_VERBOSE_CRAWLER = Crawler\OutputtingUserAgentCrawler::class;
    private const DEFAULT_LIMIT = 250;
    private const DEFAULT_SUPPORTED_DOKTYPES = [
        Core\Domain\Repository\PageRepository::DOKTYPE_DEFAULT,
    ];

    private readonly string $userAgent;
    private ?CacheWarmup\Crawler\CrawlerFactory $crawlerFactory = null;

    public function __construct(
        private readonly Core\Configuration\ExtensionConfiguration $configuration,
        private readonly CacheWarmup\Crawler\Strategy\CrawlingStrategyFactory $crawlingStrategyFactory,
        private readonly CacheWarmup\Config\Component\OptionsParser $optionsParser,
    ) {
        $this->userAgent = $this->generateUserAgent();
    }

    /**
     * @throws CacheWarmup\Exception\CrawlerDoesNotExist
     * @throws CacheWarmup\Exception\CrawlerIsInvalid
     * @throws CacheWarmup\Exception\OptionsAreInvalid
     * @throws CacheWarmup\Exception\OptionsAreMalformed
     */
    public function getCrawler(): CacheWarmup\Crawler\Crawler
    {
        $crawlerOptions = [];

        try {
            /** @var class-string<CacheWarmup\Crawler\Crawler>|null $crawlerClass */
            $crawlerClass = $this->configuration->get(Extension::KEY, 'crawler');

            if (!\is_string($crawlerClass) ||
                !\is_a($crawlerClass, CacheWarmup\Crawler\Crawler::class, true)
            ) {
                $crawlerClass = self::DEFAULT_CRAWLER;
            } else {
                $crawlerOptions = $this->getCrawlerOptions();
            }
        } catch (Core\Exception) {
            $crawlerClass = self::DEFAULT_VERBOSE_CRAWLER;
        }

        return $this->getCrawlerFactory()->get($crawlerClass, $crawlerOptions);
    }

    /**
     * @return array<string, mixed>
     * @throws CacheWarmup\Exception\OptionsAreInvalid
     * @throws CacheWarmup\Exception\OptionsAreMalformed
     */
    public function getCrawlerOptions(): array
    {
        try {
            $json = $this->configuration->get(Extension::KEY, 'crawlerOptions');

            // Early return if no crawler options are configured
            if (!\is_string($json) || $json === '') {
                return [];
            }

            return $this->optionsParser->parse($json);
        } catch (Core\Exception) {
            return [];
        }
    }

    /**
     * @throws CacheWarmup\Exception\CrawlerDoesNotExist
     * @throws CacheWarmup\Exception\CrawlerIsInvalid
     * @throws CacheWarmup\Exception\OptionsAreInvalid
     * @throws CacheWarmup\Exception\OptionsAreMalformed
     */
    public function getVerboseCrawler(): CacheWarmup\Crawler\VerboseCrawler
    {
        $crawlerOptions = [];

        try {
            /** @var class-string<CacheWarmup\Crawler\VerboseCrawler>|null $crawlerClass */
            $crawlerClass = $this->configuration->get(Extension::KEY, 'verboseCrawler');

            if (!\is_string($crawlerClass) ||
                !is_a($crawlerClass, CacheWarmup\Crawler\VerboseCrawler::class, true)
            ) {
                $crawlerClass = self::DEFAULT_VERBOSE_CRAWLER;
            } else {
                $crawlerOptions = $this->getVerboseCrawlerOptions();
            }
        } catch (Core\Exception) {
            $crawlerClass = self::DEFAULT_VERBOSE_CRAWLER;
        }

        /** @var CacheWarmup\Crawler\VerboseCrawler $crawler */
        $crawler = $this->getCrawlerFactory()->get($crawlerClass, $crawlerOptions);

        return $crawler;
    }

    /**
     * @return array<string, mixed>
     * @throws CacheWarmup\Exception\OptionsAreInvalid
     * @throws CacheWarmup\Exception\OptionsAreMalformed
     */
    public function getVerboseCrawlerOptions(): array
    {
        try {
            $json = $this->configuration->get(Extension::KEY, 'verboseCrawlerOptions');

            // Early return if no crawler options are configured
            if (!\is_string($json) || $json === '') {
                return [];
            }

            return $this->optionsParser->parse($json);
        } catch (Core\Exception) {
            return [];
        }
    }

    /**
     * @return array<string, mixed>
     * @throws CacheWarmup\Exception\OptionsAreInvalid
     * @throws CacheWarmup\Exception\OptionsAreMalformed
     */
    public function getParserOptions(): array
    {
        try {
            $json = $this->configuration->get(Extension::KEY, 'parserOptions');

            // Early return if no parser options are configured
            if (!\is_string($json) || $json === '') {
                return [];
            }

            return $this->optionsParser->parse($json);
        } catch (Core\Exception) {
            return [];
        }
    }

    /**
     * @return array<string, mixed>
     * @throws CacheWarmup\Exception\OptionsAreInvalid
     * @throws CacheWarmup\Exception\OptionsAreMalformed
     */
    public function getClientOptions(): array
    {
        try {
            $json = $this->configuration->get(Extension::KEY, 'clientOptions');

            // Early return if no client options are configured
            if (!\is_string($json) || $json === '') {
                return [];
            }

            return $this->optionsParser->parse($json);
        } catch (Core\Exception) {
            return [];
        }
    }

    /**
     * @return non-negative-int
     */
    public function getLimit(): int
    {
        try {
            $limit = $this->configuration->get(Extension::KEY, 'limit');

            if (!is_numeric($limit)) {
                return self::DEFAULT_LIMIT;
            }

            return max(0, (int)$limit);
        } catch (Core\Exception) {
            return self::DEFAULT_LIMIT;
        }
    }

    /**
     * @return list<string>
     */
    public function getExcludePatterns(): array
    {
        try {
            $exclude = $this->configuration->get(Extension::KEY, 'exclude');

            // Early return if no exclude patterns are configured
            if (!\is_string($exclude) || $exclude === '') {
                return [];
            }

            return Core\Utility\GeneralUtility::trimExplode(',', $exclude, true);
        } catch (Core\Exception) {
            return [];
        }
    }

    public function getStrategy(): ?CacheWarmup\Crawler\Strategy\CrawlingStrategy
    {
        try {
            $strategy = $this->configuration->get(Extension::KEY, 'strategy');

            // Early return if no crawling strategy is configured
            if (!\is_string($strategy) || $strategy === '') {
                return null;
            }

            // Early return if configured crawling strategy is invalid
            if (!$this->crawlingStrategyFactory->has($strategy)) {
                return null;
            }

            return $this->crawlingStrategyFactory->get($strategy);
        } catch (Core\Exception) {
            return null;
        }
    }

    public function isEnabledInPageTree(): bool
    {
        try {
            $enablePageTree = $this->configuration->get(Extension::KEY, 'enablePageTree');

            return (bool)$enablePageTree;
        } catch (Core\Exception) {
            return true;
        }
    }

    /**
     * @return list<int>
     */
    public function getSupportedDoktypes(): array
    {
        try {
            $doktypes = $this->configuration->get(Extension::KEY, 'supportedDoktypes');

            if (!\is_string($doktypes)) {
                return self::DEFAULT_SUPPORTED_DOKTYPES;
            }

            return Core\Utility\GeneralUtility::intExplode(',', $doktypes, true);
        } catch (Core\Exception) {
            return self::DEFAULT_SUPPORTED_DOKTYPES;
        }
    }

    public function isEnabledInToolbar(): bool
    {
        try {
            $enableToolbar = $this->configuration->get(Extension::KEY, 'enableToolbar');

            return (bool)$enableToolbar;
        } catch (Core\Exception) {
            return true;
        }
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    private function generateUserAgent(): string
    {
        $string = 'TYPO3/tx_warming_crawler';

        if (class_exists(Core\Crypto\HashService::class)) {
            return Core\Utility\GeneralUtility::makeInstance(Core\Crypto\HashService::class)->appendHmac(
                $string,
                self::class,
            );
        }

        // @todo Remove once support for TYPO3 v12 is dropped
        /* @phpstan-ignore classConstant.deprecatedClass, method.deprecatedClass */
        return Core\Utility\GeneralUtility::makeInstance(Extbase\Security\Cryptography\HashService::class)->appendHmac(
            $string,
        );
    }

    private function getCrawlerFactory(): CacheWarmup\Crawler\CrawlerFactory
    {
        // Cannot be instantiated with DI, would lead to circular dependencies
        return $this->crawlerFactory ??= Core\Utility\GeneralUtility::makeInstance(
            CacheWarmup\Crawler\CrawlerFactory::class,
        );
    }
}
