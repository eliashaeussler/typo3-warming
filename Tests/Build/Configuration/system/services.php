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

use CuyZ\Valinor;
use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming\Tests;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;

return static function (
    DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator,
    DependencyInjection\ContainerBuilder $containerBuilder,
): void {
    $cachePath = Core\Core\Environment::getVarPath() . '/cache/data/di/DependencyInjectionContainer.xml';

    // Configure public services by pattern
    foreach ([
        '/^EliasHaeussler\\\\Typo3Warming\\\\/',
        '/^cache\\.runtime$/',
        '/^cache\\.warming$/',
    ] as $definitionPattern) {
        $containerBuilder->addCompilerPass(
            new Tests\Build\DependencyInjection\CompilerPass\PublicServicePass($definitionPattern),
            DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_REMOVING,
            200,
        );
    }

    // Configure public services by class name
    foreach ([
        Core\Configuration\ExtensionConfiguration::class,
        Core\Site\SiteFinder::class,
        CacheWarmup\Crawler\CrawlerFactory::class,
        CacheWarmup\Crawler\Strategy\CrawlingStrategyFactory::class,
        Valinor\Mapper\TreeMapper::class,
        Typo3SitemapLocator\Cache\SitemapsCache::class,
        Typo3SitemapLocator\Http\Client\ClientFactory::class,
    ] as $className) {
        $containerBuilder->addCompilerPass(
            Tests\Build\DependencyInjection\CompilerPass\PublicServicePass::fromClass($className),
            DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_REMOVING,
            200,
        );
    }

    $containerBuilder->addCompilerPass(
        new Tests\Build\DependencyInjection\CompilerPass\ContainerBuilderDebugDumpPass($cachePath),
        DependencyInjection\Compiler\PassConfig::TYPE_AFTER_REMOVING,
        100,
    );

    // Additional services
    $containerConfigurator->services()
        ->set(Tests\Unit\Fixtures\DummyCrawlingStrategy::class)
        ->autoconfigure()
    ;
};
