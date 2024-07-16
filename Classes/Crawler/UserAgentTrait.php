<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Crawler;

use EliasHaeussler\Typo3Warming\Configuration\Configuration;
use GuzzleHttp\Psr7\Request;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * UserAgentTrait
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
trait UserAgentTrait
{
    protected function applyUserAgentHeader(Request $request): Request
    {
        /** @var Request $request */
        $request = $request->withAddedHeader('User-Agent', $this->getUserAgent());

        return $request;
    }

    protected function getUserAgent(): string
    {
        $configuration = GeneralUtility::makeInstance(Configuration::class);

        return $configuration->getUserAgent();
    }
}
