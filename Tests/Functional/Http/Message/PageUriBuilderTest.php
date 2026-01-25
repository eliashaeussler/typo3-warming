<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2026 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Http\Message;

use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * PageUriBuilderTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Message\PageUriBuilder::class)]
final class PageUriBuilderTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\Functional\SiteTrait;

    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'typed_extconf',
        'warming',
    ];

    private Src\Http\Message\PageUriBuilder $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/Database/be_groups.csv');
        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/Database/pages.csv');

        $this->createSite();
        $this->setUpBackendUser(3);

        $this->subject = $this->get(Src\Http\Message\PageUriBuilder::class);
    }

    #[Framework\Attributes\Test]
    public function buildReturnsNullIfPageDoesNotExist(): void
    {
        self::assertNull($this->subject->build(99));
    }

    #[Framework\Attributes\Test]
    public function buildReturnsNullIfRelatedSiteIsNotAccessible(): void
    {
        $this->setUpBackendUser(2);

        self::assertNull($this->subject->build(2));
    }

    #[Framework\Attributes\Test]
    public function buildReturnsUriForGivenTranslatedPage(): void
    {
        self::assertEquals(
            new Core\Http\Uri('https://typo3-testing.local/subsite-1'),
            $this->subject->build(6),
        );
    }

    #[Framework\Attributes\Test]
    public function buildReturnsUriForGivenPage(): void
    {
        self::assertEquals(
            new Core\Http\Uri('https://typo3-testing.local/subsite-1'),
            $this->subject->build(2),
        );
    }

    #[Framework\Attributes\Test]
    public function buildReturnsNullIfPageIsNotAvailableWithinGivenLanguage(): void
    {
        self::assertNull($this->subject->build(3, 1));
    }

    #[Framework\Attributes\Test]
    public function buildReturnsNullIfGivenSiteLanguageIsNotAccessible(): void
    {
        $this->setUpBackendUser(1);

        self::assertNull($this->subject->build(1, 1));
    }

    #[Framework\Attributes\Test]
    public function buildReturnsUriForGivenPageAndGivenLanguage(): void
    {
        self::assertEquals(
            new Core\Http\Uri('https://typo3-testing.local/de/subsite-1-l-1'),
            $this->subject->build(2, 1),
        );
    }
}
