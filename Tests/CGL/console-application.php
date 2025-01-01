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

use Composer\Autoload\ClassLoader;
use EliasHaeussler\Typo3Warming\Command;
use Symfony\Component\Console;
use TYPO3\CMS\Core;

/** @var ClassLoader $classLoader */
$classLoader = require \dirname(__DIR__, 2) . '/.Build/vendor/autoload.php';

// Move project's class loader in front of PHPStan's class loader
$classLoader->register(true);

// Build environment and initialize the service container
Core\Core\SystemEnvironmentBuilder::run(0, Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_CLI);
$container = Core\Core\Bootstrap::init($classLoader);

// Disable TYPO3's phar stream wrapper to allow execution of PHPStan
stream_wrapper_restore('phar');

// Initialize application and add command
$application = new Console\Application();
$application->add($container->get(Command\ShowUserAgentCommand::class));
$application->add($container->get(Command\WarmupCommand::class));

return $application;
