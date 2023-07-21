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
use TYPO3\CMS\Core;

/**
 * DefaultProvider
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class DefaultProvider implements Provider
{
    public const DEFAULT_PATH = 'sitemap.xml';

    public function get(
        Core\Site\Entity\Site $site,
        Core\Site\Entity\SiteLanguage $siteLanguage = null,
    ): array {
        $sitemap = new Sitemap\SiteAwareSitemap(
            Utility\HttpUtility::getSiteUrlWithPath($site, self::DEFAULT_PATH, $siteLanguage),
            $site,
            $siteLanguage ?? $site->getDefaultLanguage(),
        );

        return [$sitemap];
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getPriority(): int
    {
        return PHP_INT_MIN;
    }
}
