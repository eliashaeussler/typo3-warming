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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Cache;

use EliasHaeussler\Typo3Warming\Cache\CacheManager;
use EliasHaeussler\Typo3Warming\Sitemap\SiteAwareSitemap;
use TYPO3\CMS\Core\Cache\CacheManager as CoreCacheManager;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * CacheManagerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class CacheManagerTest extends FunctionalTestCase
{
    /**
     * @var PhpFrontend
     */
    protected $cache;

    /**
     * @var CacheManager
     */
    protected $subject;

    /**
     * @var Site
     */
    protected $site;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var PhpFrontend $coreCache */
        $coreCache = GeneralUtility::makeInstance(CoreCacheManager::class)->getCache('core');
        $this->cache = $coreCache;
        $this->subject = new CacheManager($this->cache);
        $this->site = new Site('foo', 1, []);
    }

    /**
     * @test
     */
    public function getReturnsEmptyArrayIfNoSitemapsAreCached(): void
    {
        $this->cache->remove(CacheManager::CACHE_IDENTIFIER);

        self::assertSame([], $this->subject->get());
    }

    /**
     * @test
     */
    public function getReturnsAllCachedSitemaps(): void
    {
        $this->cache->set(
            CacheManager::CACHE_IDENTIFIER,
            sprintf('return %s;', var_export(['sitemaps' => ['foo' => 'baz']], true))
        );

        self::assertSame(['foo' => 'baz'], $this->subject->get());
    }

    /**
     * @test
     */
    public function getReturnsNullIfGivenSiteIsNotCached(): void
    {
        $this->cache->set(
            CacheManager::CACHE_IDENTIFIER,
            sprintf('return %s;', var_export(['sitemaps' => ['baz' => 'foo']], true))
        );

        self::assertNull($this->subject->get($this->site));
    }

    /**
     * @test
     */
    public function getReturnsCachedSitemapForDefaultLanguage(): void
    {
        $this->cache->set(
            CacheManager::CACHE_IDENTIFIER,
            sprintf('return %s;', var_export(['sitemaps' => ['foo' => ['default' => 'baz']]], true))
        );

        self::assertSame('baz', $this->subject->get($this->site));
    }

    /**
     * @test
     */
    public function getReturnsCachedSitemapForGivenLanguage(): void
    {
        $this->cache->set(
            CacheManager::CACHE_IDENTIFIER,
            sprintf('return %s;', var_export(['sitemaps' => ['foo' => ['1' => 'baz']]], true))
        );

        $site = new Site('foo', 1, []);
        $siteLanguage = new SiteLanguage(1, 'de_DE.UTF-8', new Uri('https://example.com'), []);

        self::assertSame('baz', $this->subject->get($site, $siteLanguage));
    }

    /**
     * @test
     */
    public function setStoresGivenSitemapForDefaultLanguageInCache(): void
    {
        $this->subject->set(new SiteAwareSitemap(new Uri('https://www.example.com/sitemap.xml'), $this->site));

        self::assertSame(
            ['sitemaps' => ['foo' => ['default' => 'https://www.example.com/sitemap.xml']]],
            $this->cache->require(CacheManager::CACHE_IDENTIFIER)
        );
    }

    /**
     * @test
     */
    public function setStoresGivenSitemapForGivenLanguageInCache(): void
    {
        $siteLanguage = new SiteLanguage(1, 'de_DE.UTF-8', new Uri('https://example.com'), []);

        $this->subject->set(new SiteAwareSitemap(new Uri('https://www.example.com/sitemap.xml'), $this->site, $siteLanguage));

        self::assertSame(
            ['sitemaps' => ['foo' => ['1' => 'https://www.example.com/sitemap.xml']]],
            $this->cache->require(CacheManager::CACHE_IDENTIFIER)
        );
    }

    protected function tearDown(): void
    {
        $this->cache->flush();

        parent::tearDown();
    }
}
