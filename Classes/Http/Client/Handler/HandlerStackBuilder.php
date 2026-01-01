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

namespace EliasHaeussler\Typo3Warming\Http\Client\Handler;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise;
use Psr\Http\Message;

/**
 * HandlerStackBuilder
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final class HandlerStackBuilder
{
    /**
     * @param array<string, mixed> $options
     * @param (callable(Message\RequestInterface, array<string, mixed>): Promise\PromiseInterface)|null $handler
     */
    public function buildFromClientOrRequestOptions(
        ClientInterface $client,
        array $options,
        ?callable $handler = null,
    ): HandlerStack {
        $registeredHandler = null;

        // Resolve currently registered handler from request options (high priority) or client (low priority)
        if (is_callable($options['handler'] ?? null)) {
            $registeredHandler = $options['handler'];
        } elseif ($client instanceof Client) {
            $registeredHandler = $this->getHandlerFromClient($client);
        }

        // Early return with new handler stack and given handler, if no handler is currently registered
        if ($registeredHandler === null) {
            return HandlerStack::create($handler);
        }

        // Early return with new handler stack and given handler (high priority) or currently registered
        // handler (low priority), if currently registered handler is not a handler stack
        if (!($registeredHandler instanceof HandlerStack)) {
            return HandlerStack::create($handler ?? $registeredHandler);
        }

        // Overwrite handler for currently registered handler stack
        if ($handler !== null) {
            $registeredHandler->setHandler($handler);
        }

        return $registeredHandler;
    }

    private function getHandlerFromClient(Client $client): ?callable
    {
        $clientReflection = new \ReflectionObject($client);
        $clientConfig = $clientReflection->getProperty('config')->getValue($client);

        if (is_callable($clientConfig['handler'] ?? null)) {
            return $clientConfig['handler'];
        }

        return null;
    }
}
