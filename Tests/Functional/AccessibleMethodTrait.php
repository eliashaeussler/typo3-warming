<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Tests\Functional;

/**
 * AccessibleMethodTrait
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
trait AccessibleMethodTrait
{
    /**
     * @param class-string|object $classNameOrObject
     * @param string $methodName
     * @return \ReflectionMethod
     * @throws \ReflectionException
     */
    private function getAccessibleMethod($classNameOrObject, string $methodName): \ReflectionMethod
    {
        $reflection = new \ReflectionClass($classNameOrObject);
        $reflectionMethod = $reflection->getMethod($methodName);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod;
    }

    /**
     * @param class-string $traitName
     * @param string $methodName
     * @return array{0: object, 1: \ReflectionMethod}
     * @throws \ReflectionException
     */
    private function getAccessibleMethodOfTrait(string $traitName, string $methodName): array
    {
        $object = $this->getObjectForTrait($traitName);

        return [
            $object,
            $this->getAccessibleMethod($object, $methodName),
        ];
    }
}
