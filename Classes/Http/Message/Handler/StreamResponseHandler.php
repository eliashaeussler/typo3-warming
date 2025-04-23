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

namespace EliasHaeussler\Typo3Warming\Http\Message\Handler;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\SSE;
use EliasHaeussler\Typo3Warming\Http;
use Psr\Http\Message;

/**
 * StreamResponseHandler
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class StreamResponseHandler implements CacheWarmup\Http\Message\Handler\ResponseHandler
{
    public function __construct(
        private SSE\Stream\EventStream $stream,
        private int $numberOfUrls,
        private CacheWarmup\Result\CacheWarmupResult $result,
    ) {}

    /**
     * @throws \JsonException
     * @throws SSE\Exception\StreamIsClosed
     * @throws SSE\Exception\StreamIsInactive
     */
    public function onSuccess(Message\ResponseInterface $response, Message\UriInterface $uri): void
    {
        $this->sendEvent($uri);
    }

    /**
     * @throws \JsonException
     * @throws SSE\Exception\StreamIsClosed
     * @throws SSE\Exception\StreamIsInactive
     */
    public function onFailure(\Throwable $exception, Message\UriInterface $uri): void
    {
        $this->sendEvent($uri);
    }

    /**
     * @throws \JsonException
     * @throws SSE\Exception\StreamIsClosed
     * @throws SSE\Exception\StreamIsInactive
     */
    private function sendEvent(Message\UriInterface $currentUrl): void
    {
        $event = new Http\Message\Event\WarmupProgressEvent(
            (string)$currentUrl,
            array_map('strval', $this->result->getSuccessful()),
            array_map('strval', $this->result->getFailed()),
            $this->numberOfUrls,
        );

        $this->stream->sendEvent($event);
    }
}
