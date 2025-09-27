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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Middleware;

use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use Psr\Http\Server;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * ProcessCacheWarmupQueueMiddlewareTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Middleware\ProcessCacheWarmupQueueMiddleware::class)]
final class ProcessCacheWarmupQueueMiddlewareTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\Functional\SiteTrait;

    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'typed_extconf',
        'warming',
    ];

    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'warming' => [
                'crawler' => Tests\Functional\Fixtures\Classes\DummyCrawler::class,
            ],
        ],
    ];

    private Src\Queue\CacheWarmupQueue $cacheWarmupQueue;
    private Src\Middleware\ProcessCacheWarmupQueueMiddleware $subject;
    private Core\Messaging\FlashMessageQueue $flashMessageQueue;
    private Server\RequestHandlerInterface&Framework\MockObject\Stub $handlerStub;

    public function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/pages.csv');

        // Create site configuration
        $this->createSite();

        // Set up backend user
        $backendUser = $this->setUpBackendUser(3);
        $GLOBALS['LANG'] = $this->get(Core\Localization\LanguageServiceFactory::class)->createFromUserPreferences($backendUser);

        $flashMessageService = $this->get(Core\Messaging\FlashMessageService::class);

        $this->cacheWarmupQueue = $this->get(Src\Queue\CacheWarmupQueue::class);
        $this->subject = new Src\Middleware\ProcessCacheWarmupQueueMiddleware($flashMessageService, $this->cacheWarmupQueue);
        $this->flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier(
            Core\Messaging\FlashMessageQueue::NOTIFICATION_QUEUE,
        );
        $this->handlerStub = self::createStub(Server\RequestHandlerInterface::class);
    }

    #[Framework\Attributes\Test]
    public function processDoesNothingIfQueueIsEmpty(): void
    {
        $request = new Core\Http\ServerRequest('https://typo3-testing.local');

        $this->handlerStub->method('handle')->willReturn(new Core\Http\Response());

        $this->subject->process($request, $this->handlerStub);

        self::assertTrue($this->flashMessageQueue->isEmpty());
    }

    #[Framework\Attributes\Test]
    public function processIgnoresErrorsDuringCacheWarmup(): void
    {
        $this->cacheWarmupQueue->enqueue(new Src\ValueObject\Request\PageWarmupRequest(1));

        $request = new Core\Http\ServerRequest('https://typo3-testing.local');

        $this->handlerStub->method('handle')->willReturn(new Core\Http\Response());

        Tests\Functional\Fixtures\Classes\DummyCrawler::$throwExceptionOnNextIteration = true;

        $this->subject->process($request, $this->handlerStub);

        self::assertTrue($this->flashMessageQueue->isEmpty());
    }

    /**
     * @param non-empty-list<Src\ValueObject\Request\PageWarmupRequest> $requests
     * @param list<non-negative-int> $failOnIterations
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('processEnqueuesFlashMessageAfterCacheWarmupQueueProcessingDataProvider')]
    public function processEnqueuesFlashMessageAfterCacheWarmupQueueProcessing(
        array $requests,
        array $failOnIterations,
        Core\Type\ContextualFeedbackSeverity $expected,
    ): void {
        Tests\Functional\Fixtures\Classes\DummyCrawler::$failOnIterations = $failOnIterations;

        foreach ($requests as $request) {
            $this->cacheWarmupQueue->enqueue($request);
        }

        $this->subject->process(new Core\Http\ServerRequest('https://typo3-testing.local'), $this->handlerStub);

        self::assertCount(1, $this->flashMessageQueue->getAllMessages($expected));
    }

    /**
     * @return \Generator<string, array{non-empty-list<Src\ValueObject\Request\PageWarmupRequest>, list<non-negative-int>, Core\Type\ContextualFeedbackSeverity}>
     */
    public static function processEnqueuesFlashMessageAfterCacheWarmupQueueProcessingDataProvider(): \Generator
    {
        yield 'only failed warmups' => [
            [
                new Src\ValueObject\Request\PageWarmupRequest(1),
                new Src\ValueObject\Request\PageWarmupRequest(2),
            ],
            [0, 1],
            Core\Type\ContextualFeedbackSeverity::ERROR,
        ];
        yield 'failed and successful warmups' => [
            [
                new Src\ValueObject\Request\PageWarmupRequest(1),
                new Src\ValueObject\Request\PageWarmupRequest(2),
            ],
            [0],
            Core\Type\ContextualFeedbackSeverity::WARNING,
        ];
        yield 'only successful warmups' => [
            [
                new Src\ValueObject\Request\PageWarmupRequest(1),
                new Src\ValueObject\Request\PageWarmupRequest(2),
            ],
            [],
            Core\Type\ContextualFeedbackSeverity::OK,
        ];
    }

    protected function tearDown(): void
    {
        Tests\Functional\Fixtures\Classes\DummyCrawler::reset();

        parent::tearDown();
    }
}
