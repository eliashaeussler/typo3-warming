<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "cache_warmup".
 *
 * Copyright (C) 2021 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3CacheWarmup\Configuration;

use EliasHaeussler\Typo3CacheWarmup\Backend\ContextMenu\ItemProviders\CacheWarmupProvider;
use EliasHaeussler\Typo3CacheWarmup\Backend\ToolbarItems\CacheWarmupToolbarItem;
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
    public const KEY = 'cache_warmup';
    public const NAME = 'CacheWarmup';

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
            ['source' => 'EXT:cache_warmup/Resources/Public/Icons/cache-warmup-page.svg']
        );
        $iconRegistry->registerIcon(
            'cache-warmup-site',
            SvgIconProvider::class,
            ['source' => 'EXT:cache_warmup/Resources/Public/Icons/cache-warmup-site.svg']
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
}
