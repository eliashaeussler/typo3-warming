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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Security;

use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * WarmupPermissionGuardTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Security\WarmupPermissionGuard::class)]
final class WarmupPermissionGuardTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\Functional\SiteTrait;

    private Core\Site\Entity\Site $site;
    private Core\Cache\Frontend\FrontendInterface $cache;
    private Src\Security\WarmupPermissionGuard $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_groups.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/pages.csv');

        $this->site = $this->createSite();
        $this->cache = $this->get('cache.runtime');
        $this->subject = new Src\Security\WarmupPermissionGuard($this->cache);
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfPageCachesPermissionResult(): void
    {
        $pageId = 1;
        $context = new Src\Security\Context\PermissionContext();
        $identifier = 'warming_warmupPermissionGuard_' . sha1(serialize(['canWarmupCacheOfPage', $pageId, $context]));

        self::assertFalse($this->cache->has($identifier));
        self::assertTrue($this->subject->canWarmupCacheOfPage($pageId, $context));
        self::assertTrue($this->cache->has($identifier));
        self::assertTrue($this->cache->get($identifier));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfPageUsesCachedResult(): void
    {
        $pageId = 1;
        $context = new Src\Security\Context\PermissionContext();
        $identifier = 'warming_warmupPermissionGuard_' . sha1(serialize(['canWarmupCacheOfPage', $pageId, $context]));

        $this->cache->set($identifier, false);

        self::assertFalse($this->subject->canWarmupCacheOfPage($pageId, $context));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfPageReturnsTrueForAdmins(): void
    {
        $backendUser = $this->setUpBackendUser(3);
        $context = new Src\Security\Context\PermissionContext(backendUser: $backendUser);

        self::assertTrue($this->subject->canWarmupCacheOfPage(1, $context));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfPageReturnsTrueForUsersWithPagePermissions(): void
    {
        $backendUser = $this->setUpBackendUser(1);
        $context = new Src\Security\Context\PermissionContext(backendUser: $backendUser);

        self::assertTrue($this->subject->canWarmupCacheOfPage(1, $context));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfPageReturnsFalseForUsersWithoutPagePermissions(): void
    {
        $backendUser = $this->setUpBackendUser(1);
        $context = new Src\Security\Context\PermissionContext(backendUser: $backendUser);

        self::assertFalse($this->subject->canWarmupCacheOfPage(2, $context));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfPageReturnsFalseForUsersWithoutAnyPermissions(): void
    {
        $backendUser = $this->setUpBackendUser(2);
        $context = new Src\Security\Context\PermissionContext(backendUser: $backendUser);

        self::assertFalse($this->subject->canWarmupCacheOfPage(1, $context));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfPageReturnsTrueForUsersWithRecursivePagePermissions(): void
    {
        $backendUser = $this->setUpBackendUser(1);
        $context = new Src\Security\Context\PermissionContext(backendUser: $backendUser);

        self::assertTrue($this->subject->canWarmupCacheOfPage(4, $context));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfPageReturnsFalseForPageWithoutTranslation(): void
    {
        $backendUser = $this->setUpBackendUser(3);
        $context = new Src\Security\Context\PermissionContext(1, $backendUser);

        self::assertFalse($this->subject->canWarmupCacheOfPage(3, $context));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfPageReturnsFalseForUsersWithoutLanguagePermissions(): void
    {
        $backendUser = $this->setUpBackendUser(1);
        $context = new Src\Security\Context\PermissionContext(1, $backendUser);

        self::assertFalse($this->subject->canWarmupCacheOfPage(1, $context));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfPageReturnsTrueIfUserIsOmittedInContext(): void
    {
        self::assertTrue($this->subject->canWarmupCacheOfPage(2));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfSiteCachesPermissionResult(): void
    {
        $context = new Src\Security\Context\PermissionContext();
        $identifier = 'warming_warmupPermissionGuard_' . sha1(serialize(['canWarmupCacheOfSite', $this->site, $context]));

        self::assertFalse($this->cache->has($identifier));
        self::assertTrue($this->subject->canWarmupCacheOfSite($this->site, $context));
        self::assertTrue($this->cache->has($identifier));
        self::assertTrue($this->cache->get($identifier));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfSiteUsesCachedResult(): void
    {
        $context = new Src\Security\Context\PermissionContext();
        $identifier = 'warming_warmupPermissionGuard_' . sha1(serialize(['canWarmupCacheOfSite', $this->site, $context]));

        $this->cache->set($identifier, false);

        self::assertFalse($this->subject->canWarmupCacheOfSite($this->site, $context));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfSiteReturnsTrueForAdmins(): void
    {
        $backendUser = $this->setUpBackendUser(3);
        $context = new Src\Security\Context\PermissionContext(backendUser: $backendUser);

        self::assertTrue($this->subject->canWarmupCacheOfSite($this->site, $context));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfSiteReturnsTrueForUsersWithSitePermissions(): void
    {
        $backendUser = $this->setUpBackendUser(2);
        $context = new Src\Security\Context\PermissionContext(backendUser: $backendUser);

        self::assertTrue($this->subject->canWarmupCacheOfSite($this->site, $context));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfSiteReturnsFalseForUsersWithoutSitePermissions(): void
    {
        $backendUser = $this->setUpBackendUser(1);
        $context = new Src\Security\Context\PermissionContext(backendUser: $backendUser);

        self::assertFalse($this->subject->canWarmupCacheOfSite($this->site, $context));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfSiteWithGivenLanguageReturnsTrueForAdmins(): void
    {
        $backendUser = $this->setUpBackendUser(3);
        $context = new Src\Security\Context\PermissionContext(1, $backendUser);

        self::assertTrue($this->subject->canWarmupCacheOfSite($this->site, $context));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfSiteReturnsFalseForUsersWithoutLanguagePermissions(): void
    {
        $backendUser = $this->setUpBackendUser(2);
        $context = new Src\Security\Context\PermissionContext(2, $backendUser);

        self::assertFalse($this->subject->canWarmupCacheOfSite($this->site, $context));
    }

    #[Framework\Attributes\Test]
    public function canWarmupCacheOfSiteReturnsTrueIfUserIsOmittedInContext(): void
    {
        self::assertTrue($this->subject->canWarmupCacheOfSite($this->site));
    }
}
