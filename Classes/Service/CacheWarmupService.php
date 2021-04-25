<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "cache_warmup".
 *
 * Copyright (C) 2021 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3CacheWarmup\Service;

use EliasHaeussler\CacheWarmup\CacheWarmer;
use EliasHaeussler\CacheWarmup\Crawler\CrawlerInterface;
use EliasHaeussler\Typo3CacheWarmup\Configuration\Extension;
use EliasHaeussler\Typo3CacheWarmup\Exception\UnsupportedConfigurationException;
use EliasHaeussler\Typo3CacheWarmup\Exception\UnsupportedSiteException;
use EliasHaeussler\Typo3CacheWarmup\Sitemap\SitemapLocator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Exception;
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
     * @var CrawlerInterface|null
     */
    protected $crawler;

    /**
     * @param SiteFinder $siteFinder
     * @param SitemapLocator $sitemapLocator
     * @param ExtensionConfiguration $extensionConfiguration
     * @throws Exception
     * @throws UnsupportedConfigurationException
     */
    public function __construct(
        SiteFinder $siteFinder,
        SitemapLocator $sitemapLocator,
        ExtensionConfiguration $extensionConfiguration
    ) {
        $this->siteFinder = $siteFinder;
        $this->sitemapLocator = $sitemapLocator;
        $this->limit = abs((int)$extensionConfiguration->get(Extension::KEY, 'limit'));
        $this->crawler = $this->initializeCrawler($extensionConfiguration->get(Extension::KEY, 'crawler'));
    }

    /**
     * @param Site[] $sites
     * @return CrawlerInterface
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function warmupSites(array $sites): CrawlerInterface
    {
        $cacheWarmer = new CacheWarmer();
        $cacheWarmer->setLimit($this->limit);

        foreach ($sites as $site) {
            $sitemap = $this->sitemapLocator->locateBySite($site);
            $cacheWarmer->addSitemaps($sitemap);
        }

        return $cacheWarmer->run($this->crawler);
    }

    /**
     * @param int[] $pageIds
     * @return CrawlerInterface
     * @throws SiteNotFoundException
     */
    public function warmupPages(array $pageIds): CrawlerInterface
    {
        $cacheWarmer = new CacheWarmer();
        $cacheWarmer->setLimit($this->limit);

        foreach ($pageIds as $pageId) {
            $site = $this->siteFinder->getSiteByPageId($pageId);
            $url = $site->getRouter()->generateUri((string)$pageId);
            $cacheWarmer->addUrl($url);
        }

        return $cacheWarmer->run($this->crawler);
    }

    /**
     * @return CrawlerInterface|null
     */
    public function getCrawler(): ?CrawlerInterface
    {
        return $this->crawler;
    }

    /**
     * @param string|CrawlerInterface|null $crawler
     * @return self
     * @throws UnsupportedConfigurationException
     */
    public function setCrawler($crawler): self
    {
        $this->crawler = $this->initializeCrawler($crawler);
        return $this;
    }

    /**
     * @param string|CrawlerInterface|null $crawler
     * @return CrawlerInterface|null
     * @throws UnsupportedConfigurationException
     */
    protected function initializeCrawler($crawler): ?CrawlerInterface
    {
        if ($crawler instanceof CrawlerInterface) {
            return $crawler;
        }

        // Early return if no crawler is given
        if (empty($crawler)) {
            return null;
        }

        // Throw exception if crawler variable type is unsupported
        if (!is_string($crawler)) {
            throw UnsupportedConfigurationException::forTypeMismatch('string', gettype($crawler));
        }

        // Throw exception if crawler class does not exist
        if (!class_exists($crawler)) {
            throw UnsupportedConfigurationException::forUnresolvableClass($crawler);
        }

        // Throw exception if crawler class is invalid
        if (!in_array(CrawlerInterface::class, class_implements($crawler))) {
            throw UnsupportedConfigurationException::forMissingImplementation($crawler, CrawlerInterface::class);
        }

        /** @var CrawlerInterface $instance */
        $instance = GeneralUtility::makeInstance($crawler);
        return $instance;
    }
}
