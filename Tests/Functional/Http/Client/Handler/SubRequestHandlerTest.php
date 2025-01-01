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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Http\Client\Handler;

use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use GuzzleHttp\Exception;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * SubRequestHandlerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Client\Handler\SubRequestHandler::class)]
final class SubRequestHandlerTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\Functional\SiteTrait;

    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'warming',
    ];

    protected bool $initializeDatabase = false;

    private Frontend\Http\Application&Framework\MockObject\MockObject $applicationMock;
    private Src\Http\Client\Handler\SubRequestHandler $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->createSite();

        $this->applicationMock = $this->createMock(Frontend\Http\Application::class);
        $this->subject = new Src\Http\Client\Handler\SubRequestHandler(
            $this->applicationMock,
            $this->get(Core\Site\SiteFinder::class),
        );
    }

    #[Framework\Attributes\Test]
    public function invokeUsesFallbackHandlerIfNoMatchingSitesAreAvailable(): void
    {
        $request = new Core\Http\Request('https://typo3-fake.local/', body: 'php://temp');

        $this->applicationMock->expects(self::never())->method('handle');

        ($this->subject)($request, [])->cancel();
    }

    #[Framework\Attributes\Test]
    public function invokeReturnsFulfilledPromiseWithResponseFromApplication(): void
    {
        $request = new Core\Http\Request('https://typo3-testing.local/', body: 'php://temp');
        $response = new Core\Http\Response();

        $this->applicationMock->method('handle')->willReturn($response);

        $actual = ($this->subject)($request, [])->wait();

        self::assertSame($response, $actual);
    }

    #[Framework\Attributes\Test]
    public function invokeReturnsRejectedPromiseWithExceptionFromApplication(): void
    {
        $request = new Core\Http\Request('https://typo3-testing.local/', body: 'php://temp');
        $exception = new \Exception('something went wrong');

        $this->applicationMock->method('handle')->willThrowException($exception);

        $expected = new Exception\RequestException('something went wrong', $request);
        $actual = null;

        try {
            ($this->subject)($request, [])->wait();
        } catch (\Exception $actual) {
            // Intentionally left blank.
        }

        self::assertEquals($expected, $actual);
    }
}
