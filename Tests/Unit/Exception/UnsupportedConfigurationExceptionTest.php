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

use EliasHaeussler\Typo3Warming\Exception\UnsupportedConfigurationException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * UnsupportedConfigurationExceptionTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class UnsupportedConfigurationExceptionTest extends UnitTestCase
{
    /**
     * @test
     */
    public function forBaseUrlReturnsExceptionForBaseUrl(): void
    {
        $subject = UnsupportedConfigurationException::forBaseUrl('foo');

        self::assertInstanceOf(UnsupportedConfigurationException::class, $subject);
        self::assertSame('The given base URL "foo" is not supported.', $subject->getMessage());
        self::assertSame(1619168965, $subject->getCode());
    }

    /**
     * @test
     */
    public function forMissingPageIdReturnsExceptionForMissingPageId(): void
    {
        $subject = UnsupportedConfigurationException::forMissingPageId();

        self::assertInstanceOf(UnsupportedConfigurationException::class, $subject);
        self::assertSame('No page ID given. Omitting the page ID is not supported.', $subject->getMessage());
        self::assertSame(1619190793, $subject->getCode());
    }

    /**
     * @test
     */
    public function forTypeMismatchReturnsExceptionForTypeMismatch(): void
    {
        $subject = UnsupportedConfigurationException::forTypeMismatch('foo', 'baz');

        self::assertInstanceOf(UnsupportedConfigurationException::class, $subject);
        self::assertSame('Expected variable of type "foo", got "baz" instead.', $subject->getMessage());
        self::assertSame(1619196807, $subject->getCode());
    }

    /**
     * @test
     */
    public function forUnresolvableClassReturnsExceptionForUnresolvableClass(): void
    {
        $subject = UnsupportedConfigurationException::forUnresolvableClass('foo');

        self::assertInstanceOf(UnsupportedConfigurationException::class, $subject);
        self::assertSame('Given class "foo" does not exist or cannot be resolved.', $subject->getMessage());
        self::assertSame(1619196886, $subject->getCode());
    }

    /**
     * @test
     */
    public function forMissingImplementationReturnsExceptionForMissingImplementation(): void
    {
        $subject = UnsupportedConfigurationException::forMissingImplementation('foo', 'baz');

        self::assertInstanceOf(UnsupportedConfigurationException::class, $subject);
        self::assertSame('Given class "baz" does not implement the expected interface "foo".', $subject->getMessage());
        self::assertSame(1619196994, $subject->getCode());
    }
}
