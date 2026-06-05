<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2026 Elias Häußler <elias@haeussler.dev>
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

use Composer\Autoload;
use Symfony\Component\Filesystem;
use TYPO3\CMS\Core;

$rootPath = dirname(__DIR__, 2);
/** @var Autoload\ClassLoader $classLoader */
$classLoader = require $rootPath . '/.Build/vendor/autoload.php';

// Make sure no DI container is available before dumping a new one
$filesystem = new Filesystem\Filesystem();
$filesystem->remove($rootPath . '/var/cache/code/di');

// Build service container
Core\Core\SystemEnvironmentBuilder::run(0, Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_CLI);
Core\Core\Bootstrap::init($classLoader);
