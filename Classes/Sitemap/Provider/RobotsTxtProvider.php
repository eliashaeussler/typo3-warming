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

namespace EliasHaeussler\Typo3Warming\Sitemap\Provider;

use EliasHaeussler\Typo3Warming\Sitemap\SiteAwareSitemap;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * RobotsTxtProvider
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class RobotsTxtProvider extends AbstractProvider
{
    private const SITEMAP_PATTERN = '#^Sitemap:\s*(?P<url>https?://[^\r\n]+)#im';

    private RequestFactory $requestFactory;

    public function __construct(RequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    public function get(Site $site, SiteLanguage $siteLanguage = null): ?SiteAwareSitemap
    {
        $robotsTxt = $this->fetchRobotsTxt($this->getSiteUrlWithPath($site, 'robots.txt', $siteLanguage));

        // Early return if no robots.txt exists
        if (empty($robotsTxt)) {
            return null;
        }

        // Early return if no sitemap is specified in robots.txt
        if (!preg_match(self::SITEMAP_PATTERN, $robotsTxt, $matches)) {
            return null;
        }

        $uri = new Uri($matches['url']);

        return new SiteAwareSitemap($uri, $site, $siteLanguage);
    }

    private function fetchRobotsTxt(UriInterface $uri): ?string
    {
        try {
            $response = $this->requestFactory->request((string)$uri);

            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getPriority(): int
    {
        return 100;
    }
}
