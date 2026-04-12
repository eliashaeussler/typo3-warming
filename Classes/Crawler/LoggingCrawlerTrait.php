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

namespace EliasHaeussler\Typo3Warming\Crawler;

use EliasHaeussler\SSE;
use EliasHaeussler\Typo3Warming\Http;
use Psr\Log;
use TYPO3\CMS\Core;

/**
 * LoggingCrawlerTrait
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
trait LoggingCrawlerTrait
{
    private ?Log\LoggerInterface $logger = null;

    private function createLogHandler(): Http\Message\Handler\LogHandler
    {
        $logger = $this->logger;
        $requestId = null;

        if ($logger === null) {
            $logger = $this->createLogger();
        }

        if ($this instanceof StreamableCrawler && $this->stream instanceof SSE\Stream\SelfEmittingEventStream) {
            $requestId = $this->stream->getId();
        }

        return new Http\Message\Handler\LogHandler($logger, $requestId);
    }

    private function createLogger(): Log\LoggerInterface
    {
        return Core\Utility\GeneralUtility::makeInstance(Core\Log\LogManager::class)->getLogger(static::class);
    }

    public function setLogger(Log\LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function setLogLevel(string $logLevel): void
    {
        // Intentionally left blank. Log level is handled by the configured logger itself.
    }
}
