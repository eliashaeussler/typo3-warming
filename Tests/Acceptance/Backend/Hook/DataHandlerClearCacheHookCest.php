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

namespace EliasHaeussler\Typo3Warming\Tests\Acceptance\Backend\Hook;

use EliasHaeussler\Typo3Warming\Tests;

/**
 * DataHandlerClearCacheHookCest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class DataHandlerClearCacheHookCest
{
    public function _before(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\PageTree $pageTree,
    ): void {
        $I->loginAs('admin');

        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::BackendPageModule->value);
        $I->switchToIFrame();

        $pageTree->openContextMenu(['Main']);
        $pageTree->selectInContextMenu(['Edit']);

        $I->switchToIFrame('list_frame');
    }

    public function warmupPageCacheIsNotExecutedWhenDisabledViaExtensionConfiguration(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ExtensionConfiguration $extensionConfiguration,
    ): void {
        $extensionConfiguration->write('runAfterCacheClear', false);

        $I->click('Save');
        $I->switchToFrame();

        $I->see('Record saved', Tests\Acceptance\Support\Enums\Selectors::Notification->value);
        $I->dontSee('Cache warmup failed', Tests\Acceptance\Support\Enums\Selectors::Notification->value);

        $extensionConfiguration->write('runAfterCacheClear', true);
    }

    public function warmupPageCacheIsExecutedWhenEnabledViaExtensionConfiguration(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ExtensionConfiguration $extensionConfiguration,
    ): void {
        $extensionConfiguration->write('runAfterCacheClear', true);

        $I->click('Save');
        $I->switchToFrame();

        $I->see('Record saved', Tests\Acceptance\Support\Enums\Selectors::Notification->value);
        $I->see('Cache warmup failed', Tests\Acceptance\Support\Enums\Selectors::Notification->value);
    }

    public function warmupPageCacheIsExecutedForPageLocalization(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ExtensionConfiguration $extensionConfiguration,
    ): void {
        $extensionConfiguration->write('runAfterCacheClear', true);

        $I->selectOption(Tests\Acceptance\Support\Enums\Selectors::RecordLanguageSelector->value, 'German');

        $I->click('Save');
        $I->switchToFrame();

        $I->see('Record saved', Tests\Acceptance\Support\Enums\Selectors::Notification->value);
        $I->see('Cache warmup failed', Tests\Acceptance\Support\Enums\Selectors::Notification->value);
        $I->see('Main L=1 [5]', Tests\Acceptance\Support\Enums\Selectors::Notification->value);
    }
}
