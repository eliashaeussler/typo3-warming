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

namespace EliasHaeussler\Typo3Warming\Tests\Acceptance\Backend\EventListener;

use Codeception\Attribute;
use Codeception\Example;
use EliasHaeussler\Typo3CodeceptionHelper;
use EliasHaeussler\Typo3Warming\Tests;

/**
 * UrlMetadataListenerCest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class UrlMetadataListenerCest
{
    public function cannotViewLogsForWarmedUpPageAsUnauthorizedEditor(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $modalDialog,
    ): void {
        $I->loginAs('editor.2');

        $this->proceedToReportModal($I, $modalDialog);

        $I->dontSeeElementInDOM(Tests\Acceptance\Support\Enums\Selectors::ReportPanelActionButtonViewLogs->value);
    }

    public function canViewLogsForWarmedUpPageAsAdmin(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $modalDialog,
    ): void {
        $I->loginAs('admin');

        $this->proceedToReportModal($I, $modalDialog);

        $I->waitForElementClickable(Tests\Acceptance\Support\Enums\Selectors::ReportPanelActionButtonViewLogs->value);
        $I->click(Tests\Acceptance\Support\Enums\Selectors::ReportPanelActionButtonViewLogs->value);

        $I->switchToNextTab();
        $I->switchToFrame();
        $I->switchToFrame(Typo3CodeceptionHelper\Enums\Selectors::BackendContentFrame->value);
        $I->waitForText('Administration log');
    }

    public function cannotEditRecordOfWarmedUpPageAsUnauthorizedEditor(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $modalDialog,
    ): void {
        $I->loginAs('editor.4');

        $this->proceedToReportModal($I, $modalDialog);

        $I->dontSeeElementInDOM(Tests\Acceptance\Support\Enums\Selectors::ReportPanelActionButtonEditRecord->value);
    }

    public function canEditRecordOfWarmedUpPageAsAdmin(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $modalDialog,
    ): void {
        $I->loginAs('admin');

        $this->proceedToReportModal($I, $modalDialog);

        $I->waitForElementClickable(Tests\Acceptance\Support\Enums\Selectors::ReportPanelActionButtonEditRecord->value);
        $I->click(Tests\Acceptance\Support\Enums\Selectors::ReportPanelActionButtonEditRecord->value);

        $I->switchToNextTab();
        $I->switchToFrame(Typo3CodeceptionHelper\Enums\Selectors::BackendContentFrame->value);
        $I->waitForText('Edit Page');
    }

    /**
     * @param Example<array{username: string}> $example
     */
    #[Attribute\Examples(username: 'admin')]
    #[Attribute\Examples(username: 'editor.2')]
    public function canShowInformationOfWarmedUpPage(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $modalDialog,
        Example $example,
    ): void {
        $I->loginAs($example['username']);

        $this->proceedToReportModal($I, $modalDialog);

        $I->waitForElementClickable(Tests\Acceptance\Support\Enums\Selectors::ReportPanelActionButtonShowInfo->value);
        $I->click(Tests\Acceptance\Support\Enums\Selectors::ReportPanelActionButtonShowInfo->value);

        $I->seeElement(Tests\Acceptance\Support\Enums\Selectors::InformationModal->value);
    }

    private function proceedToReportModal(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $modalDialog,
    ): void {
        $I->waitForElementClickable(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem->value);
        $I->click(Tests\Acceptance\Support\Enums\Selectors::ToolbarItem->value);

        $modalDialog->canSeeDialog();

        $I->checkOption(Tests\Acceptance\Support\Enums\Selectors::SelectAllCheckbox->value);
        $I->click('Start', Tests\Acceptance\Support\Helper\ModalDialog::$openedModalButtonContainerSelector);

        $modalDialog->canSeeDialog();

        $I->waitForElementNotVisible(Tests\Acceptance\Support\Enums\Selectors::ProgressPlaceholder->value);
        $I->click('Show report', Tests\Acceptance\Support\Helper\ModalDialog::$openedModalButtonContainerSelector);
    }
}
