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

namespace EliasHaeussler\Typo3Warming\Tests\Acceptance\Support\Helper;

use EliasHaeussler\Typo3Warming\Tests;
use TYPO3\CMS\Core;

/**
 * ExtensionConfiguration
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class ExtensionConfiguration
{
    private Core\Configuration\ConfigurationManager $configurationManager;

    public function __construct(
        private Tests\Acceptance\Support\AcceptanceTester $tester,
    ) {
        $this->configurationManager = Core\Utility\GeneralUtility::makeInstance(Core\Configuration\ConfigurationManager::class);
    }

    public function read(string $path): mixed
    {
        return $this->configurationManager->getConfigurationValueByPath('EXTENSIONS/warming/' . $path);
    }

    /**
     * @param non-empty-string $path
     */
    public function write(string $path, mixed $value): void
    {
        $this->configurationManager->setLocalConfigurationValueByPath('EXTENSIONS/warming/' . $path, $value);

        $I = $this->tester;
        $I->runShellCommand('typo3 cache:flush');
    }
}
