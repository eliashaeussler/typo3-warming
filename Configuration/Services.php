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
use EliasHaeussler\Typo3Warming\Sitemap;
use Symfony\Component\DependencyInjection;

return static function (
    DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator,
    DependencyInjection\ContainerBuilder $container,
): void {
    $container->registerForAutoconfiguration(CacheWarmup\Crawler\Strategy\CrawlingStrategy::class)
        ->addTag('warming.crawling_strategy');
    $container->registerForAutoconfiguration(Sitemap\Provider\Provider::class)
        ->addTag('warming.sitemap_provider');

    // External services
    $services = $containerConfigurator->services();
    $services->set(CacheWarmup\Crawler\CrawlerFactory::class);
};
