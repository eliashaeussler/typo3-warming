<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2025 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Domain\Model;

use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * SiteAwareSitemapTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Domain\Model\SiteAwareSitemap::class)]
final class SiteAwareSitemapTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Core\Site\Entity\Site $site;
    private Src\Domain\Model\SiteAwareSitemap $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = new Core\Site\Entity\Site('foo', 1, []);
        $this->subject = new Src\Domain\Model\SiteAwareSitemap(
            new Core\Http\Uri('https://www.example.com'),
            $this->site,
            $this->site->getDefaultLanguage(),
            true,
        );
    }

    #[Framework\Attributes\Test]
    public function fromLocatedSitemapReturnsSitemapFromLocatedSitemap(): void
    {
        $sitemap = new Typo3SitemapLocator\Domain\Model\Sitemap(
            new Core\Http\Uri('https://www.example.com'),
            $this->site,
            $this->site->getDefaultLanguage(),
            true,
        );

        self::assertEquals($this->subject, Src\Domain\Model\SiteAwareSitemap::fromLocatedSitemap($sitemap));
    }

    #[Framework\Attributes\Test]
    public function getSiteReturnsSite(): void
    {
        self::assertSame($this->site, $this->subject->getSite());
    }

    #[Framework\Attributes\Test]
    public function getSiteLanguageReturnsSiteLanguage(): void
    {
        self::assertSame($this->site->getDefaultLanguage(), $this->subject->getSiteLanguage());
    }

    #[Framework\Attributes\Test]
    public function isCachedReturnsTrueIfSitemapIsCached(): void
    {
        self::assertTrue($this->subject->isCached());
    }
}
