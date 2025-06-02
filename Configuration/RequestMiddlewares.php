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

use EliasHaeussler\Typo3Warming\Middleware;

return [
    'backend' => [
        'eliashaeussler/typo3-warming/inject-extension-configuration-script' => [
            'target' => Middleware\InjectExtensionConfigurationScriptMiddleware::class,
            'after' => [
                'typo3/cms-backend/backend-routing',
                'typo3/cms-backend/csp-headers',
            ],
        ],
    ],
];
