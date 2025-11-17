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
use EliasHaeussler\Typo3Warming\Http;
use mteu\TypedExtConf;
use TYPO3\CMS\Core;

/**
 * Configuration
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[TypedExtConf\Attribute\ExtensionConfig(extensionKey: Extension::KEY)]
final class Configuration
{
    private Http\Message\Request\RequestOptions $requestOptions;
    private ?CacheWarmup\Crawler\CrawlerFactory $crawlerFactory = null;

    /**
     * @param class-string<CacheWarmup\Crawler\Crawler> $crawlerClass
     * @param array<string, mixed> $crawlerOptions
     * @param class-string<CacheWarmup\Crawler\VerboseCrawler> $verboseCrawlerClass
     * @param array<string, mixed> $verboseCrawlerOptions
     * @param array<string, mixed> $parserOptions
     * @param array<string, mixed> $clientOptions
     * @param non-negative-int $limit
     * @param list<non-empty-string> $excludePatterns
     * @param list<int> $supportedDoktypes
     */
    public function __construct(
        #[TypedExtConf\Attribute\ExtConfProperty(path: 'crawler')]
        public readonly string $crawlerClass = Crawler\ConcurrentUserAgentCrawler::class,
        #[TypedExtConf\Attribute\ExtConfProperty]
        public readonly array $crawlerOptions = [],
        #[TypedExtConf\Attribute\ExtConfProperty(path: 'verboseCrawler')]
        public readonly string $verboseCrawlerClass = Crawler\OutputtingUserAgentCrawler::class,
        #[TypedExtConf\Attribute\ExtConfProperty]
        public readonly array $verboseCrawlerOptions = [],
        #[TypedExtConf\Attribute\ExtConfProperty]
        public readonly array $parserOptions = [],
        #[TypedExtConf\Attribute\ExtConfProperty]
        public readonly array $clientOptions = [],
        #[TypedExtConf\Attribute\ExtConfProperty]
        public readonly int $limit = 250,
        #[TypedExtConf\Attribute\ExtConfProperty(path: 'exclude')]
        public readonly array $excludePatterns = [],
        #[TypedExtConf\Attribute\ExtConfProperty(path: 'strategy')]
        public readonly ?CacheWarmup\Crawler\Strategy\CrawlingStrategy $crawlingStrategy = null,
        #[TypedExtConf\Attribute\ExtConfProperty(path: 'enablePageTree')]
        public readonly bool $enabledInPageTree = true,
        #[TypedExtConf\Attribute\ExtConfProperty]
        public readonly array $supportedDoktypes = [Core\Domain\Repository\PageRepository::DOKTYPE_DEFAULT],
        #[TypedExtConf\Attribute\ExtConfProperty(path: 'enableToolbar')]
        public readonly bool $enabledInToolbar = true,
    ) {}

    public function injectRequestOptions(Http\Message\Request\RequestOptions $requestOptions): void
    {
        $this->requestOptions = $requestOptions;
    }

    /**
     * @param array{} $arguments
     *
     * @todo Remove with v5.0
     */
    public function __call(string $name, array $arguments): mixed
    {
        $propertyName = match ($name) {
            'getCrawlerOptions' => 'crawlerOptions',
            'getVerboseCrawlerOptions' => 'verboseCrawlerOptions',
            'getParserOptions' => 'parserOptions',
            'getClientOptions' => 'clientOptions',
            'getLimit' => 'limit',
            'getExcludePatterns' => 'excludePatterns',
            'getStrategy' => 'crawlingStrategy',
            'isEnabledInPageTree' => 'enabledInPageTree',
            'getSupportedDoktypes' => 'supportedDoktypes',
            'isEnabledInToolbar' => 'enabledInToolbar',
            default => throw new \BadMethodCallException(
                \sprintf('Unknown method "%s".', $name),
                1753475960,
            ),
        };

        trigger_error(
            \sprintf(
                'Method "%s::%s()" is deprecated and will be removed in v5.0. Access class property "$%s" directly.',
                self::class,
                $name,
                $propertyName,
            ),
            E_USER_DEPRECATED,
        );

        /* @phpstan-ignore property.dynamicName */
        return $this->{$propertyName};
    }

    /**
     * @throws CacheWarmup\Exception\CrawlerDoesNotExist
     * @throws CacheWarmup\Exception\CrawlerIsInvalid
     */
    public function getCrawler(): CacheWarmup\Crawler\Crawler
    {
        return $this->getCrawlerFactory()->get($this->crawlerClass, $this->crawlerOptions);
    }

    /**
     * @throws CacheWarmup\Exception\CrawlerDoesNotExist
     * @throws CacheWarmup\Exception\CrawlerIsInvalid
     */
    public function getVerboseCrawler(): CacheWarmup\Crawler\VerboseCrawler
    {
        return $this->getCrawlerFactory()->get($this->verboseCrawlerClass, $this->verboseCrawlerOptions);
    }

    /**
     * @deprecated Use {@see RequestOptions::getUserAgent()} instead. Will be removed in v5.0.
     */
    public function getUserAgent(): string
    {
        trigger_error(
            \sprintf(
                'Method "%s()" is deprecated and will be removed in v5.0. ' .
                'Access User-Agent header via "%s::getUserAgent()" method instead.',
                __METHOD__,
                Http\Message\Request\RequestOptions::class,
            ),
            E_USER_DEPRECATED,
        );

        return $this->requestOptions->getUserAgent();
    }

    private function getCrawlerFactory(): CacheWarmup\Crawler\CrawlerFactory
    {
        return $this->crawlerFactory ??= Core\Utility\GeneralUtility::makeInstance(
            CacheWarmup\Crawler\CrawlerFactory::class,
        );
    }
}
