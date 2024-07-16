<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2024 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\CacheWarmup\Sitemap;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * SiteAwareSitemap
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class SiteAwareSitemap extends Sitemap
{
    protected Site $site;
    protected ?SiteLanguage $siteLanguage;

    public function __construct(UriInterface $uri, Site $site, SiteLanguage $siteLanguage = null)
    {
        parent::__construct($uri);
        $this->site = $site;
        $this->siteLanguage = $siteLanguage;
    }

    public function getSite(): Site
    {
        return $this->site;
    }

    public function getSiteLanguage(): ?SiteLanguage
    {
        return $this->siteLanguage;
    }

    public function setSiteLanguage(SiteLanguage $siteLanguage): self
    {
        $this->siteLanguage = $siteLanguage;
        return $this;
    }
}
