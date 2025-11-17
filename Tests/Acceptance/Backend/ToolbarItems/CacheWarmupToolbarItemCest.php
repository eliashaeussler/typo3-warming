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

namespace EliasHaeussler\Typo3Warming\Tests\Acceptance\Backend\ToolbarItems;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming\Tests;
use TYPO3\CMS\Core;

/**
 * CacheWarmupToolbarItem
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class CacheWarmupToolbarItemCest
{
    public function canSeeToolbarItemAsAdmin(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->loginAs('admin');
        $I->seeElement(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem);
    }

    public function cannotSeeToolbarItemAsNonPermittedUser(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->loginAs('editor.1');
        $I->dontSeeElement(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem);
    }

    public function canSeeToolbarItemAsPermittedUser(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->loginAs('editor.2');
        $I->seeElement(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem);
    }

    public function cannotSeeToolbarItemIfDisabledInExtensionConfiguration(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ExtensionConfiguration $extensionConfiguration,
    ): void {
        $extensionConfiguration->write('enableToolbar', false);

        $I->wait(2);
        $I->loginAs('admin');
        $I->dontSeeElement(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem);

        $extensionConfiguration->write('enableToolbar', true);
    }

    public function canOpenCacheWarmupModal(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $modalDialog,
    ): void {
        $I->loginAs('admin');

        $I->waitForElementClickable(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem);
        $I->click(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem);

        $modalDialog->canSeeDialog();

        if ((new Core\Information\Typo3Version())->getMajorVersion() >= 14) {
            $modalTitleSelector = Tests\Acceptance\Support\Enums\Selectors::ModalTitle;
        } else {
            // @todo Remove once support for TYPO3 v13 is dropped
            $modalTitleSelector = Tests\Acceptance\Support\Enums\Selectors::ModalTitleLegacy;
        }

        $I->canSee('Cache warmup', $modalTitleSelector);
    }

    public function canSeeAllSitesAndLanguagesInCacheWarmupModalAsAdmin(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $modalDialog,
    ): void {
        $I->loginAs('admin');

        $I->waitForElementClickable(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem);
        $I->click(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem);

        $modalDialog->canSeeDialog();

        $I->seeElement(Tests\Acceptance\Support\Enums\Selectors::CheckboxMainGroup);
        $I->seeElement(Tests\Acceptance\Support\Enums\Selectors::CheckboxMainLanguage0);
        $I->seeElement(Tests\Acceptance\Support\Enums\Selectors::CheckboxMainLanguage1);
        $I->seeElement(Tests\Acceptance\Support\Enums\Selectors::CheckboxRoot2);
    }

    public function canSeeOnlyPermittedSitesAndLanguagesInCacheWarmupModalAsPermittedUser(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $modalDialog,
    ): void {
        $I->loginAs('editor.2');

        $I->waitForElementClickable(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem);
        $I->click(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem);

        $modalDialog->canSeeDialog();

        $I->seeElement(Tests\Acceptance\Support\Enums\Selectors::CheckboxMainGroup);
        $I->seeElement(Tests\Acceptance\Support\Enums\Selectors::CheckboxMainLanguage0);
        $I->dontSeeElement(Tests\Acceptance\Support\Enums\Selectors::CheckboxMainLanguage1);
        $I->dontSeeElement(Tests\Acceptance\Support\Enums\Selectors::CheckboxRoot2);
    }

    public function canRunCacheWarmupFromModal(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $modalDialog,
    ): void {
        $I->loginAs('admin');

        $I->waitForElementClickable(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem);
        $I->click(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem);

        $modalDialog->canSeeDialog();

        $I->checkOption(Tests\Acceptance\Support\Enums\Selectors::SelectAllCheckbox);
        $I->click('Start', Tests\Acceptance\Support\Helper\ModalDialog::$openedModalButtonContainerSelector);

        $modalDialog->canSeeDialog();

        if ((new Core\Information\Typo3Version())->getMajorVersion() >= 14) {
            $modalTitleSelector = Tests\Acceptance\Support\Enums\Selectors::ModalTitle;
        } else {
            // @todo Remove once support for TYPO3 v13 is dropped
            $modalTitleSelector = Tests\Acceptance\Support\Enums\Selectors::ModalTitleLegacy;
        }

        $I->canSee('Cache warmup failed', $modalTitleSelector);
        $I->waitForElementNotVisible(Tests\Acceptance\Support\Enums\Selectors::ProgressPlaceholder);

        $modalDialog->clickButtonInDialog('Close');
    }

    public function canChangeCacheWarmupSettings(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ExtensionConfiguration $extensionConfiguration,
        Tests\Acceptance\Support\Helper\ModalDialog $modalDialog,
    ): void {
        $I->loginAs('admin');

        $I->waitForElementClickable(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem);
        $I->click(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem);

        $modalDialog->canSeeDialog();

        $configuredLimit = $extensionConfiguration->read('limit');
        $configuredStrategy = $extensionConfiguration->read('strategy');

        $I->seeInField(Tests\Acceptance\Support\Enums\Selectors::SettingsLimit, $configuredLimit);
        $I->fillField(Tests\Acceptance\Support\Enums\Selectors::SettingsLimit, 1);

        $I->seeOptionIsSelected(Tests\Acceptance\Support\Enums\Selectors::SettingsStrategy, $configuredStrategy);
        $I->selectOption(
            Tests\Acceptance\Support\Enums\Selectors::SettingsStrategy,
            CacheWarmup\Crawler\Strategy\SortByPriorityStrategy::getName(),
        );

        $I->checkOption(Tests\Acceptance\Support\Enums\Selectors::SelectAllCheckbox);
        $I->click('Start', Tests\Acceptance\Support\Helper\ModalDialog::$openedModalButtonContainerSelector);

        $modalDialog->canSeeDialog();

        $I->waitForElementNotVisible(Tests\Acceptance\Support\Enums\Selectors::ProgressPlaceholder);
        $I->see('1', Tests\Acceptance\Support\Enums\Selectors::ProgressCounterTotal);

        $modalDialog->clickButtonInDialog('Close');
    }

    public function canCopyUserAgentToClipboard(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $modalDialog,
    ): void {
        $I->loginAs('admin');

        $I->waitForElementClickable(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem);
        $I->click(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem);

        $modalDialog->canSeeDialog();

        $I->click('Copy to clipboard');
        $I->waitForText('Copied', 5, Tests\Acceptance\Support\Enums\Selectors::UserAgentCopyButton);

        $clipboard = $I->executeJS('return await navigator.clipboard.readText();');

        $I->assertStringStartsWith('TYPO3/tx_warming_crawler', $clipboard);
    }
}
