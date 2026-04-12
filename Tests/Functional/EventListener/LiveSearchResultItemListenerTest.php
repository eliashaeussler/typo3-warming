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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\EventListener;

use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * LiveSearchResultItemListenerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\EventListener\LiveSearchResultItemListener::class)]
final class LiveSearchResultItemListenerTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\Functional\ClientMockTrait;
    use Tests\Functional\SiteTrait;

    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'typed_extconf',
        'warming',
    ];

    private Backend\Search\LiveSearch\ResultItem $resultItem;
    private Backend\Search\Event\ModifyResultItemInLiveSearchEvent $event;
    private Core\Imaging\IconFactory $iconFactory;
    private Src\EventListener\LiveSearchResultItemListener $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_groups.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/pages.csv');

        $this->createSite();
        $this->setUpBackendUser(3);

        $clientFactoryStub = self::createStub(Core\Http\Client\GuzzleClientFactory::class);
        $clientFactoryStub->method('getClient')->willReturn($this->createClient());

        $this->resultItem = new Backend\Search\LiveSearch\ResultItem(Backend\Search\LiveSearch\PageRecordProvider::class);
        $this->resultItem->setInternalData([
            'row' => $this->fetchPageRecord(),
        ]);

        $this->event = new Backend\Search\Event\ModifyResultItemInLiveSearchEvent($this->resultItem);
        $this->iconFactory = $this->get(Core\Imaging\IconFactory::class);
        $this->subject = new Src\EventListener\LiveSearchResultItemListener(
            new Src\Backend\Action\WarmupActionsProvider(
                $this->get(Src\Configuration\Configuration::class),
                $this->get(Src\Security\WarmupPermissionGuard::class),
                $this->get(Core\Site\SiteFinder::class),
                $this->get(Src\Domain\Repository\SiteLanguageRepository::class),
                $this->get(Src\Domain\Repository\SiteRepository::class),
                new Typo3SitemapLocator\Sitemap\SitemapLocator(
                    new Typo3SitemapLocator\Http\Client\ClientFactory(
                        $clientFactoryStub,
                        new Core\EventDispatcher\NoopEventDispatcher(),
                    ),
                    $this->get(Typo3SitemapLocator\Cache\SitemapsCache::class),
                    new Core\EventDispatcher\NoopEventDispatcher(),
                    [new Typo3SitemapLocator\Sitemap\Provider\DefaultProvider()],
                ),
                new Core\Cache\Frontend\NullFrontend('runtime'),
            ),
            new Src\Configuration\Configuration(),
            $this->iconFactory,
        );
    }

    #[Framework\Attributes\Test]
    public function invokeDoesNothingIfLiveSearchIsDisabled(): void
    {
        $subject = new Src\EventListener\LiveSearchResultItemListener(
            $this->get(Src\Backend\Action\WarmupActionsProvider::class),
            new Src\Configuration\Configuration(enabledInLiveSearch: false),
            $this->get(Core\Imaging\IconFactory::class),
        );

        $expected = clone $this->resultItem;

        $subject($this->event);

        self::assertEquals($expected, $this->event->getResultItem());
    }

    #[Framework\Attributes\Test]
    public function invokeDoesNothingOnUnsupportedProviderClassName(): void
    {
        $event = new Backend\Search\Event\ModifyResultItemInLiveSearchEvent(
            new Backend\Search\LiveSearch\ResultItem(Backend\Search\LiveSearch\DatabaseRecordProvider::class),
        );

        $expected = clone $event->getResultItem();

        ($this->subject)($event);

        self::assertEquals($expected, $event->getResultItem());
    }

    #[Framework\Attributes\Test]
    public function invokeDoesNothingIfPageIdIsMissing(): void
    {
        $this->resultItem->setInternalData([]);

        $expected = clone $this->resultItem;

        ($this->subject)($this->event);

        self::assertEquals($expected, $this->event->getResultItem());
    }

    #[Framework\Attributes\Test]
    public function invokeDoesNothingIfPageIdIsInvalid(): void
    {
        $this->resultItem->setInternalData([
            'row' => [
                'uid' => 0,
            ],
        ]);

        $expected = clone $this->resultItem;

        ($this->subject)($this->event);

        self::assertEquals($expected, $this->event->getResultItem());
    }

    #[Framework\Attributes\Test]
    public function invokeAddsSiteWarmupAction(): void
    {
        $this->setUpBackendUser(2);

        $this->handler->append(new Core\Http\Response());

        $this->resultItem->setInternalData([
            'row' => $this->fetchPageRecord(5),
        ]);

        $expected = clone $this->resultItem;
        $expected->addAction($this->createSiteAction());
        $expected->setExtraData([
            'siteIdentifier' => 'test-site',
            'languageId' => 1,
        ]);

        ($this->subject)($this->event);

        self::assertEquals($expected, $this->event->getResultItem());
    }

    #[Framework\Attributes\Test]
    public function invokeAddsPageWarmupAction(): void
    {
        $expected = clone $this->resultItem;
        $expected->addAction($this->createPageAction());
        $expected->setExtraData([
            'pageId' => 1,
            'languageId' => 0,
        ]);

        ($this->subject)($this->event);

        self::assertEquals($expected, $this->event->getResultItem());
    }

    #[Framework\Attributes\Test]
    public function invokeRespectsLanguageId(): void
    {
        $this->resultItem->setInternalData([
            'row' => $this->fetchPageRecord(5),
        ]);

        $expected = clone $this->resultItem;
        $expected->addAction($this->createPageAction());
        $expected->setExtraData([
            'pageId' => 1,
            'languageId' => 1,
        ]);

        ($this->subject)($this->event);

        self::assertEquals($expected, $this->event->getResultItem());
    }

    private function createSiteAction(): Backend\Search\LiveSearch\ResultItemAction
    {
        $action = new Backend\Search\LiveSearch\ResultItemAction('warmupSiteCache');
        $action->setLabel('Warmup all caches');
        $action->setIcon(
            $this->iconFactory->getIcon('cache-warmup-site', Core\Imaging\IconSize::SMALL),
        );

        return $action;
    }

    private function createPageAction(): Backend\Search\LiveSearch\ResultItemAction
    {
        $action = new Backend\Search\LiveSearch\ResultItemAction('warmupPageCache');
        $action->setLabel('Warmup cache for this page');
        $action->setIcon(
            $this->iconFactory->getIcon('cache-warmup-page', Core\Imaging\IconSize::SMALL),
        );

        return $action;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchPageRecord(int $uid = 1): array
    {
        $result = $this->getConnectionPool()->getConnectionForTable('pages')->select(
            ['*'],
            'pages',
            ['uid' => $uid],
        )->fetchAssociative();

        self::assertIsArray($result);

        return $result;
    }
}
