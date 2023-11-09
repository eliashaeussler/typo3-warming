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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3Warming\Domain\Model;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3SitemapLocator;
use Psr\Http\Message;
use TYPO3\CMS\Core;

/**
 * SiteAwareSitemap
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class SiteAwareSitemap extends CacheWarmup\Sitemap\Sitemap
{
    public function __construct(
        Message\UriInterface $uri,
        protected readonly Core\Site\Entity\Site $site,
        protected readonly Core\Site\Entity\SiteLanguage $siteLanguage,
        protected readonly bool $cached = false,
    ) {
        parent::__construct($uri);
    }

    /**
     * @throws CacheWarmup\Exception\InvalidUrlException
     */
    public static function fromLocatedSitemap(Typo3SitemapLocator\Domain\Model\Sitemap $sitemap): self
    {
        return new self(
            $sitemap->getUri(),
            $sitemap->getSite(),
            $sitemap->getSiteLanguage(),
            $sitemap->isCached(),
        );
    }

    public function getSite(): Core\Site\Entity\Site
    {
        return $this->site;
    }

    public function getSiteLanguage(): Core\Site\Entity\SiteLanguage
    {
        return $this->siteLanguage;
    }

    public function isCached(): bool
    {
        return $this->cached;
    }
}
