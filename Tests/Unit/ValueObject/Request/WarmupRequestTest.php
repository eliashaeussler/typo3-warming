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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\ValueObject\Request;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * WarmupRequestTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\ValueObject\Request\WarmupRequest::class)]
final class WarmupRequestTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\ValueObject\Request\SiteWarmupRequest $site;
    private Src\ValueObject\Request\PageWarmupRequest $page;
    private Src\ValueObject\Request\RequestConfiguration $configuration;
    private Src\ValueObject\Request\WarmupRequest $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = new Src\ValueObject\Request\SiteWarmupRequest(
            new Core\Site\Entity\Site('foo', 7, []),
            [1],
        );
        $this->page = new Src\ValueObject\Request\PageWarmupRequest(7);
        $this->configuration = new Src\ValueObject\Request\RequestConfiguration(
            50,
            new CacheWarmup\Crawler\Strategy\SortByPriorityStrategy(),
        );
        $this->subject = new Src\ValueObject\Request\WarmupRequest(
            'foo',
            [$this->site],
            [$this->page],
            $this->configuration,
        );
    }

    #[Framework\Attributes\Test]
    public function getIdReturnsId(): void
    {
        self::assertSame('foo', $this->subject->getId());
    }

    #[Framework\Attributes\Test]
    public function getSitesReturnsSites(): void
    {
        self::assertSame([$this->site], $this->subject->getSites());
    }

    #[Framework\Attributes\Test]
    public function getPagesReturnsPages(): void
    {
        self::assertSame([$this->page], $this->subject->getPages());
    }

    #[Framework\Attributes\Test]
    public function getConfigurationReturnsConfiguration(): void
    {
        self::assertSame($this->configuration, $this->subject->getConfiguration());
    }
}
