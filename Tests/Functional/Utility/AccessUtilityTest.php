<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\Typo3Warming\Utility\AccessUtility;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * AccessUtilityTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class AccessUtilityTest extends FunctionalTestCase
{
    protected $backendUserFixture = __DIR__ . '/../Fixtures/be_users.xml';

    /**
     * @var Site
     */
    protected $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = new Site('main', 1, []);

        $this->importDataSet(__DIR__ . '/../Fixtures/be_groups.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/pages.xml');
    }

    /**
     * @test
     */
    public function canWarmupCacheOfPageReturnsTrueIfBackendUserIsAdmin(): void
    {
        $this->setUpBackendUserFromFixture(1);

        self::assertTrue(AccessUtility::canWarmupCacheOfPage(1));
    }

    /**
     * @test
     */
    public function canWarmupCacheOfPageReturnsFalseIfBackendUserHasNoPagePermissions(): void
    {
        $this->setUpBackendUserFromFixture(2);

        self::assertFalse(AccessUtility::canWarmupCacheOfPage(3));
    }

    /**
     * @test
     */
    public function canWarmupCacheOfPageReturnsFalseIfBackendUserHasNoPageAccess(): void
    {
        $this->setUpBackendUserFromFixture(2);

        self::assertFalse(AccessUtility::canWarmupCacheOfPage(2));
    }

    /**
     * @test
     */
    public function canWarmupCacheOfPageReturnsTrueIfBackendUserHasPageAccessViaUserTSconfig(): void
    {
        $this->setUpBackendUserFromFixture(3);

        self::assertTrue(AccessUtility::canWarmupCacheOfPage(2));
        self::assertFalse(AccessUtility::canWarmupCacheOfPage(1));
    }

    /**
     * @test
     */
    public function canWarmupCacheOfSiteReturnsTrueIfBackendUserIsAdmin(): void
    {
        $this->setUpBackendUserFromFixture(1);

        self::assertTrue(AccessUtility::canWarmupCacheOfSite($this->site));
    }

    /**
     * @test
     */
    public function canWarmupCacheOfSiteReturnsFalseIfBackendUserHasNoPagePermissions(): void
    {
        $this->setUpBackendUserFromFixture(2);

        self::assertFalse(AccessUtility::canWarmupCacheOfSite($this->site));
    }

    /**
     * @test
     */
    public function canWarmupCacheOfSiteReturnsFalseIfBackendUserHasNoPageAccess(): void
    {
        $this->setUpBackendUserFromFixture(2);

        self::assertFalse(AccessUtility::canWarmupCacheOfSite($this->site));
    }

    /**
     * @test
     */
    public function canWarmupCacheOfSiteReturnsTrueIfBackendUserHasPageAccessViaUserTSconfig(): void
    {
        $this->setUpBackendUserFromFixture(3);

        self::assertTrue(AccessUtility::canWarmupCacheOfSite($this->site));
    }
}
