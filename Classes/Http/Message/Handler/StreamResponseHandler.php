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
final class StreamResponseHandler implements CacheWarmup\Http\Message\Handler\ResponseHandlerInterface
{
    /**
     * @var list<string>
     */
    private array $successfulUrls = [];

    /**
     * @var list<string>
     */
    private array $failedUrls = [];

    public function __construct(
        private readonly SSE\Stream\EventStream $stream,
        private readonly int $numberOfUrls,
    ) {}

    /**
     * @throws \JsonException
     * @throws SSE\Exception\StreamIsClosed
     * @throws SSE\Exception\StreamIsInactive
     */
    public function onSuccess(Message\ResponseInterface $response, Message\UriInterface $uri): void
    {
        $this->successfulUrls[] = (string)$uri;

        $this->sendEvent($uri);
    }

    /**
     * @throws \JsonException
     * @throws SSE\Exception\StreamIsClosed
     * @throws SSE\Exception\StreamIsInactive
     */
    public function onFailure(\Throwable $exception, Message\UriInterface $uri): void
    {
        $this->failedUrls[] = (string)$uri;

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
            $this->successfulUrls,
            $this->failedUrls,
            $this->numberOfUrls,
        );

        $this->stream->sendEvent($event);
    }
}
