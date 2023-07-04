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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3Warming\Tests\Unit;

use TYPO3\CMS\Core;

/**
 * SiteTrait
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
trait SiteTrait
{
    private static string $testSiteIdentifier = 'test-site';

    private function createSite(string $baseUrl = 'https://typo3-testing.local/'): Core\Site\Entity\Site
    {
        return new Core\Site\Entity\Site(
            static::$testSiteIdentifier,
            1,
            [
                'rootPageId' => 1,
                'base' => $baseUrl,
                'languages' => [
                    0 => [
                        'title' => 'English',
                        'enabled' => true,
                        'languageId' => 0,
                        'base' => '/',
                        'locale' => 'en_US.UTF-8',
                        'navigationTitle' => 'English',
                        'flag' => 'us',
                    ],
                    1 => [
                        'title' => 'German',
                        'enabled' => true,
                        'locale' => 'de_DE',
                        'base' => '/de/',
                        'websiteTitle' => '',
                        'navigationTitle' => 'Deutsch',
                        'fallbackType' => 'strict',
                        'fallbacks' => '',
                        'flag' => 'de',
                        'languageId' => 1,
                    ],
                    2 => [
                        'title' => 'French',
                        'enabled' => true,
                        'locale' => 'fr_FR',
                        'base' => '/fr/',
                        'websiteTitle' => '',
                        'navigationTitle' => 'Français',
                        'fallbackType' => 'strict',
                        'fallbacks' => '',
                        'flag' => 'fr',
                        'languageId' => 2,
                    ],
                ],
                'errorHandling' => [],
                'routes' => [],
            ],
        );
    }
}
