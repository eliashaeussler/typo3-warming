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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Domain\Repository;

use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * SiteLanguageRepositoryTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Domain\Repository\SiteLanguageRepository::class)]
final class SiteLanguageRepositoryTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\Functional\SiteTrait;

    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'warming',
    ];

    private Core\Site\Entity\Site $site;
    private Src\Domain\Repository\SiteLanguageRepository $subject;
    private Core\Site\SiteFinder $siteFinder;

    public function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/Database/be_groups.csv');
        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/Database/pages.csv');

        $this->site = $this->createSite();
        $this->setUpBackendUser(2);

        $this->subject = $this->get(Src\Domain\Repository\SiteLanguageRepository::class);
        $this->siteFinder = $this->get(Core\Site\SiteFinder::class);
    }

    #[Framework\Attributes\Test]
    public function findAllReturnsEmptyArrayIfGivenSiteDoesNotExist(): void
    {
        $site = new Core\Site\Entity\Site('foo', 1, []);

        self::assertSame([], $this->subject->findAll($site));
    }

    #[Framework\Attributes\Test]
    public function findAllReturnsEmptyArrayIfGivenSiteIsExcludedFromWarming(): void
    {
        $this->site = $this->createSite(languagesToExcludeFromWarming: [0]);
        $this->siteFinder->getAllSites(false);

        self::assertSame([], $this->subject->findAll($this->site));
    }

    #[Framework\Attributes\Test]
    public function findAllReturnsAllSiteLanguagesAvailableToGivenBackendUser(): void
    {
        $expected = [
            1 => $this->site->getLanguageById(1),
        ];

        self::assertSame($expected, $this->subject->findAll($this->site));
    }

    #[Framework\Attributes\Test]
    public function findAllIgnoresSiteLanguagesWhichAreExcludedFromWarming(): void
    {
        $this->setUpBackendUser(3);

        $this->site = $this->createSite(languagesToExcludeFromWarming: [1]);
        $this->siteFinder->getAllSites(false);

        $expected = [
            0 => $this->site->getLanguageById(0),
            2 => $this->site->getLanguageById(2),
        ];

        self::assertSame($expected, $this->subject->findAll($this->site));
    }

    #[Framework\Attributes\Test]
    public function findAllReturnsAllSiteLanguages(): void
    {
        $expected = [
            1 => $this->site->getLanguageById(1),
        ];

        self::assertSame($expected, $this->subject->findAll($this->site));
    }

    #[Framework\Attributes\Test]
    public function countAllReturnsNumberOfAllSiteLanguages(): void
    {
        self::assertSame(1, $this->subject->countAll($this->site));
    }

    #[Framework\Attributes\Test]
    public function findOneByLanguageIdReturnsNullIfGivenSiteDoesNotExist(): void
    {
        $site = new Core\Site\Entity\Site('foo', 1, []);

        self::assertNull($this->subject->findOneByLanguageId($site, 0));
    }

    #[Framework\Attributes\Test]
    public function findOneByLanguageIdReturnsNullIfGivenSiteIsExcludedFromWarming(): void
    {
        $this->site = $this->createSite(languagesToExcludeFromWarming: [0]);
        $this->siteFinder->getAllSites(false);

        self::assertNull($this->subject->findOneByLanguageId($this->site, 0));
    }

    #[Framework\Attributes\Test]
    public function findOneByLanguageIdReturnsNullIfGivenSiteLanguageDoesNotExist(): void
    {
        self::assertNull($this->subject->findOneByLanguageId($this->site, 5));
    }

    #[Framework\Attributes\Test]
    public function findOneByLanguageIdReturnsNullIfSiteLanguageIsNotAvailableToGivenBackendUser(): void
    {
        self::assertNull($this->subject->findOneByLanguageId($this->site, 0));
    }

    #[Framework\Attributes\Test]
    public function findOneByLanguageIdReturnsSiteLanguageAvailableToGivenBackendUser(): void
    {
        self::assertSame(
            $this->site->getLanguageById(1),
            $this->subject->findOneByLanguageId($this->site, 1),
        );
    }

    #[Framework\Attributes\Test]
    public function findOneByLanguageIdReturnsNullIfSiteLanguageIsExcludedFromWarming(): void
    {
        $this->site = $this->createSite(languagesToExcludeFromWarming: [1]);
        $this->siteFinder->getAllSites(false);

        self::assertNull($this->subject->findOneByLanguageId($this->site, 1));
    }
}
