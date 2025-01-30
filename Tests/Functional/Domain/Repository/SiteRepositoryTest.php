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
 * SiteRepositoryTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Domain\Repository\SiteRepository::class)]
final class SiteRepositoryTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\Functional\SiteTrait;

    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'warming',
    ];

    private Core\Site\Entity\Site $site;
    private Src\Domain\Repository\SiteRepository $subject;
    private Core\Site\SiteFinder $siteFinder;

    public function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/Database/be_groups.csv');
        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/Database/pages.csv');

        $this->site = $this->createSite();
        $this->setUpBackendUser(2);

        $this->subject = $this->get(Src\Domain\Repository\SiteRepository::class);
        $this->siteFinder = $this->get(Core\Site\SiteFinder::class);
    }

    #[Framework\Attributes\Test]
    public function findAllIgnoresSitesWhichAreExcludedFromWarming(): void
    {
        $this->site = $this->createSite(languagesToExcludeFromWarming: [0]);
        $this->siteFinder->getAllSites(false);

        self::assertSame([], $this->subject->findAll());
    }

    #[Framework\Attributes\Test]
    public function findAllReturnsAllSitesAvailableToGivenBackendUser(): void
    {
        $this->createSite(identifier: 'other-test-site');
        $this->siteFinder->getAllSites(false);

        $expected = [
            'test-site' => $this->site,
        ];

        self::assertEquals($expected, $this->subject->findAll());
    }

    #[Framework\Attributes\Test]
    public function findAllReturnsAllSiteLanguages(): void
    {
        $this->setUpBackendUser(3);

        $otherSite = $this->createSite(identifier: 'other-test-site');
        $this->siteFinder->getAllSites(false);

        $expected = [
            'test-site' => $this->site,
            'other-test-site' => $otherSite,
        ];

        self::assertEquals($expected, $this->subject->findAll());
    }

    #[Framework\Attributes\Test]
    public function countAllReturnsNumberOfAllSites(): void
    {
        self::assertSame(1, $this->subject->countAll());
    }

    #[Framework\Attributes\Test]
    public function findOneByRootPageIdReturnsNullIfNoSiteExistsForGivenRootPageId(): void
    {
        self::assertNull($this->subject->findOneByRootPageId(3));
    }

    #[Framework\Attributes\Test]
    public function findOneByRootPageIdReturnsNullIfRelatedSiteIsExcludedFromWarming(): void
    {
        $this->site = $this->createSite(languagesToExcludeFromWarming: [0]);
        $this->siteFinder->getAllSites(false);

        self::assertNull($this->subject->findOneByRootPageId(1));
    }

    #[Framework\Attributes\Test]
    public function findOneByRootPageIdReturnsNullIfRelatedSiteIsNotAvailableToGivenBackendUser(): void
    {
        $this->createSite(identifier: 'other-test-site', rootPageId: 3);
        $this->siteFinder->getAllSites(false);

        self::assertNull($this->subject->findOneByRootPageId(3));
    }

    #[Framework\Attributes\Test]
    public function findOneByRootPageIdReturnsRelatedSite(): void
    {
        self::assertEquals($this->site, $this->subject->findOneByRootPageId(1));
    }

    #[Framework\Attributes\Test]
    public function findOneByPageIdReturnsNullIfNoSiteExistsForGivenPageId(): void
    {
        self::assertNull($this->subject->findOneByPageId(99));
    }

    #[Framework\Attributes\Test]
    public function findOneByPageIdReturnsNullIfRelatedSiteIsExcludedFromWarming(): void
    {
        $this->site = $this->createSite(languagesToExcludeFromWarming: [0]);
        $this->siteFinder->getAllSites(false);

        self::assertNull($this->subject->findOneByPageId(3));
    }

    #[Framework\Attributes\Test]
    public function findOneByPageIdReturnsNullIfRelatedSiteIsNotAvailableToGivenBackendUser(): void
    {
        $this->setUpBackendUser(1);

        $this->createSite(identifier: 'other-test-site', rootPageId: 2);
        $this->siteFinder->getAllSites(false);

        self::assertNull($this->subject->findOneByPageId(3));
    }

    #[Framework\Attributes\Test]
    public function findOneByPageIdReturnsRelatedSite(): void
    {
        self::assertEquals($this->site, $this->subject->findOneByPageId(3));
    }

    #[Framework\Attributes\Test]
    public function findOneByIdentifierReturnsNullIfNoSiteExistsForGivenIdentifier(): void
    {
        self::assertNull($this->subject->findOneByIdentifier('foo'));
    }

    #[Framework\Attributes\Test]
    public function findOneByIdentifierReturnsNullIfRelatedSiteIsExcludedFromWarming(): void
    {
        $this->site = $this->createSite(languagesToExcludeFromWarming: [0]);
        $this->siteFinder->getAllSites(false);

        self::assertNull($this->subject->findOneByIdentifier('test-site'));
    }

    #[Framework\Attributes\Test]
    public function findOneByIdentifierReturnsNullIfRelatedSiteIsNotAvailableToGivenBackendUser(): void
    {
        $this->setUpBackendUser(1);

        $this->createSite(identifier: 'other-test-site');
        $this->siteFinder->getAllSites(false);

        self::assertNull($this->subject->findOneByIdentifier('other-test-site'));
    }

    #[Framework\Attributes\Test]
    public function findOneByIdentifierReturnsRelatedSite(): void
    {
        self::assertEquals($this->site, $this->subject->findOneByIdentifier('test-site'));
    }
}
