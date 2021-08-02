<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Sitemap;

use EliasHaeussler\Typo3Warming\Sitemap\SiteAwareSitemap;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * SiteAwareSitemapTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class SiteAwareSitemapTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getSiteReturnsSite(): void
    {
        $site = new Site('foo', 1, []);
        $subject = new SiteAwareSitemap(new Uri('https://www.example.com'), $site);

        self::assertSame($site, $subject->getSite());
    }

    /**
     * @test
     */
    public function getSiteLanguageReturnsSiteLanguage(): void
    {
        $site = new Site('foo', 1, []);
        $subject = new SiteAwareSitemap(new Uri('https://www.example.com'), $site, $site->getDefaultLanguage());

        self::assertSame($site->getDefaultLanguage(), $subject->getSiteLanguage());
    }

    /**
     * @test
     */
    public function setSiteLanguageSetsSiteLanguage(): void
    {
        $site = new Site('foo', 1, []);
        $subject = new SiteAwareSitemap(new Uri('https://www.example.com'), $site);

        self::assertNull($subject->getSiteLanguage());

        $subject->setSiteLanguage($site->getDefaultLanguage());

        self::assertSame($site->getDefaultLanguage(), $subject->getSiteLanguage());
    }
}
