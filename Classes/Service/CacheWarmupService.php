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

namespace EliasHaeussler\Typo3Warming\Service;

use EliasHaeussler\CacheWarmup\CacheWarmer;
use EliasHaeussler\CacheWarmup\Crawler\ConfigurableCrawlerInterface;
use EliasHaeussler\CacheWarmup\Crawler\CrawlerInterface;
use EliasHaeussler\Typo3Warming\Configuration\Configuration;
use EliasHaeussler\Typo3Warming\Crawler\RequestAwareInterface;
use EliasHaeussler\Typo3Warming\Exception\UnsupportedConfigurationException;
use EliasHaeussler\Typo3Warming\Exception\UnsupportedSiteException;
use EliasHaeussler\Typo3Warming\Request\WarmupRequest;
use EliasHaeussler\Typo3Warming\Sitemap\SitemapLocator;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * CacheWarmupService
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class CacheWarmupService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var SiteFinder
     */
    protected $siteFinder;

    /**
     * @var SitemapLocator
     */
    protected $sitemapLocator;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var CrawlerInterface
     */
    protected $crawler;

    /**
     * @throws UnsupportedConfigurationException
     */
    public function __construct(
        SiteFinder $siteFinder,
        SitemapLocator $sitemapLocator,
        Configuration $configuration
    ) {
        $this->siteFinder = $siteFinder;
        $this->sitemapLocator = $sitemapLocator;
        $this->limit = $configuration->getLimit();
        $this->crawler = $this->initializeCrawler(
            $configuration->getCrawler(),
            $configuration->getCrawlerOptions()
        );
    }

    /**
     * @param Site[] $sites
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function warmupSites(array $sites, WarmupRequest $request): CrawlerInterface
    {
        $cacheWarmer = new CacheWarmer();
        $cacheWarmer->setLimit($this->limit);

        foreach ($sites as $site) {
            $siteLanguage = null;
            if ($request->getLanguageId() !== null) {
                $siteLanguage = $site->getLanguageById($request->getLanguageId());
            }
            $sitemap = $this->sitemapLocator->locateBySite($site, $siteLanguage);
            $cacheWarmer->addSitemaps($sitemap);
        }

        if ($this->crawler instanceof RequestAwareInterface) {
            $request->setRequestedUrls($cacheWarmer->getUrls());
            $this->crawler->setRequest($request);
        }

        return $cacheWarmer->run($this->crawler);
    }

    /**
     * @param int[] $pageIds
     * @throws SiteNotFoundException
     */
    public function warmupPages(array $pageIds, WarmupRequest $request): CrawlerInterface
    {
        $cacheWarmer = new CacheWarmer();
        $cacheWarmer->setLimit($this->limit);

        foreach ($pageIds as $pageId) {
            $url = $this->generateUri($pageId, $request->getLanguageId());
            $cacheWarmer->addUrl($url);
        }

        if ($this->crawler instanceof RequestAwareInterface) {
            $request->setRequestedUrls($cacheWarmer->getUrls());
            $this->crawler->setRequest($request);
        }

        return $cacheWarmer->run($this->crawler);
    }

    /**
     * @throws SiteNotFoundException
     */
    public function generateUri(int $pageId, int $languageId = null): UriInterface
    {
        $site = $this->siteFinder->getSiteByPageId($pageId);

        return $site->getRouter()->generateUri((string)$pageId, ['_language' => $languageId]);
    }

    public function getCrawler(): CrawlerInterface
    {
        return $this->crawler;
    }

    /**
     * @param class-string<CrawlerInterface>|CrawlerInterface $crawler
     * @param array<string, mixed> $options
     * @throws UnsupportedConfigurationException
     */
    public function setCrawler($crawler, array $options = []): self
    {
        $this->crawler = $this->initializeCrawler($crawler, $options);
        return $this;
    }

    /**
     * @param class-string<CrawlerInterface>|CrawlerInterface $crawler
     * @param array<string, mixed> $options
     * @throws UnsupportedConfigurationException
     */
    protected function initializeCrawler($crawler, array $options = []): CrawlerInterface
    {
        if ($crawler instanceof CrawlerInterface) {
            goto configurableCrawler;
        }

        // Use default crawler if no custom crawler is given
        if (empty($crawler)) {
            $crawler = Configuration::DEFAULT_CRAWLER;
        }

        // Throw exception if crawler variable type is unsupported
        if (!\is_string($crawler)) {
            throw UnsupportedConfigurationException::forTypeMismatch('string', \gettype($crawler));
        }

        // Throw exception if crawler class does not exist
        if (!class_exists($crawler)) {
            throw UnsupportedConfigurationException::forUnresolvableClass($crawler);
        }

        // Throw exception if crawler class is invalid
        if (!\in_array(CrawlerInterface::class, class_implements($crawler) ?: [])) {
            throw UnsupportedConfigurationException::forMissingImplementation($crawler, CrawlerInterface::class);
        }

        // Instantiate crawler
        $crawler = GeneralUtility::makeInstance($crawler);

        // Apply crawler options to configurable crawler
        configurableCrawler:
        if ($crawler instanceof ConfigurableCrawlerInterface) {
            $crawler->setOptions($options);
        }

        return $crawler;
    }
}
