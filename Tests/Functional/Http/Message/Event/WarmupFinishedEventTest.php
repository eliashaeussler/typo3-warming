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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Http\Message\Event;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * WarmupFinishedEventTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Message\Event\WarmupFinishedEvent::class)]
final class WarmupFinishedEventTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Src\Tests\Functional\SiteTrait;

    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'typed_extconf',
        'warming',
    ];

    private Src\Result\CacheWarmupResult $cacheWarmupResult;
    private Src\Http\Message\Event\WarmupFinishedEvent $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__, 3) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__, 3) . '/Fixtures/Database/pages.csv');

        // Set up backend user
        $backendUser = $this->setUpBackendUser(3);
        $GLOBALS['LANG'] = $this->get(Core\Localization\LanguageServiceFactory::class)->createFromUserPreferences($backendUser);

        // Create site configuration
        $site = $this->createSite();

        $this->cacheWarmupResult = new Src\Result\CacheWarmupResult(
            new CacheWarmup\Result\CacheWarmupResult(),
            [
                new CacheWarmup\Sitemap\Sitemap(new Core\Http\Uri('https://typo3-testing.local/')),
            ],
            [
                new CacheWarmup\Sitemap\Url('https://typo3-testing.local/'),
            ],
        );
        $this->subject = new Src\Http\Message\Event\WarmupFinishedEvent(
            new Src\ValueObject\Request\WarmupRequest(
                'foo',
                [
                    new Src\ValueObject\Request\SiteWarmupRequest($site, [0, 1]),
                ],
                [
                    new Src\ValueObject\Request\PageWarmupRequest(1),
                    new Src\ValueObject\Request\PageWarmupRequest(2),
                ],
            ),
            $this->cacheWarmupResult,
        );
    }

    #[Framework\Attributes\Test]
    public function getDataIncludesWarmupState(): void
    {
        $this->cacheWarmupResult->getResult()->addResult(
            CacheWarmup\Result\CrawlingResult::createSuccessful(new Core\Http\Uri('https://typo3-testing.local/')),
        );
        $this->cacheWarmupResult->getResult()->addResult(
            CacheWarmup\Result\CrawlingResult::createFailed(new Core\Http\Uri('https://typo3-testing.local/')),
        );

        $actual = $this->subject->getData();

        self::assertSame(Src\Enums\WarmupState::Warning->value, $actual['state']);
    }

    #[Framework\Attributes\Test]
    public function getDataIncludesProgress(): void
    {
        $this->cacheWarmupResult->getResult()->addResult(
            CacheWarmup\Result\CrawlingResult::createSuccessful(new Core\Http\Uri('https://typo3-testing.local/')),
        );
        $this->cacheWarmupResult->getResult()->addResult(
            CacheWarmup\Result\CrawlingResult::createFailed(new Core\Http\Uri('https://typo3-testing.local/')),
        );

        $actual = $this->subject->getData();

        self::assertSame(2, $actual['progress']['current']);
        self::assertSame(2, $actual['progress']['total']);
    }

    #[Framework\Attributes\Test]
    public function getDataIncludesUrls(): void
    {
        $uri = new Core\Http\Uri('https://typo3-testing.local/');
        $successful = CacheWarmup\Result\CrawlingResult::createSuccessful($uri);
        $failed = CacheWarmup\Result\CrawlingResult::createFailed($uri);

        $this->cacheWarmupResult->getResult()->addResult($successful);
        $this->cacheWarmupResult->getResult()->addResult($failed);

        $expected = [
            'failed' => [
                [
                    'url' => (string)$failed->getUri(),
                    'data' => $failed->getData(),
                ],
            ],
            'successful' => [
                [
                    'url' => (string)$successful->getUri(),
                    'data' => $successful->getData(),
                ],
            ],
        ];

        $actual = $this->subject->getData();

        self::assertSame($expected, $actual['results']);
    }

    #[Framework\Attributes\Test]
    public function getDataIncludesExcludedSitemapsAndUrls(): void
    {
        $sitemap = new CacheWarmup\Sitemap\Sitemap(new Core\Http\Uri('https://typo3-testing.local/'));
        $url = new CacheWarmup\Sitemap\Url('https://typo3-testing.local/');

        $actual = $this->subject->getData();

        self::assertSame([(string)$sitemap], $actual['excluded']['sitemaps']);
        self::assertSame([(string)$url], $actual['excluded']['urls']);
    }

    #[Framework\Attributes\Test]
    public function getDataIncludesResultMessages(): void
    {
        $actual = $this->subject->getData();

        self::assertCount(4, $actual['messages']);
    }

    #[Framework\Attributes\Test]
    public function subjectIsJsonSerializable(): void
    {
        self::assertJson(json_encode($this->subject, JSON_THROW_ON_ERROR));
    }
}
