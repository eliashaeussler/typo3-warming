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

namespace EliasHaeussler\Typo3Warming\EventListener;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming\Http;
use TYPO3\CMS\Core;

/**
 * ClientOptionsListener
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final readonly class ClientOptionsListener
{
    public function __construct(
        private Http\Client\ClientBridge $clientBridge,
    ) {}

    // @todo Enable attribute once support for TYPO3 v12 is dropped
    // #[\TYPO3\CMS\Core\Attribute\AsEventListener('eliashaeussler/typo3-warming/client-options')]
    public function __invoke(CacheWarmup\Event\Config\ConfigResolved $event): void
    {
        $clientOptions = $event->config()->getClientOptions();
        $config = $this->clientBridge->getClientConfig();

        // Overwrite handler if not exists yet
        if (!isset($clientOptions['handler']) && isset($config['handler'])) {
            $clientOptions['handler'] = $config['handler'];
            unset($config['handler']);
        }

        Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($clientOptions, $config);

        $event->config()->setClientOptions($clientOptions);
    }
}
