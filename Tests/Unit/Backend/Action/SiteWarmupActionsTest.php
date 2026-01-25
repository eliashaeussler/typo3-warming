<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2026 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Backend\Action;

use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * SiteWarmupActionsTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Backend\Action\SiteWarmupActions::class)]
final class SiteWarmupActionsTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Tests\Unit\SiteTrait;

    private Core\Site\Entity\Site $site;
    private Src\Backend\Action\SiteWarmupActions $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->site = $this->createSite();
        $this->subject = new Src\Backend\Action\SiteWarmupActions($this->site, [
            $this->site->getLanguageById(0),
            $this->site->getLanguageById(1),
        ]);

        $GLOBALS['LANG'] = $this->createMock(Core\Localization\LanguageService::class);
        $GLOBALS['LANG']->method('sL')->willReturn('foo');
    }

    #[Framework\Attributes\Test]
    public function getActionsReturnsEmptyIterableIfNoSiteLanguagesAreAvailable(): void
    {
        $subject = new Src\Backend\Action\SiteWarmupActions($this->site, []);

        self::assertSame([], iterator_to_array($subject->getActions()));
    }

    #[Framework\Attributes\Test]
    public function getActionsReturnsIterableWarmupActionsWithSelectAction(): void
    {
        $expected = [
            Src\Backend\Action\WarmupAction::special('select', 'foo', 'flags-multiple'),
            Src\Backend\Action\WarmupAction::fromSiteLanguage($this->site->getLanguageById(0)),
            Src\Backend\Action\WarmupAction::fromSiteLanguage($this->site->getLanguageById(1)),
        ];

        self::assertEquals($expected, iterator_to_array($this->subject->getActions()));
    }

    #[Framework\Attributes\Test]
    public function getActionsReturnsIterableWarmupActionsWithoutSelectAction(): void
    {
        $subject = new Src\Backend\Action\SiteWarmupActions($this->site, [
            $this->site->getLanguageById(0),
        ]);

        $expected = [
            Src\Backend\Action\WarmupAction::fromSiteLanguage($this->site->getLanguageById(0)),
        ];

        self::assertEquals($expected, iterator_to_array($subject->getActions()));
    }

    #[Framework\Attributes\Test]
    public function countReturnsNumberOfSiteLanguagesPlusSelectAction(): void
    {
        self::assertCount(3, $this->subject);
    }

    #[Framework\Attributes\Test]
    public function countReturnsNumberOfSiteLanguagesWithoutSelectAction(): void
    {
        $subject = new Src\Backend\Action\SiteWarmupActions($this->site, [
            $this->site->getLanguageById(0),
        ]);

        self::assertCount(1, $subject);
    }

    #[Framework\Attributes\Test]
    public function subjectIsIterable(): void
    {
        $expected = [
            Src\Backend\Action\WarmupAction::special('select', 'foo', 'flags-multiple'),
            Src\Backend\Action\WarmupAction::fromSiteLanguage($this->site->getLanguageById(0)),
            Src\Backend\Action\WarmupAction::fromSiteLanguage($this->site->getLanguageById(1)),
        ];

        self::assertEquals($expected, iterator_to_array($this->subject));
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['LANG']);

        parent::tearDown();
    }
}
