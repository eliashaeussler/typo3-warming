<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Tests\Hooks;

use DG\BypassFinals;
use PHPUnit\Runner\BeforeTestHook;

/**
 * BypassFinalHook
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class BypassFinalHook implements BeforeTestHook
{
    /**
     * @inheritDoc
     * @see https://tomasvotruba.com/blog/2019/03/28/how-to-mock-final-classes-in-phpunit/
     */
    public function executeBeforeTest(string $test): void
    {
        BypassFinals::enable();
    }
}
