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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming\Tests;
use Symfony\Component\DependencyInjection as SymfonyDI;
use TYPO3\CMS\Core;

return static function (SymfonyDI\ContainerBuilder $containerBuilder): void {
    $cachePath = Core\Core\Environment::getVarPath() . '/cache/data/di/DependencyInjectionContainer.xml';

    $containerBuilder->addCompilerPass(
        new Tests\Build\DependencyInjection\CompilerPass\ContainerBuilderDebugDumpPass($cachePath),
        SymfonyDI\Compiler\PassConfig::TYPE_AFTER_REMOVING,
        100,
    );
    $containerBuilder->addCompilerPass(
        new Tests\Build\DependencyInjection\CompilerPass\PublicServicePass('/^EliasHaeussler\\\\Typo3Warming\\\\/'),
        SymfonyDI\Compiler\PassConfig::TYPE_BEFORE_REMOVING,
        200,
    );
    $containerBuilder->addCompilerPass(
        new Tests\Build\DependencyInjection\CompilerPass\PublicServicePass('/^cache\\.warming$/'),
        SymfonyDI\Compiler\PassConfig::TYPE_BEFORE_REMOVING,
        200,
    );
    $containerBuilder->addCompilerPass(
        Tests\Build\DependencyInjection\CompilerPass\PublicServicePass::fromClass(Core\Configuration\ExtensionConfiguration::class),
        SymfonyDI\Compiler\PassConfig::TYPE_BEFORE_REMOVING,
        200,
    );
    $containerBuilder->addCompilerPass(
        Tests\Build\DependencyInjection\CompilerPass\PublicServicePass::fromClass(Core\Site\SiteFinder::class),
        SymfonyDI\Compiler\PassConfig::TYPE_BEFORE_REMOVING,
        200,
    );
    $containerBuilder->addCompilerPass(
        Tests\Build\DependencyInjection\CompilerPass\PublicServicePass::fromClass(CacheWarmup\Crawler\CrawlerFactory::class),
        SymfonyDI\Compiler\PassConfig::TYPE_BEFORE_REMOVING,
        200,
    );
};
