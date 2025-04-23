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

namespace EliasHaeussler\Typo3Warming\Tests\Functional;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler;
use GuzzleHttp\Psr7;
use TYPO3\CMS\Core;

/**
 * ClientMockTrait
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
trait ClientMockTrait
{
    private Handler\MockHandler $handler;

    private function createMockHandler(): Handler\MockHandler
    {
        return $this->handler ??= new Handler\MockHandler();
    }

    private function createClient(): ClientInterface
    {
        return new Client($this->getClientOptions());
    }

    /**
     * @return array{handler: Handler\MockHandler}
     */
    private function getClientOptions(): array
    {
        return ['handler' => $this->createMockHandler()];
    }

    private function mockSitemapResponse(string ...$languages): void
    {
        foreach ($languages as $language) {
            $filename = sprintf(__DIR__ . '/Fixtures/Files/sitemap_%s.xml', $language);
            $sitemapXml = fopen($filename, 'r');

            self::assertIsResource($sitemapXml);

            $this->handler->append(
                new Core\Http\Response(Psr7\Utils::streamFor($sitemapXml)),
            );
        }
    }
}
