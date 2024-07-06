<?php

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

/** @noinspection PhpUndefinedVariableInspection */
$EM_CONF[$_EXTKEY] = [
    'title' => 'Warming',
    'description' => 'Warms up Frontend caches based on an XML sitemap. Cache warmup can be triggered via TYPO3 backend or using a console command. Supports multiple languages and custom crawler implementations.',
    'category' => 'be',
    'version' => '2.2.0',
    'state' => 'stable',
    'author' => 'Elias Häußler',
    'author_email' => 'elias@haeussler.dev',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.2-13.2.99',
            'php' => '8.1.0-8.3.99',
            'sitemap_locator' => '0.1.0-0.1.99',
        ],
    ],
];
