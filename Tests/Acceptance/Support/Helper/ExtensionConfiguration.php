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

namespace EliasHaeussler\Typo3Warming\Tests\Acceptance\Support\Helper;

use Codeception\Module;
use EliasHaeussler\Typo3CodeceptionHelper;

/**
 * ExtensionConfiguration
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ExtensionConfiguration
{
    /**
     * @var non-empty-string
     */
    private readonly string $scriptPath;

    public function __construct(
        private readonly Module\Asserts $asserts,
        private readonly Module\Cli $cli,
    ) {
        $this->scriptPath = $this->determineScriptPath();
    }

    public function read(string $path): mixed
    {
        $command = $this->buildCommand(['configuration:showactive', 'EXTENSIONS/warming/' . $path, '--json']);

        $this->cli->runShellCommand($command);

        $output = $this->cli->grabShellOutput();

        $this->asserts->assertJson($output);

        return json_decode($output, true);
    }

    /**
     * @param non-empty-string $path
     */
    public function write(string $path, mixed $value): void
    {
        $command = $this->buildCommand(['configuration:set', 'EXTENSIONS/warming/' . $path, $value]);

        $this->cli->runShellCommand($command);
    }

    /**
     * @param non-empty-list<scalar> $command
     * @return non-empty-string
     */
    private function buildCommand(array $command): string
    {
        $fullCommand = [$this->scriptPath, ...$command];
        $fullCommand = array_map('strval', $fullCommand);

        return implode(' ', array_map('escapeshellarg', $fullCommand));
    }

    /**
     * @return non-empty-string
     */
    private function determineScriptPath(): string
    {
        $buildDir = \dirname(Typo3CodeceptionHelper\Helper\PathHelper::getVendorDirectory());

        return $buildDir . '/bin/typo3';
    }
}
