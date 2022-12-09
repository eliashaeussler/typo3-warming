<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
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
    'directories' => [
        '.build',
        '.git',
        '.github',
        'bin',
        'build',
        'public',
        'resources\\/private\\/frontend',
        'resources\\/private\\/libs\\/build',
        'tailor-version-upload',
        'tests',
        'vendor',
    ],
    'files' => [
        'DS_Store',
        'captainhook.json',
        'codecov.yml',
        'CODEOWNERS',
        'composer.lock',
        'crowdin.yaml',
        'dependency-checker.json',
        'docker-compose.yml',
        'editorconfig',
        'editorconfig-lint.php',
        'gitattributes',
        'gitignore',
        'packaging_exclude.php',
        'php-cs-fixer.php',
        'phpstan.neon',
        'phpunit.ci.xml',
        'phpunit.xml',
        'rector.php',
    ],
];
