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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\ValueObject\Request;

use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * SiteWarmupRequestTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\ValueObject\Request\SiteWarmupRequest::class)]
final class SiteWarmupRequestTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Src\Tests\Unit\SiteTrait;

    private Core\Site\Entity\Site $site;
    private Src\ValueObject\Request\SiteWarmupRequest $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = $this->createSite();
        $this->subject = new Src\ValueObject\Request\SiteWarmupRequest($this->site, [1]);
    }

    #[Framework\Attributes\Test]
    public function getSiteReturnsSite(): void
    {
        self::assertSame($this->site, $this->subject->getSite());
    }

    #[Framework\Attributes\Test]
    public function getLanguageIdsReturnsConfiguredLanguageIds(): void
    {
        self::assertSame([1], $this->subject->getLanguageIds());
    }

    #[Framework\Attributes\Test]
    public function getLanguageIdsReturnsDefaultLanguageIdIfNoLanguageIdsAreConfigured(): void
    {
        $subject = new Src\ValueObject\Request\SiteWarmupRequest($this->site);

        self::assertSame([0], $subject->getLanguageIds());
    }
}
