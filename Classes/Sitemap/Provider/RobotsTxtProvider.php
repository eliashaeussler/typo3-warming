<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\Typo3Warming\Sitemap;
use EliasHaeussler\Typo3Warming\Utility;
use Exception;
use Psr\Http\Message;
use TYPO3\CMS\Core;

/**
 * RobotsTxtProvider
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class RobotsTxtProvider implements Provider
{
    private const SITEMAP_PATTERN = '#^Sitemap:\s*(?P<url>https?://[^\r\n]+)#im';

    public function __construct(
        private readonly Core\Http\RequestFactory $requestFactory,
    ) {
    }

    public function get(
        Core\Site\Entity\Site $site,
        Core\Site\Entity\SiteLanguage $siteLanguage = null,
    ): array {
        $robotsTxtUri = Utility\HttpUtility::getSiteUrlWithPath($site, 'robots.txt', $siteLanguage);
        $robotsTxt = $this->fetchRobotsTxt($robotsTxtUri);

        // Early return if no robots.txt exists
        if ($robotsTxt === null || trim($robotsTxt) === '') {
            return [];
        }

        // Early return if no sitemap is specified in robots.txt
        if ((int)preg_match_all(self::SITEMAP_PATTERN, $robotsTxt, $matches) === 0) {
            return [];
        }

        return array_values(
            array_map(
                static fn (string $url) => new Sitemap\SiteAwareSitemap(
                    new Core\Http\Uri($url),
                    $site,
                    $siteLanguage ?? $site->getDefaultLanguage(),
                ),
                $matches['url'],
            ),
        );
    }

    private function fetchRobotsTxt(Message\UriInterface $uri): ?string
    {
        try {
            $response = $this->requestFactory->request((string)$uri);

            return $response->getBody()->getContents();
        } catch (Exception) {
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
