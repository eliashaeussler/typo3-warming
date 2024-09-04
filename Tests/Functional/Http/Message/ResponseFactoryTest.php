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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Http\Message;

use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * ResponseFactoryTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Http\Message\ResponseFactory::class)]
final class ResponseFactoryTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'warming',
    ];

    protected bool $initializeDatabase = false;

    private Src\Http\Message\ResponseFactory $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(Src\Http\Message\ResponseFactory::class);
    }

    #[Framework\Attributes\Test]
    public function okReturnsOkResponse(): void
    {
        $expected = new Core\Http\Response();
        $actual = $this->subject->ok();

        self::assertSame((string)$expected->getBody(), (string)$actual->getBody());
        self::assertEquals($expected, $actual->withBody($expected->getBody()));
    }

    #[Framework\Attributes\Test]
    public function htmlReturnsHtmlResponse(): void
    {
        $expected = new Core\Http\HtmlResponse('foo');
        $actual = $this->subject->html('foo');

        self::assertSame((string)$expected->getBody(), (string)$actual->getBody());
        self::assertEquals($expected, $actual->withBody($expected->getBody()));
    }

    #[Framework\Attributes\Test]
    public function htmlTemplateReturnsHtmlResponseWithRenderedTemplate(): void
    {
        $actual = $this->subject->htmlTemplate('Modal/SitesModal');

        self::assertInstanceOf(Core\Http\HtmlResponse::class, $actual);
        self::assertNotEmpty((string)$actual->getBody());
    }

    #[Framework\Attributes\Test]
    public function jsonReturnsJsonResponse(): void
    {
        $expected = new Core\Http\JsonResponse(['foo' => 'baz']);
        $actual = $this->subject->json(['foo' => 'baz']);

        self::assertSame((string)$expected->getBody(), (string)$actual->getBody());
        self::assertEquals($expected, $actual->withBody($expected->getBody()));
    }

    #[Framework\Attributes\Test]
    public function badRequestReturnsBadRequestResponse(): void
    {
        $expected = new Core\Http\Response(null, 400, [], 'foo');
        $actual = $this->subject->badRequest('foo');

        self::assertSame((string)$expected->getBody(), (string)$actual->getBody());
        self::assertEquals($expected, $actual->withBody($expected->getBody()));
    }
}
