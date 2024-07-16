<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Command;

use EliasHaeussler\Typo3Warming\Configuration\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ShowUserAgentCommand
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ShowUserAgentCommand extends Command
{
    private Configuration $configuration;

    public function __construct(Configuration $configuration, string $name = null)
    {
        $this->configuration = $configuration;

        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription('Show custom "User-Agent" header to be used for Frontend requests by default crawlers.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write($this->configuration->getUserAgent());

        return 0;
    }
}
