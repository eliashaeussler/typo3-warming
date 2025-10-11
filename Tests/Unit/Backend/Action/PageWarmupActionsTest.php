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

namespace EliasHaeussler\Typo3Warming\Tests\Backend\Action;

use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * PageWarmupActionsTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Backend\Action\PageWarmupActions::class)]
final class PageWarmupActionsTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Tests\Unit\SiteTrait;

    private Core\Site\Entity\Site $site;
    private Src\Backend\Action\PageWarmupActions $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->site = $this->createSite();
        $this->subject = new Src\Backend\Action\PageWarmupActions(1, [
            $this->site->getLanguageById(0),
            $this->site->getLanguageById(1),
        ]);
    }

    #[Framework\Attributes\Test]
    public function getActionsReturnsEmptyIterableIfNoSiteLanguagesAreAvailable(): void
    {
        $subject = new Src\Backend\Action\PageWarmupActions(1, []);

        self::assertSame([], iterator_to_array($subject->getActions()));
    }

    #[Framework\Attributes\Test]
    public function getActionsReturnsIterableWarmupActions(): void
    {
        $expected = [
            Src\Backend\Action\WarmupAction::fromSiteLanguage($this->site->getLanguageById(0)),
            Src\Backend\Action\WarmupAction::fromSiteLanguage($this->site->getLanguageById(1)),
        ];

        self::assertEquals($expected, iterator_to_array($this->subject->getActions()));
    }

    #[Framework\Attributes\Test]
    public function countReturnsNumberOfSiteLanguages(): void
    {
        self::assertCount(2, $this->subject);
    }

    #[Framework\Attributes\Test]
    public function subjectIsIterable(): void
    {
        $expected = [
            Src\Backend\Action\WarmupAction::fromSiteLanguage($this->site->getLanguageById(0)),
            Src\Backend\Action\WarmupAction::fromSiteLanguage($this->site->getLanguageById(1)),
        ];

        self::assertEquals($expected, iterator_to_array($this->subject));
    }
}
