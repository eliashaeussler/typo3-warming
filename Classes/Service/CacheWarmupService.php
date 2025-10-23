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

namespace EliasHaeussler\Typo3Warming\Service;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Domain;
use EliasHaeussler\Typo3Warming\Event;
use EliasHaeussler\Typo3Warming\Http;
use EliasHaeussler\Typo3Warming\Result;
use EliasHaeussler\Typo3Warming\ValueObject;
use GuzzleHttp\Exception\GuzzleException;
use Psr\EventDispatcher;
use Symfony\Component\DependencyInjection;

/**
 * CacheWarmupService
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\Autoconfigure(public: true)]
final readonly class CacheWarmupService
{
    private CacheWarmup\Crawler\Crawler $crawler;

    /**
     * @throws CacheWarmup\Exception\CrawlerDoesNotExist
     * @throws CacheWarmup\Exception\CrawlerIsInvalid
     */
    public function __construct(
        private CacheWarmup\Http\Client\ClientFactory $clientFactory,
        private Configuration\Configuration $configuration,
        private EventDispatcher\EventDispatcherInterface $eventDispatcher,
        private Typo3SitemapLocator\Sitemap\SitemapLocator $sitemapLocator,
        private Http\Message\PageUriBuilder $pageUriBuilder,
    ) {
        $this->crawler = $this->configuration->getCrawler();
    }

    /**
     * @param list<ValueObject\Request\SiteWarmupRequest> $sites
     * @param non-negative-int|null $limit
     * @param list<ValueObject\Request\PageWarmupRequest> $pages
     * @throws CacheWarmup\Exception\Exception
     * @throws GuzzleException
     * @throws Typo3SitemapLocator\Exception\BaseUrlIsNotSupported
     * @throws Typo3SitemapLocator\Exception\SitemapIsMissing
     */
    public function warmup(
        array $sites = [],
        array $pages = [],
        ?int $limit = null,
        ?CacheWarmup\Crawler\Strategy\CrawlingStrategy $strategy = null,
    ): Result\CacheWarmupResult {
        $strategy ??= $this->configuration->crawlingStrategy;
        $cacheWarmer = new CacheWarmup\CacheWarmer(
            $limit ?? $this->configuration->limit,
            $this->crawler,
            $strategy,
            new CacheWarmup\Xml\SitemapXmlParser(
                $this->configuration->parserOptions,
                $this->clientFactory->get(),
            ),
            true,
            array_map(
                CacheWarmup\Config\Option\ExcludePattern::create(...),
                $this->configuration->excludePatterns,
            ),
            $this->eventDispatcher,
        );

        foreach ($sites as $siteWarmupRequest) {
            foreach ($siteWarmupRequest->getLanguageIds() as $languageId) {
                $siteLanguage = $siteWarmupRequest->getSite()->getLanguageById($languageId);
                $sitemaps = $this->sitemapLocator->locateBySite($siteWarmupRequest->getSite(), $siteLanguage);
                $cacheWarmer->addSitemaps(
                    array_map(
                        Domain\Model\SiteAwareSitemap::fromLocatedSitemap(...),
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
                $uri = $this->pageUriBuilder->build($pageWarmupRequest->getPage(), $languageId);

                if ($uri !== null) {
                    $cacheWarmer->addUrl((string)$uri);
                }
            }
        }

        $this->eventDispatcher->dispatch(
            new Event\BeforeCacheWarmupEvent($sites, $pages, $strategy, $this->crawler, $cacheWarmer),
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

    public function getCrawler(): CacheWarmup\Crawler\Crawler
    {
        return $this->crawler;
    }
}
