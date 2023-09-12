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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Sitemap\Provider;

use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * DefaultProviderTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Sitemap\Provider\DefaultProvider::class)]
final class DefaultProviderTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Sitemap\Provider\DefaultProvider $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new Src\Sitemap\Provider\DefaultProvider();
    }

    #[Framework\Attributes\Test]
    public function getReturnsSitemapWithDefaultPath(): void
    {
        $site = new Core\Site\Entity\Site('foo', 1, ['base' => 'https://www.example.com/']);
        $expected = [
            new Src\Sitemap\SiteAwareSitemap(
                new Core\Http\Uri('https://www.example.com/sitemap.xml'),
                $site,
                $site->getDefaultLanguage(),
            ),
        ];

        self::assertEquals($expected, $this->subject->get($site));
    }
}
