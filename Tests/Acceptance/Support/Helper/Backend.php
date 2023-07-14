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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3Warming\Tests\Acceptance\Support\Helper;

use EliasHaeussler\Typo3CodeceptionHelper;
use EliasHaeussler\Typo3Warming\Tests;

/**
 * Backend
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 *
 * @extends Typo3CodeceptionHelper\Codeception\Helper\AbstractBackend<Tests\Acceptance\Support\AcceptanceTester>
 */
final class Backend extends Typo3CodeceptionHelper\Codeception\Helper\AbstractBackend
{
    private const USERS = [
        'admin' => 'password',
        'editor.1' => 'password',
        'editor.2' => 'password',
    ];

    public function __construct(
        Tests\Acceptance\Support\AcceptanceTester $tester,
        ModalDialog $modalDialog,
    ) {
        parent::__construct($tester, $modalDialog);
    }

    public function loginAs(string $username): void
    {
        if (!isset(self::USERS[$username])) {
            $this->tester->fail(
                sprintf('Backend user "%s" does not exist.', $username),
            );
        }

        $this->login($username, self::USERS[$username]);
    }

    public function loginAsAdmin(): void
    {
        $this->loginAs('admin');
    }
}
