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

namespace EliasHaeussler\Typo3Warming\Exception;

use EliasHaeussler\Typo3Warming\Sitemap;

/**
 * InvalidProviderException
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class InvalidProviderException extends Exception
{
    public static function create(object $provider): self
    {
        return new self(
            sprintf(
                'The given provider "%s" does not implement the interface "%s".',
                $provider::class,
                Sitemap\Provider\Provider::class,
            ),
            1619524996,
        );
    }

    public static function forInvalidType(mixed $value): self
    {
        return new self(
            sprintf('Providers must be of type object, "%s" given.', \gettype($value)),
            1619525071,
        );
    }
}