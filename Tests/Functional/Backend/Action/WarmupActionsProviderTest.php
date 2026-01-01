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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Backend\Action;

use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * WarmupActionsProviderTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Backend\Action\WarmupActionsProvider::class)]
final class WarmupActionsProviderTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\Functional\ClientMockTrait;
    use Tests\Functional\SiteTrait;

    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'typed_extconf',
        'warming',
    ];

    private Core\Site\Entity\Site $site;
    private Core\Cache\Frontend\VariableFrontend $cache;
    private Src\Backend\Action\WarmupActionsProvider $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/Database/be_groups.csv');
        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/Database/pages.csv');

        // Create site configuration
        $this->site = $this->createSite();

        // Set up backend user
        $backendUser = $this->setUpBackendUser(3);
        $GLOBALS['LANG'] = $this->get(Core\Localization\LanguageServiceFactory::class)->createFromUserPreferences($backendUser);

        $clientFactoryMock = $this->createMock(Core\Http\Client\GuzzleClientFactory::class);
        $clientFactoryMock->method('getClient')->willReturn($this->createClient());

        if ((new Core\Information\Typo3Version())->getMajorVersion() >= 14) {
            /* @phpstan-ignore arguments.count */
            $cacheBackend = new Core\Cache\Backend\TransientMemoryBackend();
        } else {
            // @todo Remove once support for TYPO3 v13 is dropped
            $cacheBackend = new Core\Cache\Backend\TransientMemoryBackend('testing');
        }

        $this->cache = new Core\Cache\Frontend\VariableFrontend('runtime', $cacheBackend);
        $this->subject = new Src\Backend\Action\WarmupActionsProvider(
            $this->get(Src\Configuration\Configuration::class),
            $this->get(Src\Security\WarmupPermissionGuard::class),
            $this->get(Core\Site\SiteFinder::class),
            $this->get(Src\Domain\Repository\SiteLanguageRepository::class),
            $this->get(Src\Domain\Repository\SiteRepository::class),
            new Typo3SitemapLocator\Sitemap\SitemapLocator(
                new Typo3SitemapLocator\Http\Client\ClientFactory(
                    $clientFactoryMock,
                    new Core\EventDispatcher\NoopEventDispatcher(),
                ),
                $this->get(Typo3SitemapLocator\Cache\SitemapsCache::class),
                new Core\EventDispatcher\NoopEventDispatcher(),
                [new Typo3SitemapLocator\Sitemap\Provider\DefaultProvider()],
            ),
            $this->cache,
        );
    }

    #[Framework\Attributes\Test]
    public function provideActionsReturnsPageActionsForPageWarmupContext(): void
    {
        $actual = $this->subject->provideActions(Src\Backend\Action\WarmupActionContext::Page, 1);

        self::assertInstanceOf(Src\Backend\Action\PageWarmupActions::class, $actual);
    }

    #[Framework\Attributes\Test]
    public function provideActionsReturnsSiteActionsForSiteWarmupContext(): void
    {
        $actual = $this->subject->provideActions(Src\Backend\Action\WarmupActionContext::Site, 1);

        self::assertInstanceOf(Src\Backend\Action\SiteWarmupActions::class, $actual);
    }

    #[Framework\Attributes\Test]
    public function provideSiteActionsReturnsNullOnSystemRootPage(): void
    {
        self::assertNull($this->subject->provideSiteActions(0));
    }

    #[Framework\Attributes\Test]
    public function provideSiteActionsReturnsNullOnInvalidPage(): void
    {
        self::assertNull($this->subject->provideSiteActions(99));
    }

    #[Framework\Attributes\Test]
    public function provideSiteActionsReturnsNullOnPageWithUnsupportedDoktype(): void
    {
        self::assertNull($this->subject->provideSiteActions(8));
    }

    #[Framework\Attributes\Test]
    public function provideSiteActionsReturnsNullOnOrphanedPage(): void
    {
        self::assertNull($this->subject->provideSiteActions(9));
    }

    #[Framework\Attributes\Test]
    public function provideSiteActionsReturnsSiteActionsAndCachesPageValidationCheck(): void
    {
        self::assertFalse($this->cache->has('warming_warmupActionsProvider_isValidPage_1'));
        self::assertNotNull($this->subject->provideSiteActions(1));
        self::assertTrue($this->cache->has('warming_warmupActionsProvider_isValidPage_1'));

        $this->cache->set('warming_warmupActionsProvider_isValidPage_1', false);

        self::assertNull($this->subject->provideSiteActions(1));
    }

    #[Framework\Attributes\Test]
    public function provideSiteActionsReturnsSiteActionsWithoutSiteLanguagesOfInaccessibleSitemaps(): void
    {
        $this->mockSitemapResponse('en', 'de');

        $expected = new Src\Backend\Action\SiteWarmupActions($this->site, [
            $this->site->getLanguageById(0),
            $this->site->getLanguageById(1),
        ]);

        self::assertEquals($expected, $this->subject->provideSiteActions(1));
    }

    #[Framework\Attributes\Test]
    public function provideSiteActionsReturnsSiteActionsWithAvailableSiteLanguages(): void
    {
        $this->mockSitemapResponse('en', 'de', 'fr');

        $expected = new Src\Backend\Action\SiteWarmupActions($this->site, $this->site->getAllLanguages());

        self::assertEquals($expected, $this->subject->provideSiteActions(1));
    }

    #[Framework\Attributes\Test]
    public function providePageActionsReturnsNullOnSystemRootPage(): void
    {
        self::assertNull($this->subject->providePageActions(0));
    }

    #[Framework\Attributes\Test]
    public function providePageActionsReturnsNullOnInvalidPage(): void
    {
        self::assertNull($this->subject->providePageActions(99));
    }

    #[Framework\Attributes\Test]
    public function providePageActionsReturnsNullOnPageWithUnsupportedDoktype(): void
    {
        self::assertNull($this->subject->providePageActions(8));
    }

    #[Framework\Attributes\Test]
    public function providePageActionsReturnsNullOnOrphanedPage(): void
    {
        self::assertNull($this->subject->providePageActions(9));
    }

    #[Framework\Attributes\Test]
    public function providePageActionsReturnsPageActionsWithoutSiteLanguagesOfPagesWithInsufficientPermissions(): void
    {
        $this->setUpBackendUser(1);

        $expected = new Src\Backend\Action\PageWarmupActions(1, [
            $this->site->getLanguageById(0),
        ]);

        self::assertEquals($expected, $this->subject->providePageActions(1));
    }

    #[Framework\Attributes\Test]
    public function providePageActionsReturnsPageActionsWithAvailableSiteLanguages(): void
    {
        $expected = new Src\Backend\Action\PageWarmupActions(1, $this->site->getAllLanguages());

        self::assertEquals($expected, $this->subject->providePageActions(1));
    }
}
