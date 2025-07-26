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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Controller;

use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * CacheWarmupControllerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Controller\CacheWarmupController::class)]
final class CacheWarmupControllerTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'typed_extconf',
        'warming',
    ];

    private Src\Controller\CacheWarmupController $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(Src\Controller\CacheWarmupController::class);

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');

        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(Core\Localization\LanguageServiceFactory::class)->createFromUserPreferences($backendUser);
    }

    #[Framework\Attributes\Test]
    public function controllerReturnsBadRequestResponseIfRequestIsNotSupported(): void
    {
        $request = new Core\Http\ServerRequest();

        $actual = ($this->subject)($request);

        self::assertEquals(
            new Core\Http\Response(null, 400, [], 'Invalid request headers'),
            $actual,
        );
    }

    #[Framework\Attributes\Test]
    public function controllerReturnsBadRequestResponseIfRequestParametersAreInvalid(): void
    {
        $request = new Core\Http\ServerRequest();
        $request = $request->withHeader('Accept', 'text/event-stream');
        $request = $request->withQueryParams(['sites' => 'foo']);

        $actual = ($this->subject)($request);

        self::assertEquals(
            new Core\Http\Response(null, 400, [], 'Invalid request parameters'),
            $actual,
        );
    }
}
