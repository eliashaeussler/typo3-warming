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

namespace EliasHaeussler\Typo3Warming\Log\Processor;

use TYPO3\CMS\Core;

/**
 * RequestIdProcessor
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class RequestIdProcessor implements Core\Log\Processor\ProcessorInterface
{
    public function processLogRecord(Core\Log\LogRecord $logRecord): Core\Log\LogRecord
    {
        $requestId = $logRecord->getData()['request_id'] ?? null;

        // Override request id with original request id, if available (this reflects the
        // "default" state where cache warmup is requested from backend context, where
        // a new request id is generated and attached to the warmup request object
        if (is_string($requestId)) {
            $logRecord->setRequestId($requestId);
        }

        return $logRecord;
    }
}
