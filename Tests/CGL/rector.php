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

use EliasHaeussler\RectorConfig\Config\Config;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Symfony\DependencyInjection\Rector\Trait_\TraitGetByTypeToInjectRector;
use Rector\Symfony\Symfony73\Rector\Class_\CommandHelpToAttributeRector;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig): void {
    $rootPath = dirname(__DIR__, 2);

    require $rootPath . '/.Build/vendor/autoload.php';

    Config::create($rectorConfig, PhpVersion::PHP_82)
        ->in(
            $rootPath . '/Classes',
            $rootPath . '/Configuration',
            $rootPath . '/Tests',
        )
        ->not(
            $rootPath . '/.Build/*',
            $rootPath . '/.ddev/*',
            $rootPath . '/.github/*',
            $rootPath . '/config/*',
            $rootPath . '/Resources/Private/Frontend/*',
            $rootPath . '/Resources/Private/Libs/*',
            $rootPath . '/Tests/Acceptance/Support/_generated/*',
            $rootPath . '/Tests/Build/Configuration/*',
            $rootPath . '/Tests/CGL/vendor/*',
            $rootPath . '/var/*',
        )
        ->withPHPUnit()
        ->withSymfony()
        ->withTYPO3()
        ->skip(AnnotationToAttributeRector::class, [
            $rootPath . '/Classes/Extension.php',
            $rootPath . '/Classes/Sitemap/Provider/DefaultProvider.php',
            $rootPath . '/Classes/Sitemap/Provider/PageTypeProvider.php',
            $rootPath . '/Classes/Sitemap/Provider/RobotsTxtProvider.php',
            $rootPath . '/Classes/Sitemap/Provider/SiteProvider.php',
            $rootPath . '/Tests/Build/DependencyInjection/CompilerPass/ContainerBuilderDebugDumpPass.php',
            $rootPath . '/Tests/Build/DependencyInjection/CompilerPass/PublicServicePass.php',
        ])
        ->skip(ClassPropertyAssignToConstructorPromotionRector::class, [
            // We cannot use CPP for properties that are declared in abstract classes
            $rootPath . '/Tests/Acceptance/Support/Helper/ModalDialog.php',
            $rootPath . '/Tests/Acceptance/Support/Helper/PageTree.php',
        ])
        ->skip(CommandHelpToAttributeRector::class, [
            $rootPath . '/Classes/Command/WarmupCommand.php',
        ])
        ->skip(TraitGetByTypeToInjectRector::class, [
            $rootPath . '/Tests/Functional/SiteTrait.php',
        ])
        ->apply()
    ;

    $rectorConfig->importNames(false, false);
};
