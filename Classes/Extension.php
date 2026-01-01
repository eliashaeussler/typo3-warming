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

namespace EliasHaeussler\Typo3Warming;

use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;

/**
 * Extension
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @codeCoverageIgnore
 * @internal
 */
final class Extension
{
    public const KEY = 'warming';
    public const NAME = 'Warming';

    /**
     * Register additional caches.
     *
     * FOR USE IN ext_localconf.php.
     */
    public static function registerCaches(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][self::KEY] = [
            'backend' => Core\Cache\Backend\SimpleFileBackend::class,
            'frontend' => Core\Cache\Frontend\PhpFrontend::class,
        ];
    }

    /**
     * Register additional form data providers.
     *
     * FOR USE IN ext_localconf.php ONLY.
     */
    public static function registerFormDataProviders(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][Form\FormDataProvider\SimulateLogPageRow::class] = [
            'before' => [
                Backend\Form\FormDataProvider\DatabaseParentPageRow::class,
            ],
        ];
    }

    /**
     * Register global TypoScript setup & constants.
     *
     * FOR USE IN ext_localconf.php ONLY.
     */
    public static function registerTypoScript(): void
    {
        Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('
module.tx_warming {
    view {
        templateRootPaths {
            0 = EXT:warming/Resources/Private/Templates/
            10 = {$module.tx_warming.view.templateRootPath}
        }
        partialRootPaths {
            0 = EXT:warming/Resources/Private/Partials/
            10 = {$module.tx_warming.view.partialRootPath}
        }
    }
}
        ');

        Core\Utility\ExtensionManagementUtility::addTypoScriptConstants('
# customcategory=warming=Warming
# customsubcategory=view=View

module.tx_warming {
    view {
        # cat=warming/view/10; type=string; label=Path to template root
        templateRootPath =
        # cat=warming/view/20; type=string; label=Path to template partials
        partialRootPath =
    }
}
        ');
    }

    /**
     * Register custom styles for Backend.
     *
     * FOR USE IN ext_tables.php ONLY.
     */
    public static function registerCustomStyles(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets'][self::KEY] = 'EXT:warming/Resources/Public/Css';
    }
}
