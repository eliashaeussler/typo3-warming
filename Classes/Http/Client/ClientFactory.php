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

namespace EliasHaeussler\Typo3Warming\Http\Client;

use GuzzleHttp\ClientInterface;
use TYPO3\CMS\Core;

/**
 * ClientFactory
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ClientFactory
{
    public function __construct(
        private readonly Core\Http\Client\GuzzleClientFactory $guzzleClientFactory,
    ) {}

    /**
     * @param array<string, mixed> $config
     */
    public function get(array $config = []): ClientInterface
    {
        // Early return if no client config is set
        if ($config === []) {
            return $this->guzzleClientFactory->getClient();
        }

        // Merge initial TYPO3 config with actual client config
        $initialConfig = $GLOBALS['TYPO3_CONF_VARS']['HTTP'] ??= [];
        Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TYPO3_CONF_VARS']['HTTP'], $config);

        // Initialize client and restore initial config
        try {
            $client = $this->guzzleClientFactory->getClient();
        } finally {
            $GLOBALS['TYPO3_CONF_VARS']['HTTP'] = $initialConfig;
        }

        return $client;
    }
}
