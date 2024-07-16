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

use EliasHaeussler\CacheWarmup\Crawler\ConfigurableCrawlerInterface;
use GuzzleHttp\ClientInterface;
use TYPO3\CMS\Core\Http\Client\GuzzleClientFactory;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * ConfigurableClientTrait
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
trait ConfigurableClientTrait
{
    protected function initializeClient(): ClientInterface
    {
        $clientConfig = $this->getClientConfig();

        // Early return if no client config is set
        if ($clientConfig === []) {
            return GuzzleClientFactory::getClient();
        }

        // Merge initial TYPO3 config with actual client config
        $initialConfig = $GLOBALS['TYPO3_CONF_VARS']['HTTP'];
        ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TYPO3_CONF_VARS']['HTTP'], $clientConfig);

        // Initialize client and restore initial config
        try {
            $client = GuzzleClientFactory::getClient();
        } finally {
            $GLOBALS['TYPO3_CONF_VARS']['HTTP'] = $initialConfig;
        }

        return $client;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getClientConfig(): array
    {
        if (!($this instanceof ConfigurableCrawlerInterface)) {
            return [];
        }

        /* @phpstan-ignore-next-line */
        return $this->options['client_config'] ?? [];
    }

    public function setOptions(array $options): void
    {
        parent::setOptions($options);

        // Re-initialize client with updated client config
        $this->client = $this->initializeClient();
    }
}
