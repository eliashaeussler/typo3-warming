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

namespace EliasHaeussler\Typo3Warming\Tests\Functional;

use TYPO3\CMS\Core;

/**
 * SiteTrait
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
trait SiteTrait
{
    private function createSite(
        string $baseUrl = 'https://typo3-testing.local/',
        string $identifier = 'test-site',
    ): Core\Site\Entity\Site {
        $configPath = $this->instancePath . '/typo3conf/sites';
        $eventDispatcher = new Core\EventDispatcher\NoopEventDispatcher();

        if ((new Core\Information\Typo3Version())->getMajorVersion() >= 13) {
            $yamlFileLoader = $this->get(Core\Configuration\Loader\YamlFileLoader::class);
            $siteConfiguration = new Core\Configuration\SiteConfiguration(
                $configPath,
                $this->get(Core\Site\SiteSettingsFactory::class),
                $this->get(Core\Site\Set\SetRegistry::class),
                $eventDispatcher,
                new Core\Cache\Frontend\NullFrontend('core'),
                $yamlFileLoader,
                new Core\Cache\Frontend\NullFrontend('runtime'),
            );
            $siteWriter = new Core\Configuration\SiteWriter(
                $configPath,
                $eventDispatcher,
                $yamlFileLoader,
            );
        } else {
            // @todo Remove once support for TYPO3 v12 is dropped
            $siteConfiguration = $siteWriter = new Core\Configuration\SiteConfiguration($configPath, $eventDispatcher);
        }

        $siteWriter->createNewBasicSite($identifier, 1, $baseUrl);

        $rawConfig = $siteConfiguration->load($identifier);
        $rawConfig['languages'][1] = [
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
        ];
        $rawConfig['languages'][2] = [
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
        ];

        $siteWriter->write($identifier, $rawConfig);

        $site = $siteConfiguration->getAllExistingSites()[$identifier] ?? null;

        self::assertInstanceOf(Core\Site\Entity\Site::class, $site);

        return $site;
    }
}
