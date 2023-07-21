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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Result;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * CacheWarmupResultTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Result\CacheWarmupResult::class)]
final class CacheWarmupResultTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Tests\Unit\SiteTrait;

    protected CacheWarmup\Result\CacheWarmupResult $originalResult;
    protected Core\Site\Entity\Site $site;
    protected Src\Result\CacheWarmupResult $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalResult = new CacheWarmup\Result\CacheWarmupResult();
        $this->site = $this->createSite();
        $this->subject = new Src\Result\CacheWarmupResult(
            $this->originalResult,
            [
                new CacheWarmup\Sitemap\Sitemap(new Core\Http\Uri('https://typo3-testing.local/')),
            ],
            [
                new CacheWarmup\Sitemap\Url('https://typo3-testing.local/foo/'),
            ],
        );
    }

    #[Framework\Attributes\Test]
    public function getResultReturnsOriginalResult(): void
    {
        self::assertSame($this->originalResult, $this->subject->getResult());
    }

    #[Framework\Attributes\Test]
    public function getCrawlingResultsBySiteReturnsEmptyArrayIfNoCrawlingResultsExist(): void
    {
        self::assertSame(
            [
                'successful' => [],
                'failed' => [],
            ],
            $this->subject->getCrawlingResultsBySite($this->site, $this->site->getDefaultLanguage()),
        );
    }

    #[Framework\Attributes\Test]
    public function getCrawlingResultsBySiteReturnsEmptyArrayIfNoMatchingCrawlingResultsExist(): void
    {
        $this->originalResult
            ->addResult(
                CacheWarmup\Result\CrawlingResult::createSuccessful(
                    new CacheWarmup\Sitemap\Url('https://typo3-testing.local/'),
                ),
            )
            ->addResult(
                CacheWarmup\Result\CrawlingResult::createFailed(
                    new Core\Http\Uri('https://typo3-testing.local/'),
                ),
            )
        ;

        self::assertSame(
            [
                'successful' => [],
                'failed' => [],
            ],
            $this->subject->getCrawlingResultsBySite($this->site, $this->site->getDefaultLanguage()),
        );
    }

    #[Framework\Attributes\Test]
    public function getCrawlingResultsBySiteReturnsMatchingCrawlingResults(): void
    {
        $origin = new Src\Sitemap\SiteAwareSitemap(
            new Core\Http\Uri('https://typo3-testing.local/sitemap.xml'),
            $this->site,
            $this->site->getDefaultLanguage(),
        );

        $this->originalResult
            ->addResult(
                $result1 = CacheWarmup\Result\CrawlingResult::createSuccessful(
                    new CacheWarmup\Sitemap\Url('https://typo3-testing.local/', origin: $origin),
                ),
            )
            ->addResult(
                $result2 = CacheWarmup\Result\CrawlingResult::createSuccessful(
                    new CacheWarmup\Sitemap\Url('https://typo3-testing.local/foo/', origin: $origin),
                ),
            )
            ->addResult(
                CacheWarmup\Result\CrawlingResult::createFailed(
                    new CacheWarmup\Sitemap\Url('https://typo3-testing.local/baz/'),
                ),
            )
            ->addResult(
                $result3 = CacheWarmup\Result\CrawlingResult::createFailed(
                    new CacheWarmup\Sitemap\Url('https://typo3-testing.local/dummy/', origin: $origin),
                ),
            )
        ;

        self::assertSame(
            [
                'successful' => [$result1, $result2],
                'failed' => [$result3],
            ],
            $this->subject->getCrawlingResultsBySite($this->site, $this->site->getDefaultLanguage()),
        );
    }

    #[Framework\Attributes\Test]
    public function getExcludedSitemapsReturnsExcludedSitemaps(): void
    {
        self::assertEquals(
            [
                new CacheWarmup\Sitemap\Sitemap(new Core\Http\Uri('https://typo3-testing.local/')),
            ],
            $this->subject->getExcludedSitemaps(),
        );
    }

    #[Framework\Attributes\Test]
    public function getExcludedUrlsReturnsExcludedUrls(): void
    {
        self::assertEquals(
            [
                new CacheWarmup\Sitemap\Url('https://typo3-testing.local/foo/'),
            ],
            $this->subject->getExcludedUrls(),
        );
    }
}
