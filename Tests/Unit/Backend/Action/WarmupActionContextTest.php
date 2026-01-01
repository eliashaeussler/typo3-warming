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
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * WarmupActionContextTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Backend\Action\WarmupActionContext::class)]
final class WarmupActionContextTest extends TestingFramework\Core\Unit\UnitTestCase
{
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('labelReturnsLabelForCurrentContextDataProvider')]
    public function labelReturnsLabelForCurrentContext(
        Src\Backend\Action\WarmupActionContext $context,
        string $expected,
    ): void {
        self::assertSame($expected, $context->label());
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('iconReturnsIconForCurrentContextDataProvider')]
    public function iconReturnsIconForCurrentContext(
        Src\Backend\Action\WarmupActionContext $context,
        string $expected,
    ): void {
        self::assertSame($expected, $context->icon());
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('callbackActionReturnsCallbackActionForCurrentContextDataProvider')]
    public function callbackActionReturnsCallbackActionForCurrentContext(
        Src\Backend\Action\WarmupActionContext $context,
        string $expected,
    ): void {
        self::assertSame($expected, $context->callbackAction());
    }

    /**
     * @return \Generator<string, array{Src\Backend\Action\WarmupActionContext, string}>
     */
    public static function labelReturnsLabelForCurrentContextDataProvider(): \Generator
    {
        yield 'page' => [
            Src\Backend\Action\WarmupActionContext::Page,
            'LLL:EXT:warming/Resources/Private/Language/locallang.xlf:cacheWarmupAction.context.page',
        ];
        yield 'site' => [
            Src\Backend\Action\WarmupActionContext::Site,
            'LLL:EXT:warming/Resources/Private/Language/locallang.xlf:cacheWarmupAction.context.site',
        ];
    }

    /**
     * @return \Generator<string, array{Src\Backend\Action\WarmupActionContext, string}>
     */
    public static function iconReturnsIconForCurrentContextDataProvider(): \Generator
    {
        yield 'page' => [Src\Backend\Action\WarmupActionContext::Page, 'cache-warmup-page'];
        yield 'site' => [Src\Backend\Action\WarmupActionContext::Site, 'cache-warmup-site'];
    }

    /**
     * @return \Generator<string, array{Src\Backend\Action\WarmupActionContext, string}>
     */
    public static function callbackActionReturnsCallbackActionForCurrentContextDataProvider(): \Generator
    {
        yield 'page' => [Src\Backend\Action\WarmupActionContext::Page, 'warmupPageCache'];
        yield 'site' => [Src\Backend\Action\WarmupActionContext::Site, 'warmupSiteCache'];
    }
}
