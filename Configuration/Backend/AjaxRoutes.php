<?php

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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

return [
    'tx_warming_cache_warmup' => [
        'path' => '/warming/cache-warmup',
        'target' => \EliasHaeussler\Typo3Warming\Controller\CacheWarmupController::class,
    ],
    'tx_warming_cache_warmup_legacy' => [
        'path' => '/warming/cache-warmup-legacy',
        'target' => \EliasHaeussler\Typo3Warming\Controller\CacheWarmupLegacyController::class,
    ],
    'tx_warming_fetch_sites' => [
        'path' => '/warming/fetch-sites',
        'target' => \EliasHaeussler\Typo3Warming\Controller\FetchSitesController::class,
    ],
];
