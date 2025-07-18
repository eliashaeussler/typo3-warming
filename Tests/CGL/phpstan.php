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

use EliasHaeussler\PHPStanConfig;
use TYPO3\CMS\Backend;

$rootPath = dirname(__DIR__, 2);
$symfonySet = PHPStanConfig\Set\SymfonySet::create()
    ->withConsoleApplicationLoader(__DIR__ . '/console-application.php')
    ->withContainerXmlPath($rootPath . '/var/cache/data/di/DependencyInjectionContainer.xml')
;
$typo3Set = PHPStanConfig\Set\TYPO3Set::create()
    ->withCustomRequestAttribute('route', Backend\Routing\Route::class)
;

return PHPStanConfig\Config\Config::create($rootPath)
    ->in(
        'Classes',
        'Configuration',
        'Tests',
    )
    ->not(
        'Tests/Acceptance/Support/_generated/*',
        'Tests/Build',
        'Tests/CGL',
    )
    ->bootstrapFiles(
        $rootPath . '/.Build/vendor/autoload.php'
    )
    ->withBaseline()
    ->withBleedingEdge([
        'internalTag' => false,
    ])
    ->with($rootPath . '/.Build/vendor/cuyz/valinor/qa/PHPStan/valinor-phpstan-suppress-pure-errors.php')
    ->level(8)
    ->withSets($symfonySet, $typo3Set)
    ->toArray()
;
