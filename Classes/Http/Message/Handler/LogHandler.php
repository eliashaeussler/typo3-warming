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

namespace EliasHaeussler\Typo3Warming\Http\Message\Handler;

use EliasHaeussler\CacheWarmup;
use Psr\Http\Message;
use Psr\Log;

/**
 * LogHandler
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class LogHandler implements CacheWarmup\Http\Message\Handler\ResponseHandler
{
    public function __construct(
        private Log\LoggerInterface $logger,
        private ?string $requestId = null,
    ) {}

    public function onSuccess(Message\ResponseInterface $response, Message\UriInterface $uri): void
    {
        $this->logger->info(
            'URL {url} was successfully crawled (status code: {status_code}).',
            [
                'url' => $uri,
                'status_code' => $response->getStatusCode(),
                'request_id' => $this->requestId,
            ],
        );
    }

    public function onFailure(\Throwable $exception, Message\UriInterface $uri): void
    {
        $this->logger->error(
            'Error while crawling URL {url} (exception: {exception}).',
            [
                'url' => $uri,
                'exception' => $exception,
                'request_id' => $this->requestId,
            ],
        );
    }
}
