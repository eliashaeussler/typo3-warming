<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Domain\Type;

use EliasHaeussler\Typo3Warming\Enums;
use Stringable;
use TYPO3\CMS\Core;

/**
 * StateType
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class StateType implements Core\Type\TypeInterface, Stringable
{
    private readonly Enums\WarmupState $state;

    /**
     * @param Enums\WarmupState|value-of<Enums\WarmupState> $state
     */
    public function __construct(Enums\WarmupState|string $state)
    {
        if (is_string($state)) {
            $state = Enums\WarmupState::from($state);
        }

        $this->state = $state;
    }

    public function get(): Enums\WarmupState
    {
        return $this->state;
    }

    public function __toString(): string
    {
        return $this->state->value;
    }
}
