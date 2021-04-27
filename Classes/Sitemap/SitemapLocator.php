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

namespace EliasHaeussler\Typo3CacheWarmup\Sitemap;

use EliasHaeussler\CacheWarmup\Sitemap;
use EliasHaeussler\Typo3CacheWarmup\Cache\CacheManager;
use EliasHaeussler\Typo3CacheWarmup\Exception\UnsupportedConfigurationException;
use EliasHaeussler\Typo3CacheWarmup\Exception\UnsupportedSiteException;
use EliasHaeussler\Typo3CacheWarmup\Sitemap\Provider\ProviderInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * SitemapLocator
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class SitemapLocator
{
    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var ProviderInterface[]
     */
    protected $providers = [];

    /**
     * @param RequestFactory $requestFactory
     * @param ProviderInterface[] $providers
     */
    public function __construct(RequestFactory $requestFactory, CacheManager $cacheManager, array $providers)
    {
        $this->requestFactory = $requestFactory;
        $this->cacheManager = $cacheManager;
        $this->providers = $providers;
        ksort($this->providers);

        $this->validateProviders();
    }

    /**
     * @param Site $site
     * @return Sitemap
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function locateBySite(Site $site): Sitemap
    {
        // Get sitemap from cache
        if (($sitemapUrl = $this->cacheManager->get($site)) !== null) {
            return new Sitemap(new Uri($sitemapUrl));
        }

        $baseUrl = $site->getBase();

        if ($baseUrl->getHost() === '') {
            throw UnsupportedConfigurationException::forBaseUrl((string)$baseUrl);
        }

        $sitemap = $this->resolveSitemap($site);

        if ($sitemap === null) {
            throw UnsupportedSiteException::forMissingSitemap($site);
        }

        $this->cacheManager->set($site, $sitemap);

        return $sitemap;
    }

    public function siteContainsSitemap(Site $site): bool
    {
        try {
            $sitemap = $this->locateBySite($site);
            $response = $this->requestFactory->request((string)$sitemap->getUri(), 'HEAD');

            return $response->getStatusCode() < 400;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function resolveSitemap(Site $site): ?Sitemap
    {
        foreach ($this->providers as $provider) {
            if (($sitemap = $provider->get($site)) !== null) {
                return $sitemap;
            }
        }

        return null;
    }

    protected function validateProviders(): void
    {
        foreach ($this->providers as $provider) {
            if (!is_object($provider)) {
                throw new \InvalidArgumentException(
                    sprintf('Providers must be of type object, "%s" given.', gettype($provider)),
                    1619525071
                );
            }
            if (!in_array(ProviderInterface::class, class_implements($provider))) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The given provider "%s" does not implement the interface "%s".',
                        get_class($provider),
                        ProviderInterface::class
                    ),
                    1619524996
                );
            }
        }
    }
}
