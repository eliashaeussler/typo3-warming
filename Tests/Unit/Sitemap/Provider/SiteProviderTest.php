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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Sitemap\Provider;

use EliasHaeussler\Typo3Warming\Sitemap\Provider\SiteProvider;
use EliasHaeussler\Typo3Warming\Sitemap\SiteAwareSitemap;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * SiteProviderTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class SiteProviderTest extends UnitTestCase
{
    /**
     * @var SiteProvider
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new SiteProvider();
    }

    /**
     * @test
     */
    public function getReturnsNullIfSitemapPathIsNotConfiguredInSite(): void
    {
        $site = new Site('foo', 1, []);

        self::assertNull($this->subject->get($site));
    }

    /**
     * @test
     */
    public function getReturnsSitemapWithUrlPathFromSite(): void
    {
        $site = new Site('foo', 1, [
            'base' => 'https://www.example.com/',
            'xml_sitemap_path' => 'baz.xml',
        ]);
        $expected = new SiteAwareSitemap(new Uri('https://www.example.com/baz.xml'), $site);

        self::assertEquals($expected, $this->subject->get($site));
    }

    /**
     * @test
     */
    public function getReturnsSitemapWithUrlPathFromSiteLanguage(): void
    {
        $site = new Site('foo', 1, [
            'base' => 'https://www.example.com/',
        ]);
        $siteLanguage = new SiteLanguage(1, 'de_DE.UTF-8', new Uri('https://www.example.com/de'), [
            'xml_sitemap_path' => 'baz.xml',
        ]);
        $expected = new SiteAwareSitemap(new Uri('https://www.example.com/de/baz.xml'), $site, $siteLanguage);

        self::assertEquals($expected, $this->subject->get($site, $siteLanguage));
    }
}
