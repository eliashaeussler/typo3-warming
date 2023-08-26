<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Tests\Acceptance\Backend\Form\FormDataProvider;

use EliasHaeussler\Typo3Warming\Tests;

/**
 * SimulateLogPageRowCest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class SimulateLogPageRowCest
{
    public function _before(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->runShellCommand('typo3 warming:cachewarmup -s 1 --limit 10');
        $I->loginAs('admin');
        $I->openModule('[data-moduleroute-identifier="web_list"]');
    }

    public function seeCacheWarmupLogs(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->seeElement('#t3-table-tx_warming_domain_model_log');
    }

    public function canOpenCrawledUrlUsingPageViewButton(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->click('tr[data-table="tx_warming_domain_model_log"]:first-child td.col-title a');

        $fieldIdentifier = $I->grabAttributeFrom('//label[starts-with(text(), "URL")]', 'for');
        $loggedUrl = $I->grabValueFrom(['id' => $fieldIdentifier]);
        $resolvedUrl = $I->grabAttributeFrom('.t3js-editform-view', 'href');

        $I->assertSame($loggedUrl, $resolvedUrl);
    }
}
