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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Domain\Type;

use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * StateTypeTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Domain\Type\StateType::class)]
final class StateTypeTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Domain\Type\StateType $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Domain\Type\StateType(Src\Enums\WarmupState::Success);
    }

    #[Framework\Attributes\Test]
    public function constructorCanHandleStatesAsString(): void
    {
        $actual = new Src\Domain\Type\StateType('failed');

        self::assertSame(Src\Enums\WarmupState::Failed, $actual->get());
    }

    #[Framework\Attributes\Test]
    public function getReturnsWarmupState(): void
    {
        self::assertSame(Src\Enums\WarmupState::Success, $this->subject->get());
    }

    #[Framework\Attributes\Test]
    public function stringRepresentationReturnsWarmupStateValue(): void
    {
        self::assertSame(Src\Enums\WarmupState::Success->value, (string)$this->subject);
    }
}
