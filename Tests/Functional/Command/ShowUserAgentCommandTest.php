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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Command;

use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use Symfony\Component\Console;
use TYPO3\TestingFramework;

/**
 * ShowUserAgentCommandTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Command\ShowUserAgentCommand::class)]
final class ShowUserAgentCommandTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'typed_extconf',
        'warming',
    ];

    protected bool $initializeDatabase = false;

    private Src\Configuration\Configuration $configuration;
    private Console\Tester\CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configuration = $this->get(Src\Configuration\Configuration::class);
        $this->commandTester = new Console\Tester\CommandTester($this->get(Src\Command\ShowUserAgentCommand::class));
    }

    #[Framework\Attributes\Test]
    public function executePrintsUserAgent(): void
    {
        $this->commandTester->execute([]);

        self::assertSame(0, $this->commandTester->getStatusCode());
        self::assertSame($this->configuration->getUserAgent(), $this->commandTester->getDisplay());
    }
}
