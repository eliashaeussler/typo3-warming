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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Configuration;

use EliasHaeussler\Typo3Warming as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * LocalizationTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Configuration\Localization::class)]
final class LocalizationTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'sitemap_locator',
        'warming',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(\dirname(__DIR__) . '/Fixtures/Database/be_users.csv');

        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(Core\Localization\LanguageServiceFactory::class)->createFromUserPreferences($backendUser);
    }

    #[Framework\Attributes\Test]
    public function translateReturnsEmptyStringForMissingLocalizationKey(): void
    {
        self::assertSame('', Src\Configuration\Localization::translate('foo'));
    }

    #[Framework\Attributes\Test]
    public function translateReturnsTranslationForGivenLocalizationKey(): void
    {
        self::assertNotEmpty(
            Src\Configuration\Localization::translate('cacheWarmupToolbarItem.title'),
        );
    }

    #[Framework\Attributes\Test]
    public function translateReturnsTranslationForGivenLocalizationKeyAndArguments(): void
    {
        $actual = Src\Configuration\Localization::translate('notification.message.page.error', ['foo', 'baz']);

        self::assertNotEmpty($actual);
        self::assertStringNotContainsString('%', $actual);
    }

    #[Framework\Attributes\Test]
    public function translateReturnsTranslationForGivenLocalizationKeyAndType(): void
    {
        self::assertNotEmpty(
            Src\Configuration\Localization::translate('tabs.sitemap', type: 'db'),
        );
    }
}
