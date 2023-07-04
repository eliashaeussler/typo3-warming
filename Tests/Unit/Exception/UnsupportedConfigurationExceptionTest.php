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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Exception;

use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * UnsupportedConfigurationExceptionTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\UnsupportedConfigurationException::class)]
final class UnsupportedConfigurationExceptionTest extends TestingFramework\Core\Unit\UnitTestCase
{
    #[Framework\Attributes\Test]
    public function forBaseUrlReturnsExceptionForBaseUrl(): void
    {
        $subject = Src\Exception\UnsupportedConfigurationException::forBaseUrl('foo');

        self::assertSame('The given base URL "foo" is not supported.', $subject->getMessage());
        self::assertSame(1619168965, $subject->getCode());
    }
}
