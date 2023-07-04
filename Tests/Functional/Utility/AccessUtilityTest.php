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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
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
 * AccessUtilityTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Utility\AccessUtility::class)]
final class AccessUtilityTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\Functional\SiteTrait;

    protected Core\Site\Entity\Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_groups.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/pages.csv');

        $this->site = $this->createSite();
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfPageReturnsTrueForAdmins(): void
    {
        $this->setUpBackendUser(3);

        self::assertTrue(Src\Utility\AccessUtility::canWarmupCacheOfPage(1));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfPageReturnsTrueForUsersWithPagePermissions(): void
    {
        $this->setUpBackendUser(1);

        self::assertTrue(Src\Utility\AccessUtility::canWarmupCacheOfPage(1));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfPageReturnsFalseForUsersWithoutPagePermissions(): void
    {
        $this->setUpBackendUser(1);

        self::assertFalse(Src\Utility\AccessUtility::canWarmupCacheOfPage(2));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfPageReturnsFalseForUsersWithoutAnyPermissions(): void
    {
        $this->setUpBackendUser(2);

        self::assertFalse(Src\Utility\AccessUtility::canWarmupCacheOfPage(1));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfPageReturnsTrueForUsersWithRecursivePagePermissions(): void
    {
        $this->setUpBackendUser(1);

        self::assertTrue(Src\Utility\AccessUtility::canWarmupCacheOfPage(4));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfPageReturnsFalseForPageWithoutTranslation(): void
    {
        $this->setUpBackendUser(3);

        self::assertFalse(Src\Utility\AccessUtility::canWarmupCacheOfPage(3, 1));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfPageReturnsFalseForUsersWithoutLanguagePermissions(): void
    {
        $this->setUpBackendUser(1);

        self::assertFalse(Src\Utility\AccessUtility::canWarmupCacheOfPage(1, 1));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfSiteReturnsTrueForAdmins(): void
    {
        $this->setUpBackendUser(3);

        self::assertTrue(Src\Utility\AccessUtility::canWarmupCacheOfSite($this->site));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfSiteReturnsTrueForUsersWithSitePermissions(): void
    {
        $this->setUpBackendUser(2);

        self::assertTrue(Src\Utility\AccessUtility::canWarmupCacheOfSite($this->site));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfSiteReturnsFalseForUsersWithoutSitePermissions(): void
    {
        $this->setUpBackendUser(1);

        self::assertFalse(Src\Utility\AccessUtility::canWarmupCacheOfSite($this->site));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfSiteWithGivenLanguageReturnsTrueForAdmins(): void
    {
        $this->setUpBackendUser(3);

        self::assertTrue(Src\Utility\AccessUtility::canWarmupCacheOfSite($this->site, 1));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfSiteReturnsFalseForUsersWithoutLanguagePermissions(): void
    {
        $this->setUpBackendUser(2);

        self::assertFalse(Src\Utility\AccessUtility::canWarmupCacheOfSite($this->site, 2));
    }
}
