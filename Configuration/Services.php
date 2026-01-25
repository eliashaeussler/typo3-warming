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

namespace EliasHaeussler\Typo3Warming\DependencyInjection;

use EliasHaeussler\CacheWarmup;
use Symfony\Component\DependencyInjection;
use Symfony\Component\ExpressionLanguage;

return static function (
    DependencyInjection\ContainerBuilder $container,
    DependencyInjection\Loader\Configurator\ContainerConfigurator $configurator,
): void {
    $container->addCompilerPass(new CrawlingStrategyCompilerPass());
    $container->registerForAutoconfiguration(CacheWarmup\Crawler\Strategy\CrawlingStrategy::class)
        ->addTag(CrawlingStrategyCompilerPass::TAG_NAME)
    ;

    $configurator->services()
        ->set(CacheWarmup\Http\Client\ClientFactory::class)
        ->autowire()
        ->arg(
            '$defaults',
            new ExpressionLanguage\Expression(
                'service("EliasHaeussler\\\\Typo3SitemapLocator\\\\Http\\\\Client\\\\ClientFactory").getClientConfig()',
            ),
        )
    ;
};
