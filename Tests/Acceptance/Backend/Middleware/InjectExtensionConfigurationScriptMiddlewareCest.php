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

namespace EliasHaeussler\Typo3Warming\Tests\Acceptance\Backend\Middleware;

use EliasHaeussler\Typo3Warming\Tests;
use TYPO3\CMS\Core;

/**
 * InjectExtensionConfigurationScriptMiddlewareCest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class InjectExtensionConfigurationScriptMiddlewareCest
{
    public function cannotSeeInjectedScriptOnTypo3V12(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        if ((new Core\Information\Typo3Version())->getMajorVersion() >= 12) {
            $I->markTestSkipped('This test is only relevant on TYPO3 v12.');
        }

        $I->loginAs('admin');
        $I->dontSeeElementInDOM('#tx-warming-script-inject');
    }

    public function cannotSeeInjectedScriptOnInsufficientPrivileges(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        if ((new Core\Information\Typo3Version())->getMajorVersion() < 13) {
            $I->markTestSkipped('This test is only relevant on TYPO3 v13.');
        }

        $I->loginAs('editor.1');
        $I->dontSeeElementInDOM('#tx-warming-script-inject');
    }

    public function canSeeInjectedScriptOnTypo3V13(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        if ((new Core\Information\Typo3Version())->getMajorVersion() < 13) {
            $I->markTestSkipped('This test is only relevant on TYPO3 v13.');
        }

        $I->loginAs('admin');
        $I->seeElementInDOM('#tx-warming-script-inject');
    }
}
