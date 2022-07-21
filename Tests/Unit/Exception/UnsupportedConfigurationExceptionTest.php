<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\Typo3Warming\Exception\UnsupportedConfigurationException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * UnsupportedConfigurationExceptionTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class UnsupportedConfigurationExceptionTest extends UnitTestCase
{
    /**
     * @test
     */
    public function forBaseUrlReturnsExceptionForBaseUrl(): void
    {
        $actual = UnsupportedConfigurationException::forBaseUrl('foo');

        self::assertInstanceOf(UnsupportedConfigurationException::class, $actual);
        self::assertSame('The given base URL "foo" is not supported.', $actual->getMessage());
        self::assertSame(1619168965, $actual->getCode());
    }

    /**
     * @test
     */
    public function forMissingPageIdReturnsExceptionForMissingPageId(): void
    {
        $actual = UnsupportedConfigurationException::forMissingPageId();

        self::assertInstanceOf(UnsupportedConfigurationException::class, $actual);
        self::assertSame('No page ID given. Omitting the page ID is not supported.', $actual->getMessage());
        self::assertSame(1619190793, $actual->getCode());
    }

    /**
     * @test
     */
    public function forTypeMismatchReturnsExceptionForTypeMismatch(): void
    {
        $actual = UnsupportedConfigurationException::forTypeMismatch('foo', 'baz');

        self::assertInstanceOf(UnsupportedConfigurationException::class, $actual);
        self::assertSame('Expected variable of type "foo", got "baz" instead.', $actual->getMessage());
        self::assertSame(1619196807, $actual->getCode());
    }

    /**
     * @test
     */
    public function forUnresolvableClassReturnsExceptionForUnresolvableClass(): void
    {
        $actual = UnsupportedConfigurationException::forUnresolvableClass('foo');

        self::assertInstanceOf(UnsupportedConfigurationException::class, $actual);
        self::assertSame('Given class "foo" does not exist or cannot be resolved.', $actual->getMessage());
        self::assertSame(1619196886, $actual->getCode());
    }

    /**
     * @test
     */
    public function forMissingImplementationReturnsExceptionForMissingImplementation(): void
    {
        $actual = UnsupportedConfigurationException::forMissingImplementation('foo', 'baz');

        self::assertInstanceOf(UnsupportedConfigurationException::class, $actual);
        self::assertSame('Given class "baz" does not implement the expected interface "foo".', $actual->getMessage());
        self::assertSame(1619196994, $actual->getCode());
    }
}
