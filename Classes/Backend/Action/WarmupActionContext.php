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

namespace EliasHaeussler\Typo3Warming\Backend\Action;

/**
 * WarmupActionContext
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
enum WarmupActionContext: string
{
    case Page = 'cacheWarmupPage';
    case Site = 'cacheWarmupSite';

    public function label(): string
    {
        return match ($this) {
            self::Page => 'LLL:EXT:warming/Resources/Private/Language/locallang.xlf:cacheWarmupAction.context.page',
            self::Site => 'LLL:EXT:warming/Resources/Private/Language/locallang.xlf:cacheWarmupAction.context.site',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Page => 'cache-warmup-page',
            self::Site => 'cache-warmup-site',
        };
    }

    public function callbackAction(): string
    {
        return match ($this) {
            self::Page => 'warmupPageCache',
            self::Site => 'warmupSiteCache',
        };
    }
}
