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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Controller\Internal;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * ValidateCrawlerFqcnControllerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Controller\Internal\ValidateCrawlerFqcnController::class)]
final class ValidateCrawlerFqcnControllerTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Controller\Internal\ValidateCrawlerFqcnController $subject;
    private Core\Http\ServerRequest $request;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Controller\Internal\ValidateCrawlerFqcnController();
        $this->request = new Core\Http\ServerRequest('https://typo3-testing.local/', 'POST');
    }

    #[Framework\Attributes\Test]
    public function invokeThrowsExceptionIfControllerIsRequestedWithUnsupportedHttpMethod(): void
    {
        $request = $this->request->withMethod('GET');

        $this->expectException(Core\Http\Error\MethodNotAllowedException::class);

        ($this->subject)($request);
    }

    /**
     * @param array{actual?: mixed, expected?: mixed} $payload
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('invokeReturnsInvalidResponseIfPayloadIsInvalidDataProvider')]
    public function invokeReturnsInvalidResponseIfPayloadIsInvalid(array $payload): void
    {
        $request = $this->request->withParsedBody($payload);

        $expected = new Core\Http\JsonResponse(['valid' => false]);
        $actual = ($this->subject)($request);

        self::assertJsonStringEqualsJsonString((string)$expected->getBody(), (string)$actual->getBody());
    }

    #[Framework\Attributes\Test]
    public function invokeReturnsValidResponseIfPayloadIsValid(): void
    {
        $request = $this->request->withParsedBody([
            'actual' => Src\Crawler\ConcurrentUserAgentCrawler::class,
            'expected' => CacheWarmup\Crawler\Crawler::class,
        ]);

        $expected = new Core\Http\JsonResponse(['valid' => true]);
        $actual = ($this->subject)($request);

        self::assertJsonStringEqualsJsonString((string)$expected->getBody(), (string)$actual->getBody());
    }

    /**
     * @return \Generator<string, array{array{actual?: mixed, expected?: mixed}}>
     */
    public static function invokeReturnsInvalidResponseIfPayloadIsInvalidDataProvider(): \Generator
    {
        yield 'no properties' => [[]];
        yield 'invalid type of "actual" property' => [
            [
                'actual' => false,
                'expected' => CacheWarmup\Crawler\Crawler::class,
            ],
        ];
        yield 'invalid type of "expected" property' => [
            [
                'actual' => Src\Crawler\ConcurrentUserAgentCrawler::class,
                'expected' => false,
            ],
        ];
        yield 'invalid type of both properties' => [
            [
                'actual' => null,
                'expected' => false,
            ],
        ];
        yield 'non-existing class for "expect" property' => [
            [
                'actual' => 'Foo',
                'expected' => CacheWarmup\Crawler\Crawler::class,
            ],
        ];
        yield 'non-existing interface for "actual" property' => [
            [
                'actual' => Src\Crawler\ConcurrentUserAgentCrawler::class,
                'expected' => 'Baz',
            ],
        ];
        yield 'incompatible class in "expected" property' => [
            [
                'actual' => self::class,
                'expected' => CacheWarmup\Crawler\Crawler::class,
            ],
        ];
    }
}
