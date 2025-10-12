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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Backend\Action;

use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * WarmupActionTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Backend\Action\WarmupAction::class)]
final class WarmupActionTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Tests\Unit\SiteTrait;

    #[Framework\Attributes\Test]
    public function languageReturnsWarmupActionForGivenLanguage(): void
    {
        $actual = Src\Backend\Action\WarmupAction::language(0, 'English', 'flag-en');

        self::assertSame('lang_0', $actual->name);
        self::assertSame(0, $actual->identifier);
        self::assertSame('English', $actual->label);
        self::assertSame('flag-en', $actual->icon);
    }

    #[Framework\Attributes\Test]
    public function fromSiteLanguageReturnsWarmupActionForGivenSiteLanguage(): void
    {
        $siteLanguage = $this->createSite()->getLanguageById(0);

        $actual = Src\Backend\Action\WarmupAction::fromSiteLanguage($siteLanguage);

        self::assertSame('lang_0', $actual->name);
        self::assertSame(0, $actual->identifier);
        self::assertSame('English', $actual->label);
        self::assertSame('flags-us', $actual->icon);
    }

    #[Framework\Attributes\Test]
    public function specialReturnsWarmupActionForGivenSpecialType(): void
    {
        $actual = Src\Backend\Action\WarmupAction::special('select', 'Select…', 'flags-multiple');

        self::assertSame('special_select', $actual->name);
        self::assertSame('select', $actual->identifier);
        self::assertSame('Select…', $actual->label);
        self::assertSame('flags-multiple', $actual->icon);
    }
}
