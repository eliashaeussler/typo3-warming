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

namespace EliasHaeussler\Typo3Warming\Http\Client;

use EliasHaeussler\CacheWarmup;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\EventDispatcher;
use TYPO3\CMS\Core;

/**
 * ClientBridge
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final class ClientBridge
{
    private ?ClientInterface $client = null;

    public function __construct(
        private readonly Core\Http\Client\GuzzleClientFactory $guzzleClientFactory,
        private readonly EventDispatcher\EventDispatcherInterface $eventDispatcher,
    ) {}

    public function getClientFactory(): CacheWarmup\Http\Client\ClientFactory
    {
        return new CacheWarmup\Http\Client\ClientFactory($this->eventDispatcher, $this->getClientConfig());
    }

    /**
     * @return array<string, mixed>
     */
    public function getClientConfig(): array
    {
        $this->client ??= $this->guzzleClientFactory->getClient();

        return $this->getClientConfigFromReflection($this->client);
    }

    /**
     * @return array<string, mixed>
     */
    private function getClientConfigFromReflection(ClientInterface $client): array
    {
        if (!($client instanceof Client)) {
            return [];
        }

        $reflection = new \ReflectionObject($client);
        $property = $reflection->getProperty('config');

        return $property->getValue($client);
    }
}
