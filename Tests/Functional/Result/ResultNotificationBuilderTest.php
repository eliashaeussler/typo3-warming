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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Result;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * ResultNotificationBuilderTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Result\ResultNotificationBuilder::class)]
final class ResultNotificationBuilderTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Src\Tests\Functional\SiteTrait;

    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'typed_extconf',
        'warming',
    ];

    private Src\Result\ResultNotificationBuilder $subject;
    private Src\Result\CacheWarmupResult $cacheWarmupResult;

    public function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/pages.csv');

        // Set up backend user
        $backendUser = $this->setUpBackendUser(3);
        $GLOBALS['LANG'] = $this->get(Core\Localization\LanguageServiceFactory::class)->createFromUserPreferences($backendUser);

        $this->subject = new Src\Result\ResultNotificationBuilder();
        $this->cacheWarmupResult = new Src\Result\CacheWarmupResult(
            new CacheWarmup\Result\CacheWarmupResult(),
            [
                new CacheWarmup\Sitemap\Sitemap(new Core\Http\Uri('https://typo3-testing.local/')),
            ],
            [
                new CacheWarmup\Sitemap\Url('https://typo3-testing.local/'),
            ],
        );
    }

    #[Framework\Attributes\Test]
    public function buildMessagesReturnsNotificationMessages(): void
    {
        $site = $this->createSite();
        $origin = new Src\Domain\Model\SiteAwareSitemap(
            new Core\Http\Uri('https://typo3-testing.local/'),
            $site,
            $site->getLanguageById(0),
        );

        $this->cacheWarmupResult->getResult()->addResult(
            CacheWarmup\Result\CrawlingResult::createSuccessful(
                new CacheWarmup\Sitemap\Url('https://typo3-testing.local/', origin: $origin),
            ),
        );
        $this->cacheWarmupResult->getResult()->addResult(
            CacheWarmup\Result\CrawlingResult::createFailed(
                new CacheWarmup\Sitemap\Url('https://typo3-testing.local/de/', origin: $origin),
            ),
        );
        $this->cacheWarmupResult->getResult()->addResult(
            CacheWarmup\Result\CrawlingResult::createSuccessful(
                new CacheWarmup\Sitemap\Url('https://typo3-testing.local/'),
                [
                    'urlMetadata' => new Src\Http\Message\UrlMetadata(1),
                ],
            ),
        );
        $this->cacheWarmupResult->getResult()->addResult(
            CacheWarmup\Result\CrawlingResult::createFailed(
                new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-1'),
                [
                    'urlMetadata' => new Src\Http\Message\UrlMetadata(2),
                ],
            ),
        );
        $this->cacheWarmupResult->getResult()->addResult(
            CacheWarmup\Result\CrawlingResult::createFailed(
                new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-1'),
                [
                    'urlMetadata' => new Src\Http\Message\UrlMetadata(2),
                ],
            ),
        );
        $this->cacheWarmupResult->getResult()->addResult(
            CacheWarmup\Result\CrawlingResult::createSuccessful(
                new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-2'),
                [
                    'urlMetadata' => new Src\Http\Message\UrlMetadata(3),
                ],
            ),
        );
        $this->cacheWarmupResult->getResult()->addResult(
            CacheWarmup\Result\CrawlingResult::createFailed(
                new CacheWarmup\Sitemap\Url('https://typo3-testing.local/subsite-2'),
                [
                    'urlMetadata' => new Src\Http\Message\UrlMetadata(3),
                ],
            ),
        );

        $expected = [
            $this->createSiteNotification(
                'Root',
                1,
                $site->getLanguageById(0),
                1,
                1,
            ),
            $this->createSiteNotification(
                'Root L=1',
                5,
                $site->getLanguageById(1),
                0,
                0,
            ),
            'Cache of page "Root [1]" was successfully warmed up.',
            'Cache could not be warmed up for "Subsite 1 [2]".',
            'Cache warmup for page "Subsite 2 [3]" finished with warnings.',
        ];

        $actual = $this->subject->buildMessages(
            new Src\ValueObject\Request\WarmupRequest(
                'foo',
                [
                    new Src\ValueObject\Request\SiteWarmupRequest($site, [0, 1]),
                ],
                [
                    new Src\ValueObject\Request\PageWarmupRequest(1),
                    new Src\ValueObject\Request\PageWarmupRequest(2),
                    new Src\ValueObject\Request\PageWarmupRequest(2),
                    new Src\ValueObject\Request\PageWarmupRequest(3),
                    new Src\ValueObject\Request\PageWarmupRequest(3),
                ],
            ),
            $this->cacheWarmupResult,
        );

        self::assertSame($expected, $actual);
    }

    #[Framework\Attributes\Test]
    public function buildMessagesReturnsEmptyMessageIfNoSitesOrPagesWereRequested(): void
    {
        $message = Src\Configuration\Localization::translate('notification.message.empty');

        $actual = $this->subject->buildMessages(
            new Src\ValueObject\Request\WarmupRequest('foo'),
            $this->cacheWarmupResult,
        );

        self::assertSame([$message], $actual);
    }

    #[Framework\Attributes\Test]
    public function buildMessagesThrowsExceptionIfRequestedPageDoesNotExist(): void
    {
        $this->expectExceptionObject(Src\Exception\MissingPageIdException::create());

        $this->subject->buildMessages(
            new Src\ValueObject\Request\WarmupRequest(
                'foo',
                pages: [
                    new Src\ValueObject\Request\PageWarmupRequest(99),
                ],
            ),
            $this->cacheWarmupResult,
        );
    }

    private function createSiteNotification(
        string $pageTitle,
        int $pageId,
        Core\Site\Entity\SiteLanguage $siteLanguage,
        int $successful,
        int $failed,
    ): string {
        return <<<LOCALLANG
Caches for site "{$pageTitle} [{$pageId}]" and language "{$siteLanguage->getTitle()} [{$siteLanguage->getLanguageId()}]" have been warmed up with the following result:

{$successful} successful, {$failed} failed
LOCALLANG;
    }
}
