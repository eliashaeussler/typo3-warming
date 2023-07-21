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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Utility;

use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * HttpUtilityTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Utility\HttpUtility::class)]
final class HttpUtilityTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\Functional\SiteTrait;

    protected Core\Site\Entity\Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/pages.csv');

        $this->site = $this->createSite();
    }

    #[Framework\Attributes\Test]
    public function getSiteUrlWithPathReturnsSiteBaseUrlWithMergedPath(): void
    {
        $site = $this->createSite('https://typo3-testing.local/foo/');

        self::assertEquals(
            new Core\Http\Uri('https://typo3-testing.local/foo/baz/'),
            Src\Utility\HttpUtility::getSiteUrlWithPath($site, '/baz/'),
        );
    }

    #[Framework\Attributes\Test]
    public function getSiteUrlWithPathRespectsQueryString(): void
    {
        self::assertEquals(
            new Core\Http\Uri('https://typo3-testing.local/foo/?foo=baz'),
            Src\Utility\HttpUtility::getSiteUrlWithPath($this->site, '/foo/?foo=baz'),
        );
    }

    #[Framework\Attributes\Test]
    public function getSiteUrlWithPathRespectsSiteLanguage(): void
    {
        self::assertEquals(
            new Core\Http\Uri('https://typo3-testing.local/de/foo/'),
            Src\Utility\HttpUtility::getSiteUrlWithPath(
                $this->site,
                '/foo/',
                $this->site->getLanguageById(1),
            ),
        );
    }

    #[Framework\Attributes\Test]
    public function generateUriReturnsNullIfPageDoesNotExist(): void
    {
        self::assertNull(Src\Utility\HttpUtility::generateUri(99));
    }

    #[Framework\Attributes\Test]
    public function generateUriReturnsUriForGivenPage(): void
    {
        self::assertEquals(
            new Core\Http\Uri('https://typo3-testing.local/subsite-1'),
            Src\Utility\HttpUtility::generateUri(2),
        );
    }

    #[Framework\Attributes\Test]
    public function generateUriReturnsNullIfPageIsNotAvailableWithinGivenLanguage(): void
    {
        self::assertNull(Src\Utility\HttpUtility::generateUri(3, 1));
    }

    #[Framework\Attributes\Test]
    public function generateUriReturnsUriForGivenPageAndGivenLanguage(): void
    {
        self::assertEquals(
            new Core\Http\Uri('https://typo3-testing.local/de/subsite-1-l-1'),
            Src\Utility\HttpUtility::generateUri(2, 1),
        );
    }
}
