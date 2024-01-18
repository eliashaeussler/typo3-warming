<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Http\Client;

use EliasHaeussler\Typo3Warming as Src;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * ClientFactoryTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\BackupGlobals(true)]
#[Framework\Attributes\CoversClass(Src\Http\Client\ClientFactory::class)]
final class ClientFactoryTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Core\Http\Client\GuzzleClientFactory $guzzleClientFactory;
    private Src\Http\Client\ClientFactory $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guzzleClientFactory = new Core\Http\Client\GuzzleClientFactory();
        $this->subject = new Src\Http\Client\ClientFactory($this->guzzleClientFactory);
    }

    #[Framework\Attributes\Test]
    public function getReturnsOriginalGuzzleClientIfNoAdditionalConfigIsGiven(): void
    {
        self::assertEquals(
            $this->guzzleClientFactory->getClient(),
            $this->subject->get(),
        );
    }

    #[Framework\Attributes\Test]
    public function getMergesGivenConfigWithGlobalClientConfig(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['HTTP'] = [
            RequestOptions::HEADERS => [
                'X-Foo' => 'baz',
            ],
            RequestOptions::VERIFY => true,
        ];

        $expected = new Client([
            RequestOptions::HEADERS => [
                'X-Foo' => 'baz',
            ],
            RequestOptions::DELAY => 3,
            RequestOptions::VERIFY => true,
        ]);

        self::assertEquals($expected, $this->subject->get([
            RequestOptions::DELAY => 3,
        ]));
    }
}
