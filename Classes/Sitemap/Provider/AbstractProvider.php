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

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * AbstractProvider
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
abstract class AbstractProvider implements ProviderInterface
{
    protected function getSiteUrlWithPath(Site $site, string $path, SiteLanguage $siteLanguage = null): UriInterface
    {
        $baseUrl = $siteLanguage !== null ? $siteLanguage->getBase() : $site->getBase();
        $fullPath = rtrim($baseUrl->getPath(), '/') . '/' . ltrim($path, '/');

        return $baseUrl->withPath($fullPath);
    }
}
