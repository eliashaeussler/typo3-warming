<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2024 Elias Häußler <elias@haeussler.dev>
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
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Symfony\Symfony53\Rector\Class_\CommandDescriptionToPropertyRector;
use Rector\Symfony\Symfony61\Rector\Class_\CommandPropertyToAttributeRector;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig): void {
    Config::create($rectorConfig, PhpVersion::PHP_81)
        ->in(
            __DIR__ . '/Classes',
            __DIR__ . '/Configuration',
            __DIR__ . '/Tests',
        )
        ->not(
            __DIR__ . '/.Build/*',
            __DIR__ . '/.ddev/*',
            __DIR__ . '/.github/*',
            __DIR__ . '/config/*',
            __DIR__ . '/Resources/Private/Frontend/*',
            __DIR__ . '/Resources/Private/Libs/*',
            __DIR__ . '/Tests/Acceptance/Support/_generated/*',
            __DIR__ . '/Tests/Build/Configuration/*',
            __DIR__ . '/var/*',
        )
        ->withPHPUnit()
        ->withSymfony()
        ->withTYPO3()
        ->skip(AnnotationToAttributeRector::class, [
            __DIR__ . '/Classes/Extension.php',
            __DIR__ . '/Classes/Sitemap/Provider/DefaultProvider.php',
            __DIR__ . '/Classes/Sitemap/Provider/PageTypeProvider.php',
            __DIR__ . '/Classes/Sitemap/Provider/RobotsTxtProvider.php',
            __DIR__ . '/Classes/Sitemap/Provider/SiteProvider.php',
            __DIR__ . '/Tests/Build/DependencyInjection/CompilerPass/ContainerBuilderDebugDumpPass.php',
            __DIR__ . '/Tests/Build/DependencyInjection/CompilerPass/PublicServicePass.php',
        ])
        ->skip(ClassPropertyAssignToConstructorPromotionRector::class, [
            // We cannot use CPP for properties that are declared in abstract classes
            __DIR__ . '/Tests/Acceptance/Support/Helper/ModalDialog.php',
            __DIR__ . '/Tests/Acceptance/Support/Helper/PageTree.php',
        ])
        ->skip(CommandDescriptionToPropertyRector::class)
        ->skip(CommandPropertyToAttributeRector::class)
        ->skip(FinalizeClassesWithoutChildrenRector::class, [
            // We keep domain models and repositories open for extensions
            __DIR__ . '/Classes/Domain/Model/*',
            __DIR__ . '/Classes/Domain/Repository/*',
        ])
        ->apply()
    ;

    $rectorConfig->importNames(false, false);
};
