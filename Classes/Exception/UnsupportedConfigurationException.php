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

namespace EliasHaeussler\Typo3Warming\Exception;

/**
 * UnsupportedConfigurationException
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class UnsupportedConfigurationException extends \Exception
{
    public static function forBaseUrl(string $baseUrl): self
    {
        return new self(sprintf('The given base URL "%s" is not supported.', $baseUrl), 1619168965);
    }

    public static function forMissingPageId(): self
    {
        return new self('No page ID given. Omitting the page ID is not supported.', 1619190793);
    }

    public static function forTypeMismatch(string $expectedType, string $actualType): self
    {
        return new self(
            sprintf('Expected variable of type "%s", got "%s" instead.', $expectedType, $actualType),
            1619196807
        );
    }

    public static function forUnresolvableClass(string $className): self
    {
        return new self(
            sprintf('Given class "%s" does not exist or cannot be resolved.', $className),
            1619196886
        );
    }

    public static function forMissingImplementation(string $expectedInterface, string $actualClassName): self
    {
        return new self(
            sprintf('Given class "%s" does not implement the expected interface "%s".', $actualClassName, $expectedInterface),
            1619196994
        );
    }
}
