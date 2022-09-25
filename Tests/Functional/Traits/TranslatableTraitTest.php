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

use EliasHaeussler\Typo3Warming\Tests\Functional\Fixtures\Classes\TranslatableTraitTestClass;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * TranslatableTraitTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class TranslatableTraitTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/warming',
    ];

    /**
     * @var TranslatableTraitTestClass
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new TranslatableTraitTestClass();

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');
        $this->setUpBackendUser(1);

        Bootstrap::initializeLanguageObject();
    }

    /**
     * @test
     */
    public function translateReturnsNullIfGivenLocalizationIsNotAvailable(): void
    {
        self::assertNull($this->subject->runTranslate('foo'));
    }

    /**
     * @test
     */
    public function translateReturnsResolvedTranslation(): void
    {
        self::assertSame(
            'Warmup cache',
            $this->subject->runTranslate('cacheWarmupToolbarItem.title')
        );
    }
}
