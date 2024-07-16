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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Sitemap\Provider;

use EliasHaeussler\Typo3Warming\Sitemap\Provider\DefaultProvider;
use EliasHaeussler\Typo3Warming\Sitemap\SiteAwareSitemap;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * DefaultProviderTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class DefaultProviderTest extends UnitTestCase
{
    protected DefaultProvider $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new DefaultProvider();
    }

    /**
     * @test
     */
    public function getReturnsSitemapWithDefaultPath(): void
    {
        $site = new Site('foo', 1, ['base' => 'https://www.example.com/']);
        $expected = new SiteAwareSitemap(new Uri('https://www.example.com/sitemap.xml'), $site);

        self::assertEquals($expected, $this->subject->get($site));
    }
}
