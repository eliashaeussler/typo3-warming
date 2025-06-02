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

namespace EliasHaeussler\Typo3Warming\Tests\Acceptance\Backend\DataProcessing;

use EliasHaeussler\Typo3Warming\Tests;
use Facebook\WebDriver\WebDriverBy;

/**
 * ExtensionConfigurationProcessorCest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ExtensionConfigurationProcessorCest
{
    public function _before(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->loginAs('admin');

        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::BackendSettingsModule->value);
        $I->waitForElementClickable(Tests\Acceptance\Support\Enums\Selectors::ConfigureExtensionsButton->value);
        $I->click(Tests\Acceptance\Support\Enums\Selectors::ConfigureExtensionsButton->value);

        $I->switchToFrame();

        $I->waitForElementClickable(Tests\Acceptance\Support\Enums\Selectors::ExtensionConfigurationModalCollapseHeader->value);
        $I->click(Tests\Acceptance\Support\Enums\Selectors::ExtensionConfigurationModalCollapseHeader->value);
    }

    public function canSeeProcessedJsonInExtensionConfigurationModal(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->seeElementInDOM(
            Tests\Acceptance\Support\Enums\Selectors::CodeMirrorWrapper->value,
            ['name' => 'crawlerOptions'],
        );
        $I->seeElementInDOM(
            Tests\Acceptance\Support\Enums\Selectors::CodeMirrorWrapper->value,
            ['name' => 'verboseCrawlerOptions'],
        );
        $I->seeElementInDOM(
            Tests\Acceptance\Support\Enums\Selectors::CodeMirrorWrapper->value,
            ['name' => 'parserOptions'],
        );
        $I->seeElementInDOM(
            Tests\Acceptance\Support\Enums\Selectors::CodeMirrorWrapper->value,
            ['name' => 'clientOptions'],
        );
    }

    public function canSeeProcessedCrawlerFqcnInExtensionConfigurationModal(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->see('Congratulations, your configuration is valid.');
    }

    public function canProcessCrawlerFqcnValidationInExtensionConfigurationModal(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->fillField('#em-warming-crawler', 'foo');
        $I->see('The given class is invalid, it must implement EliasHaeussler\CacheWarmup\Crawler\Crawler.');
    }

    public function canSeeAllCrawlingStrategiesInExtensionConfigurationModal(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->click('[aria-controls="category-warming-options"]');
        $I->selectOption('#em-warming-strategy', 'dummy');
    }

    public function canSeeProcessedTagListInExtensionConfigurationModal(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->click('[aria-controls="category-warming-options"]');
        $I->seeElement('.tagify');
    }

    public function canProcessTagListValidationInExtensionConfigurationModal(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->click('[aria-controls="category-warming-options"]');
        $I->fillField(WebDriverBy::className('tagify__input'), '###,');
        $I->seeElementInDOM('[title="The string \"###\" is not a regular expression."]');
    }
}
