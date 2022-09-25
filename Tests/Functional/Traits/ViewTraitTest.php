<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Traits;

use EliasHaeussler\Typo3Warming\Configuration\Extension;
use EliasHaeussler\Typo3Warming\Tests\Functional\Fixtures\Classes\ViewTraitTestClass;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * ViewTraitTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ViewTraitTest extends FunctionalTestCase
{
    /**
     * @var ViewTraitTestClass
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new ViewTraitTestClass();
    }

    /**
     * @test
     */
    public function buildViewReturnsStandaloneView(): void
    {
        $actual = $this->subject->runBuildView('Foo.html');

        self::assertInstanceOf(StandaloneView::class, $actual);
        self::assertSame('foo', $actual->getRenderingContext()->getControllerAction());
        self::assertSame(Extension::NAME, $actual->getRequest()->getControllerExtensionName());
    }
}
