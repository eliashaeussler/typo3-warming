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

namespace EliasHaeussler\Typo3Warming\Tests\Build\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection;

/**
 * PublicServicePass.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 *
 * @internal Only to be used for testing purposes
 *
 * @codeCoverageIgnore
 */
final class PublicServicePass implements DependencyInjection\Compiler\CompilerPassInterface
{
    public function __construct(
        private readonly string $definitionPattern,
    ) {}

    public static function fromClass(string $className): self
    {
        return new self('/^' . preg_quote($className) . '$/');
    }

    public function process(DependencyInjection\ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            if (preg_match($this->definitionPattern, $id) === 1) {
                $definition->setPublic(true);
            }
        }
    }
}
