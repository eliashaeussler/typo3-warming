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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Http\Message;

use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * UrlMetadataTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Message\UrlMetadata::class)]
final class UrlMetadataTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Http\Message\UrlMetadata $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Http\Message\UrlMetadata(1, '0', 1);
    }

    #[Framework\Attributes\Test]
    public function objectIsJsonSerializable(): void
    {
        self::assertJsonStringEqualsJsonString(
            '{"pageId":1, "pageType":"0", "languageId":1}',
            json_encode($this->subject, JSON_THROW_ON_ERROR),
        );
    }
}
