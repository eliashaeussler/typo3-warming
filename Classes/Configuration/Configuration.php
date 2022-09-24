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

namespace EliasHaeussler\Typo3Warming\Configuration;

use EliasHaeussler\CacheWarmup\Crawler\CrawlerInterface;
use EliasHaeussler\Typo3Warming\Crawler\ConcurrentUserAgentCrawler;
use EliasHaeussler\Typo3Warming\Crawler\OutputtingUserAgentCrawler;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;

/**
 * Configuration
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class Configuration
{
    public const DEFAULT_LIMIT = 250;
    public const DEFAULT_CRAWLER = ConcurrentUserAgentCrawler::class;
    public const DEFAULT_VERBOSE_CRAWLER = OutputtingUserAgentCrawler::class;

    /**
     * @var ExtensionConfiguration
     */
    private $configuration;

    /**
     * @var HashService
     */
    private $hashService;

    /**
     * @var string
     */
    private $userAgent;

    public function __construct(ExtensionConfiguration $configuration, HashService $hashService)
    {
        $this->configuration = $configuration;
        $this->hashService = $hashService;
        $this->userAgent = $this->generateUserAgent();
    }

    public function getLimit(): int
    {
        try {
            $limit = $this->configuration->get(Extension::KEY, 'limit');

            if (!is_numeric($limit)) {
                return self::DEFAULT_LIMIT;
            }

            return abs((int)$limit);
        } catch (Exception $e) {
            return self::DEFAULT_LIMIT;
        }
    }

    /**
     * @return class-string<CrawlerInterface>
     */
    public function getCrawler(): string
    {
        try {
            /** @var class-string<CrawlerInterface>|null $crawler */
            $crawler = $this->configuration->get(Extension::KEY, 'crawler');

            if (!\is_string($crawler)) {
                return self::DEFAULT_CRAWLER;
            }

            return $crawler ?: self::DEFAULT_CRAWLER;
        } catch (Exception $e) {
            return self::DEFAULT_CRAWLER;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getCrawlerOptions(): array
    {
        try {
            $json = $this->configuration->get(Extension::KEY, 'crawlerOptions');

            return $this->parseCrawlerOptions($json);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @return class-string<CrawlerInterface>
     */
    public function getVerboseCrawler(): string
    {
        try {
            /** @var class-string<CrawlerInterface>|null $crawler */
            $crawler = $this->configuration->get(Extension::KEY, 'verboseCrawler');
            return !empty($crawler) ? (string)$crawler : self::DEFAULT_VERBOSE_CRAWLER;
        } catch (Exception $e) {
            return self::DEFAULT_VERBOSE_CRAWLER;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getVerboseCrawlerOptions(): array
    {
        try {
            $json = $this->configuration->get(Extension::KEY, 'verboseCrawlerOptions');

            return $this->parseCrawlerOptions($json);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAll(): array
    {
        try {
            $configuration = $this->configuration->get(Extension::KEY);
            \assert(\is_array($configuration));

            return $configuration;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @param mixed $json
     * @return array<string, mixed>
     */
    private function parseCrawlerOptions($json): array
    {
        if (!\is_string($json)) {
            return [];
        }

        $crawlerOptions = json_decode($json, true);

        if (!\is_array($crawlerOptions)) {
            return [];
        }

        return $crawlerOptions;
    }

    private function generateUserAgent(): string
    {
        return $this->hashService->appendHmac('TYPO3/tx_warming_crawler');
    }
}
