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

namespace EliasHaeussler\Typo3Warming\Sitemap;

use EliasHaeussler\Typo3Warming\Cache\CacheManager;
use EliasHaeussler\Typo3Warming\Exception\UnsupportedConfigurationException;
use EliasHaeussler\Typo3Warming\Exception\UnsupportedSiteException;
use EliasHaeussler\Typo3Warming\Sitemap\Provider\ProviderInterface;
use EliasHaeussler\Typo3Warming\Traits\BackendUserAuthenticationTrait;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * SitemapLocator
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class SitemapLocator
{
    use BackendUserAuthenticationTrait;

    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var iterable<ProviderInterface>
     */
    protected $providers = [];

    /**
     * @param iterable<ProviderInterface> $providers
     */
    public function __construct(RequestFactory $requestFactory, CacheManager $cacheManager, iterable $providers)
    {
        $this->requestFactory = $requestFactory;
        $this->cacheManager = $cacheManager;
        $this->providers = $providers;

        $this->validateProviders();
    }

    /**
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function locateBySite(Site $site, SiteLanguage $siteLanguage = null): SiteAwareSitemap
    {
        // Get sitemap from cache
        if (\is_string($sitemapUrl = $this->cacheManager->get($site, $siteLanguage))) {
            return new SiteAwareSitemap(new Uri($sitemapUrl), $site, $siteLanguage);
        }

        // Build and validate base URL
        $baseUrl = $siteLanguage !== null ? $siteLanguage->getBase() : $site->getBase();
        if ($baseUrl->getHost() === '') {
            throw UnsupportedConfigurationException::forBaseUrl((string)$baseUrl);
        }

        // Resolve and validate sitemap
        $sitemap = $this->resolveSitemap($site, $siteLanguage);
        if ($sitemap === null) {
            throw UnsupportedSiteException::forMissingSitemap($site);
        }

        // Store resolved sitemap in cache
        $this->cacheManager->set($sitemap);

        return $sitemap;
    }

    /**
     * @return array<int, SiteAwareSitemap>
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function locateAllBySite(Site $site): array
    {
        $sitemaps = [];

        foreach ($site->getAvailableLanguages(static::getBackendUser()) as $siteLanguage) {
            if ($siteLanguage->isEnabled()) {
                $sitemaps[$siteLanguage->getLanguageId()] = $this->locateBySite($site, $siteLanguage);
            }
        }

        return $sitemaps;
    }

    public function siteContainsSitemap(Site $site, SiteLanguage $siteLanguage = null): bool
    {
        try {
            $sitemap = $this->locateBySite($site, $siteLanguage);
            $response = $this->requestFactory->request((string)$sitemap->getUri(), 'HEAD');

            return $response->getStatusCode() < 400;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function resolveSitemap(Site $site, SiteLanguage $siteLanguage = null): ?SiteAwareSitemap
    {
        foreach ($this->providers as $provider) {
            if (($sitemap = $provider->get($site, $siteLanguage)) !== null) {
                return $sitemap;
            }
        }

        return null;
    }

    protected function validateProviders(): void
    {
        foreach ($this->providers as $provider) {
            if (!\is_object($provider)) {
                throw new \InvalidArgumentException(
                    sprintf('Providers must be of type object, "%s" given.', \gettype($provider)),
                    1619525071
                );
            }
            if (!\in_array(ProviderInterface::class, class_implements($provider) ?: [])) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The given provider "%s" does not implement the interface "%s".',
                        \get_class($provider),
                        ProviderInterface::class
                    ),
                    1619524996
                );
            }
        }
    }
}
