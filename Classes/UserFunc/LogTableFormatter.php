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

namespace EliasHaeussler\Typo3Warming\UserFunc;

use EliasHaeussler\Typo3Warming\Domain;
use TYPO3\CMS\Backend;

/**
 * LogTableFormatter
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final class LogTableFormatter
{
    private const TEMPLATE = '[%s] @ %s > %s';

    /**
     * @param array{row: array{uid: int}} $parameters
     */
    public function formatTitle(array &$parameters): void
    {
        /** @var array{request_id: string, date: int, url: string}|null $record */
        $record = Backend\Utility\BackendUtility::getRecord(Domain\Model\Log::TABLE_NAME, $parameters['row']['uid']);

        if ($record !== null) {
            $date = \DateTimeImmutable::createFromFormat('U', (string)$record['date']);
            $parameters['title'] = sprintf(
                self::TEMPLATE,
                $record['request_id'],
                $date !== false ? $date->format('d.m.Y H:i:s') : '(unknown)',
                $record['url'],
            );
        }
    }
}
