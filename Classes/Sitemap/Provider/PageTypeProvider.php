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
use TYPO3\CMS\Core;

/**
 * PageTypeProvider
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class PageTypeProvider implements Provider
{
    /**
     * @see https://github.com/TYPO3/typo3/blob/v12.4.0/typo3/sysext/seo/Configuration/TypoScript/XmlSitemap/setup.typoscript#L3
     */
    private const EXPECTED_PAGE_TYPE = 1533906435;

    public function get(
        Core\Site\Entity\Site $site,
        Core\Site\Entity\SiteLanguage $siteLanguage = null,
    ): array {
        // Early return if EXT:seo is not installed
        if (!Core\Utility\ExtensionManagementUtility::isLoaded('seo')) {
            return [];
        }

        $pageTypeMap = $site->getConfiguration()['routeEnhancers']['PageTypeSuffix']['map'] ?? null;

        // Early return if no page type map is configured
        if (!\is_array($pageTypeMap) || !\in_array(self::EXPECTED_PAGE_TYPE, $pageTypeMap, true)) {
            return [];
        }

        $uri = $site->getRouter()->generateUri('/', [
            'type' => self::EXPECTED_PAGE_TYPE,
            '_language' => $siteLanguage,
        ]);
        $sitemap = new Sitemap\SiteAwareSitemap($uri, $site, $siteLanguage ?? $site->getDefaultLanguage());

        return [$sitemap];
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getPriority(): int
    {
        return 300;
    }
}
