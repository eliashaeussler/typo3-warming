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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Http\Message\Request;

use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * RequestOptionsTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\BackupGlobals(true)]
#[Framework\Attributes\CoversClass(Src\Http\Message\Request\RequestOptions::class)]
final class RequestOptionsTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'typed_extconf',
        'warming',
    ];

    protected array $configurationToUseInTestInstance = [
        'SYS' => [
            'encryptionKey' => '0b84531802b4bff53a8cc152b8c5b9965fb33deb897a60130349109fbcb6f7d39e5d125d6d27a89b6e16b66a811fca42',
        ],
    ];

    protected bool $initializeDatabase = false;

    private Src\Http\Message\Request\RequestOptions $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(Src\Http\Message\Request\RequestOptions::class);
    }

    #[Framework\Attributes\Test]
    public function getUserAgentReturnsCorrectlyGeneratedUserAgent(): void
    {
        self::assertSame(
            'TYPO3/tx_warming_crawlercbca109427154aa0b126274755477f4337ecd56d',
            $this->subject->getUserAgent(),
        );
    }
}
