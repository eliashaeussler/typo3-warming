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

namespace EliasHaeussler\Typo3CacheWarmup\Utility;

use EliasHaeussler\Typo3CacheWarmup\Exception\UnsupportedConfigurationException;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * SitemapUtility
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class SitemapUtility
{
    public const DEFAULT_XML_SITEMAP_PATH = 'sitemap.xml';

    /**
     * @param Site $site
     * @return UriInterface
     * @throws UnsupportedConfigurationException
     */
    public static function buildSitemapUrl(Site $site): UriInterface
    {
        $baseUrl = $site->getBase();

        if ($baseUrl->getHost() === '') {
            throw UnsupportedConfigurationException::forBaseUrl((string)$baseUrl);
        }

        $sitemapPath = $site->getConfiguration()['xml_sitemap_path'] ?: self::DEFAULT_XML_SITEMAP_PATH;
        $urlPath = rtrim($baseUrl->getPath(), '/') . '/' . ltrim($sitemapPath, '/');

        return $baseUrl->withPath($urlPath);
    }

    public static function siteProvidesValidSitemap(Site $site): bool
    {
        try {
            $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
            $possibleSitemapUrl = static::buildSitemapUrl($site);

            return (bool)$requestFactory->request((string)$possibleSitemapUrl, 'HEAD');
        } catch (\Exception $e) {
            return false;
        }
    }
}
