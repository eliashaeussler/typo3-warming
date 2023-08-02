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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Sitemap\Provider;

use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * PageTypeProviderTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Sitemap\Provider\PageTypeProvider::class)]
final class PageTypeProviderTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected Src\Sitemap\Provider\PageTypeProvider $subject;
    protected Tests\Functional\Fixtures\Classes\DummyPackageManager $packageManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Sitemap\Provider\PageTypeProvider();
        $this->packageManager = new Tests\Functional\Fixtures\Classes\DummyPackageManager();

        Core\Utility\ExtensionManagementUtility::setPackageManager($this->packageManager);
    }

    #[Framework\Attributes\Test]
    public function getReturnsEmptyArrayIfSeoExtensionIsNotLoaded(): void
    {
        $site = new Core\Site\Entity\Site('foo', 1, []);

        self::assertSame([], $this->subject->get($site));
    }

    #[Framework\Attributes\Test]
    public function getReturnsEmptyArrayIfNoRouteEnhancersAreConfigured(): void
    {
        $this->packageManager->loadedExtensions = ['seo'];

        $site = new Core\Site\Entity\Site('foo', 1, []);

        self::assertSame([], $this->subject->get($site));
    }

    #[Framework\Attributes\Test]
    public function getReturnsEmptyArrayIfPageTypeIsNotConfigured(): void
    {
        $this->packageManager->loadedExtensions = ['seo'];

        $site = new Core\Site\Entity\Site('foo', 1, [
            'base' => 'https://www.example.com/',
            'routeEnhancers' => [
                'Foo' => [
                    'type' => 'Simple',
                    'routePath' => '/foo/{foo_id}',
                    '_arguments' => [
                        'foo_id' => 'foo/id',
                    ],
                ],
            ],
        ]);

        self::assertSame([], $this->subject->get($site));
    }

    #[Framework\Attributes\Test]
    public function getReturnsSitemapWithPageTypeFromSite(): void
    {
        $this->packageManager->loadedExtensions = ['seo'];

        $site = new Core\Site\Entity\Site('foo', 1, [
            'base' => 'https://www.example.com/',
            'routeEnhancers' => [
                'PageTypeSuffix' => [
                    'type' => 'PageType',
                    'map' => [
                        'baz.xml' => 1533906435,
                    ],
                ],
            ],
        ]);

        $expected = [
            new Src\Sitemap\SiteAwareSitemap(
                new Core\Http\Uri('https://www.example.com/baz.xml'),
                $site,
                $site->getDefaultLanguage(),
            ),
        ];

        self::assertEquals($expected, $this->subject->get($site));
    }

    #[Framework\Attributes\Test]
    public function getReturnsSitemapWithPageTypeAndSiteLanguage(): void
    {
        $this->packageManager->loadedExtensions = ['seo'];

        $site = new Core\Site\Entity\Site('foo', 1, [
            'base' => 'https://www.example.com/',
            'routeEnhancers' => [
                'PageTypeSuffix' => [
                    'type' => 'PageType',
                    'map' => [
                        'baz.xml' => 1533906435,
                    ],
                ],
            ],
        ]);
        $siteLanguage = new Core\Site\Entity\SiteLanguage(1, 'de_DE.UTF-8', new Core\Http\Uri('https://www.example.com/de/'), []);

        $expected = [
            new Src\Sitemap\SiteAwareSitemap(
                new Core\Http\Uri('https://www.example.com/de/baz.xml'),
                $site,
                $siteLanguage,
            ),
        ];

        self::assertEquals($expected, $this->subject->get($site, $siteLanguage));
    }
}
