<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Fixtures;

use Psr\Http\Message\ResponseInterface;
use Throwable;
use TYPO3\CMS\Core\Http\RequestFactory;

/**
 * DummyRequestFactory
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final class DummyRequestFactory extends RequestFactory
{
    /**
     * @var list<ResponseInterface|Throwable>
     */
    public $responseStack = [];

    /**
     * @param array<string, mixed> $options
     * @throws Throwable
     */
    public function request(string $uri, string $method = 'GET', array $options = []): ResponseInterface
    {
        if ($this->responseStack === []) {
            return parent::request($uri, $method, $options);
        }

        $response = array_shift($this->responseStack);

        if ($response instanceof Throwable) {
            throw $response;
        }

        return $response;
    }
}