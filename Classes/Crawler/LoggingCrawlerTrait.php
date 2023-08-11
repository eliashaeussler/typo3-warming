<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\SSE;
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

    private function createLogHandler(): CacheWarmup\Http\Message\Handler\LogHandler
    {
        $logger = $this->logger;

        if ($logger === null) {
            $logger = $this->createLogger();
        }

        // We use lowest log level because logger minimum log level is handled by the logger itself
        return new CacheWarmup\Http\Message\Handler\LogHandler($logger, Log\LogLevel::DEBUG);
    }

    private function createLogger(): Log\LoggerInterface
    {
        if ($this instanceof StreamableCrawler && $this->stream instanceof SSE\Stream\SelfEmittingEventStream) {
            $requestId = $this->stream->getId();
            // We avoid calling GeneralUtility::makeInstance() here
            // since LogManager is a singleton and we would not be
            // able to create a new instance of it.
            $logManager = new Core\Log\LogManager($requestId);
        } else {
            $logManager = Core\Utility\GeneralUtility::makeInstance(Core\Log\LogManager::class);
        }

        return $logManager->getLogger(static::class);
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
