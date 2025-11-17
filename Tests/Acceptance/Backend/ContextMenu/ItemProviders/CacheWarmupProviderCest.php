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

namespace EliasHaeussler\Typo3Warming\Tests\Acceptance\Backend\ContextMenu\ItemProviders;

use EliasHaeussler\Typo3Warming\Tests;
use TYPO3\CMS\Core;

/**
 * CacheWarmupProviderCest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class CacheWarmupProviderCest
{
    public function cannotSeeContextMenuOnSystemRootPage(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\PageTree $pageTree,
    ): void {
        $I->loginAs('admin');

        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::BackendPageModule);
        $I->switchToIFrame();

        $pageTree->openContextMenu(['EXT:warming']);

        $I->dontSee('Warmup cache for this page', Tests\Acceptance\Support\Enums\Selectors::ContextMenu);
        $I->dontSee('Warmup all caches', Tests\Acceptance\Support\Enums\Selectors::ContextMenu);
    }

    public function canSeeContextMenuItemsAsAdmin(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\PageTree $pageTree,
    ): void {
        $I->loginAs('admin');

        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::BackendPageModule);
        $I->switchToIFrame();

        $pageTree->openContextMenu(['Main']);

        $I->see('Warmup cache for this page', Tests\Acceptance\Support\Enums\Selectors::ContextMenu);
        $I->see('Warmup all caches', Tests\Acceptance\Support\Enums\Selectors::ContextMenu);
    }

    public function cannotSeeContextMenuItemsAsNonPermittedUser(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\PageTree $pageTree,
    ): void {
        $I->loginAs('editor.1');

        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::BackendPageModule);
        $I->switchToIFrame();

        $pageTree->openContextMenu(['Main']);

        $I->dontSee('Warmup cache for this page', Tests\Acceptance\Support\Enums\Selectors::ContextMenu);
        $I->dontSee('Warmup all caches', Tests\Acceptance\Support\Enums\Selectors::ContextMenu);
    }

    public function canSeeContextMenuItemsAsPermittedUser(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\PageTree $pageTree,
    ): void {
        $I->loginAs('editor.2');

        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::BackendPageModule);
        $I->switchToIFrame();

        $pageTree->openContextMenu(['Main']);

        $I->see('Warmup cache for this page', Tests\Acceptance\Support\Enums\Selectors::ContextMenu);
        $I->see('Warmup all caches', Tests\Acceptance\Support\Enums\Selectors::ContextMenu);
    }

    public function cannotSeeContextMenuItemsIfDisabledInExtensionConfiguration(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ExtensionConfiguration $extensionConfiguration,
        Tests\Acceptance\Support\Helper\PageTree $pageTree,
    ): void {
        $extensionConfiguration->write('enablePageTree', false);

        $I->loginAs('admin');

        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::BackendPageModule);
        $I->switchToIFrame();

        $pageTree->openContextMenu(['Main']);

        $I->dontSee('Warmup cache for this page', Tests\Acceptance\Support\Enums\Selectors::ContextMenu);
        $I->dontSee('Warmup all caches', Tests\Acceptance\Support\Enums\Selectors::ContextMenu);

        $extensionConfiguration->write('enablePageTree', true);
    }

    public function cannotSeeContextMenuItemForSiteIfConfiguredSitemapIsInvalid(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\PageTree $pageTree,
    ): void {
        $I->loginAs('admin');

        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::BackendPageModule);
        $I->switchToIFrame();

        $pageTree->openContextMenu(['Root 2']);

        $I->see('Warmup cache for this page', Tests\Acceptance\Support\Enums\Selectors::ContextMenu);
        $I->dontSee('Warmup all caches', Tests\Acceptance\Support\Enums\Selectors::ContextMenu);
    }

    public function cannotSeeContextMenuItemForSiteIfNoSiteIsConfiguredForRootPage(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\PageTree $pageTree,
    ): void {
        $I->loginAs('admin');

        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::BackendPageModule);
        $I->switchToIFrame();

        $pageTree->openContextMenu(['Root 3']);

        $I->dontSee('Warmup cache for this page', Tests\Acceptance\Support\Enums\Selectors::ContextMenu);
    }

    public function cannotSeeContextMenuItemsIfNoLanguagesAreAllowed(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\PageTree $pageTree,
    ): void {
        $I->loginAs('editor.3');

        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::BackendPageModule);
        $I->switchToIFrame();

        $pageTree->openContextMenu(['Main']);

        $I->dontSee('Warmup cache for this page', Tests\Acceptance\Support\Enums\Selectors::ContextMenu);
        $I->dontSee('Warmup all caches', Tests\Acceptance\Support\Enums\Selectors::ContextMenu);
    }

    public function canSeeAllLanguagesAsAdmin(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\PageTree $pageTree,
    ): void {
        $I->loginAs('admin');

        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::BackendPageModule);
        $I->switchToIFrame();

        $pageTree->openContextMenu(['Main']);
        $pageTree->selectInContextMenu(['Warmup cache for this page']);

        $I->see('English', Tests\Acceptance\Support\Enums\Selectors::ContextMenuSubmenu);
        $I->see('German', Tests\Acceptance\Support\Enums\Selectors::ContextMenuSubmenu);
    }

    public function canSeeOnlyPermittedLanguagesAsPermittedUser(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\PageTree $pageTree,
    ): void {
        $I->loginAs('editor.2');

        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::BackendPageModule);
        $I->switchToIFrame();

        $pageTree->openContextMenu(['Main']);
        $pageTree->selectInContextMenu(['Warmup cache for this page']);

        $I->see('English', Tests\Acceptance\Support\Enums\Selectors::ContextMenuSubmenu);
        $I->dontSee('German', Tests\Acceptance\Support\Enums\Selectors::ContextMenuSubmenu);
    }

    public function canSeeLanguageSelectionForSite(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\PageTree $pageTree,
    ): void {
        $I->loginAs('admin');

        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::BackendPageModule);
        $I->switchToIFrame();

        $pageTree->openContextMenu(['Main']);
        $pageTree->selectInContextMenu(['Warmup all caches']);

        $I->see('Select…', Tests\Acceptance\Support\Enums\Selectors::ContextMenuSubmenu);
    }

    public function canSelectLanguagesForSite(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $modalDialog,
        Tests\Acceptance\Support\Helper\PageTree $pageTree,
    ): void {
        $I->loginAs('admin');

        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::BackendPageModule);
        $I->switchToIFrame();

        $pageTree->openContextMenu(['Main']);
        $pageTree->selectInContextMenu(['Warmup all caches', 'Select…']);

        $modalDialog->canSeeDialog();

        if ((new Core\Information\Typo3Version())->getMajorVersion() >= 14) {
            $modalTitleSelector = Tests\Acceptance\Support\Enums\Selectors::ModalTitle;
        } else {
            // @todo Remove once support for TYPO3 v13 is dropped
            $modalTitleSelector = Tests\Acceptance\Support\Enums\Selectors::ModalTitleLegacy;
        }

        $I->canSee('Cache warmup', $modalTitleSelector);
        $I->canSee('Sites (filtered)', Tests\Acceptance\Support\Enums\Selectors::ModalHeader);
    }

    public function canSwitchToAllSitesInFilteredSitesModal(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $modalDialog,
        Tests\Acceptance\Support\Helper\PageTree $pageTree,
    ): void {
        $I->loginAs('admin');

        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::BackendPageModule);
        $I->switchToIFrame();

        $pageTree->openContextMenu(['Main']);
        $pageTree->selectInContextMenu(['Warmup all caches', 'Select…']);

        $modalDialog->canSeeDialog();

        $I->click(Tests\Acceptance\Support\Enums\Selectors::ShowAllButton);

        $modalDialog->canSeeDialog();

        $I->seeElement(Tests\Acceptance\Support\Enums\Selectors::SelectAllCheckbox);
        $I->dontSee('Sites (filtered)', Tests\Acceptance\Support\Enums\Selectors::ModalHeader);
    }

    public function canRunCacheWarmupForSite(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $modalDialog,
        Tests\Acceptance\Support\Helper\PageTree $pageTree,
    ): void {
        $I->loginAs('admin');

        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::BackendPageModule);
        $I->switchToIFrame();

        $pageTree->openContextMenu(['Main']);
        $pageTree->selectInContextMenu(['Warmup all caches', 'English']);

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

    public function canRunCacheWarmupForPage(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $modalDialog,
        Tests\Acceptance\Support\Helper\PageTree $pageTree,
    ): void {
        $I->loginAs('admin');

        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::BackendPageModule);
        $I->switchToIFrame();

        $pageTree->openContextMenu(['Main', 'Subsite 1']);
        $pageTree->selectInContextMenu(['Warmup cache for this page', 'English']);

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
}
