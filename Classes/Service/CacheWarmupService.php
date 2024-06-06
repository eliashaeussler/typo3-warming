<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Service;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Crawler;
use EliasHaeussler\Typo3Warming\Domain;
use EliasHaeussler\Typo3Warming\Event;
use EliasHaeussler\Typo3Warming\Http;
use EliasHaeussler\Typo3Warming\Result;
use EliasHaeussler\Typo3Warming\Utility;
use EliasHaeussler\Typo3Warming\ValueObject;
use GuzzleHttp\Exception\GuzzleException;
use Psr\EventDispatcher;
use TYPO3\CMS\Core;

/**
 * CacheWarmupService
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class CacheWarmupService
{
    private CacheWarmup\Crawler\CrawlerInterface $crawler;

    /**
     * @throws CacheWarmup\Exception\InvalidCrawlerException
     */
    public function __construct(
        private readonly Http\Client\ClientFactory $clientFactory,
        private readonly Configuration\Configuration $configuration,
        private readonly CacheWarmup\Crawler\CrawlerFactory $crawlerFactory,
        private readonly Crawler\Strategy\CrawlingStrategyFactory $crawlingStrategyFactory,
        private readonly EventDispatcher\EventDispatcherInterface $eventDispatcher,
        private readonly Typo3SitemapLocator\Sitemap\SitemapLocator $sitemapLocator,
    ) {
        $this->setCrawler(
            $this->configuration->getCrawler(),
            $this->configuration->getCrawlerOptions(),
        );
    }

    /**
     * @param list<ValueObject\Request\SiteWarmupRequest> $sites
     * @param list<ValueObject\Request\PageWarmupRequest> $pages
     * @throws CacheWarmup\Exception\Exception
     * @throws Core\Exception\SiteNotFoundException
     * @throws GuzzleException
     * @throws Typo3SitemapLocator\Exception\BaseUrlIsNotSupported
     * @throws Typo3SitemapLocator\Exception\SitemapIsMissing
     */
    public function warmup(
        array $sites = [],
        array $pages = [],
        int $limit = null,
        string $strategy = null,
    ): Result\CacheWarmupResult {
        $crawlingStrategy = $this->createCrawlingStrategy($strategy);
        $cacheWarmer = new CacheWarmup\CacheWarmer(
            $limit ?? $this->configuration->getLimit(),
            $this->clientFactory->get($this->configuration->getParserClientOptions()),
            $this->crawler,
            $crawlingStrategy,
            true,
            $this->configuration->getExcludePatterns(),
        );

        foreach ($sites as $siteWarmupRequest) {
            foreach ($siteWarmupRequest->getLanguageIds() as $languageId) {
                $siteLanguage = $siteWarmupRequest->getSite()->getLanguageById($languageId);
                $sitemaps = $this->sitemapLocator->locateBySite($siteWarmupRequest->getSite(), $siteLanguage);
                $cacheWarmer->addSitemaps(
                    array_map(
                        static fn(Typo3SitemapLocator\Domain\Model\Sitemap $sitemap) => Domain\Model\SiteAwareSitemap::fromLocatedSitemap($sitemap),
                        $sitemaps,
                    ),
                );
            }
        }

        foreach ($pages as $pageWarmupRequest) {
            $languageIds = [null];

            if ($pageWarmupRequest->getLanguageIds() !== []) {
                $languageIds = $pageWarmupRequest->getLanguageIds();
            }

            foreach ($languageIds as $languageId) {
                $url = Utility\HttpUtility::generateUri($pageWarmupRequest->getPage(), $languageId);

                if ($url !== null) {
                    $cacheWarmer->addUrl((string)$url);
                }
            }
        }

        $this->eventDispatcher->dispatch(
            new Event\BeforeCacheWarmupEvent($sites, $pages, $crawlingStrategy, $this->crawler, $cacheWarmer),
        );

        $result = new Result\CacheWarmupResult(
            $cacheWarmer->run(),
            $cacheWarmer->getExcludedSitemaps(),
            $cacheWarmer->getExcludedUrls(),
        );

        $this->eventDispatcher->dispatch(
            new Event\AfterCacheWarmupEvent($result, $this->crawler, $cacheWarmer),
        );

        return $result;
    }

    public function getCrawler(): CacheWarmup\Crawler\CrawlerInterface
    {
        return $this->crawler;
    }

    /**
     * @param class-string<CacheWarmup\Crawler\CrawlerInterface>|CacheWarmup\Crawler\CrawlerInterface $crawler
     * @param array<string, mixed> $options
     * @throws CacheWarmup\Exception\InvalidCrawlerException
     */
    public function setCrawler(string|CacheWarmup\Crawler\CrawlerInterface $crawler, array $options = []): self
    {
        if ($options !== []) {
            $options = $this->crawlerFactory->parseCrawlerOptions($options);
        }

        if ($crawler instanceof CacheWarmup\Crawler\ConfigurableCrawlerInterface && $options !== []) {
            $crawler->setOptions($options);
        }

        if (\is_string($crawler)) {
            $this->crawler = $this->crawlerFactory->get($crawler, $options);
        } else {
            $this->crawler = $crawler;
        }

        return $this;
    }

    private function createCrawlingStrategy(string $strategy = null): ?CacheWarmup\Crawler\Strategy\CrawlingStrategy
    {
        $strategy ??= $this->configuration->getStrategy();

        if ($strategy !== null) {
            return $this->crawlingStrategyFactory->get($strategy);
        }

        return null;
    }
}
