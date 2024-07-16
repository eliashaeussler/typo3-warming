<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Configuration;

use EliasHaeussler\Typo3Warming\Backend\ContextMenu\ItemProviders\CacheWarmupProvider;
use EliasHaeussler\Typo3Warming\Backend\ToolbarItems\CacheWarmupToolbarItem;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Extension
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @codeCoverageIgnore
 */
final class Extension
{
    public const KEY = 'warming';
    public const NAME = 'Warming';

    /**
     * Register context menu item provider.
     *
     * FOR USE IN ext_localconf.php ONLY.
     */
    public static function registerContextMenuProvider(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['BE']['ContextMenu']['ItemProviders'][1619185993] = CacheWarmupProvider::class;
    }

    /**
     * Register custom icons.
     *
     * FOR USE IN ext_localconf.php ONLY.
     */
    public static function registerIcons(): void
    {
        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
        $iconRegistry->registerIcon(
            'cache-warmup-page',
            SvgIconProvider::class,
            ['source' => 'EXT:warming/Resources/Public/Icons/cache-warmup-page.svg']
        );
        $iconRegistry->registerIcon(
            'cache-warmup-site',
            SvgIconProvider::class,
            ['source' => 'EXT:warming/Resources/Public/Icons/cache-warmup-site.svg']
        );
    }

    /**
     * Register cache warmup toolbar item.
     *
     * FOR USE IN ext_localconf.php ONLY.
     */
    public static function registerToolbarItem(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['BE']['toolbarItems'][1619165047] = CacheWarmupToolbarItem::class;
    }

    /**
     * Register custom styles for Backend.
     *
     * FOR USE IN ext_tables.php ONLY.
     */
    public static function registerCustomStyles(): void
    {
        $GLOBALS['TBE_STYLES']['skins'][self::KEY] = [
            'name' => self::KEY,
            'stylesheetDirectories' => [
                'css' => 'EXT:warming/Resources/Public/Css/Backend',
            ],
        ];
    }

    /**
     * Load additional libraries provided by PHAR file (only to be used in non-Composer-mode).
     *
     * FOR USE IN ext_localconf.php AND NON-COMPOSER-MODE ONLY.
     */
    public static function loadVendorLibraries(): void
    {
        // Vendor libraries are already available in Composer mode
        if (Environment::isComposerMode()) {
            return;
        }

        $vendorPharFile = GeneralUtility::getFileAbsFileName('EXT:warming/Resources/Private/Libs/vendors.phar');

        if (file_exists($vendorPharFile)) {
            require 'phar://' . $vendorPharFile . '/vendor/autoload.php';
        }
    }
}
