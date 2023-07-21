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

use EliasHaeussler\PHPStanConfig;

$symfonySet = PHPStanConfig\Set\SymfonySet::create()
    ->withConsoleApplicationLoader('Tests/Build/console-application.php')
    ->withContainerXmlPath('var/cache/data/di/DependencyInjectionContainer.xml')
;

return PHPStanConfig\Config\Config::create(__DIR__)
    ->in(
        'Classes',
        'Configuration',
        'Tests',
    )
    ->not(
        'Tests/Acceptance/Support/_generated/*',
        'Tests/Build',
    )
    ->withBaseline()
    ->withBleedingEdge([
        // Avoids errors with $GLOBALS['TYPO3_CONF_VARS'] access
        'explicitMixedForGlobalVariables' => false,
    ])
    ->level(8)
    ->withSets($symfonySet)
    ->toArray()
;
