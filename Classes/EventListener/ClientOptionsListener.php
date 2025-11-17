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

namespace EliasHaeussler\Typo3Warming\EventListener;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Http;
use GuzzleHttp\RequestOptions;
use TYPO3\CMS\Core;

/**
 * ClientOptionsListener
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final readonly class ClientOptionsListener
{
    public function __construct(
        private Typo3SitemapLocator\Http\Client\ClientFactory $clientFactory,
        private Configuration\Configuration $configuration,
        private Http\Message\Request\RequestOptions $requestOptions,
    ) {}

    #[Core\Attribute\AsEventListener('eliashaeussler/typo3-warming/client-options/process-config')]
    public function processConfig(CacheWarmup\Event\Config\ConfigResolved $event): void
    {
        $clientOptions = $event->config()->getClientOptions();
        $config = $this->clientFactory->getClientConfig();

        // Overwrite handler if not exists yet
        if (!isset($clientOptions['handler']) && isset($config['handler'])) {
            $clientOptions['handler'] = $config['handler'];
            unset($config['handler']);
        }

        Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($clientOptions, $config);

        $event->config()->setClientOptions($clientOptions);
    }

    #[Core\Attribute\AsEventListener('eliashaeussler/typo3-warming/client-options/process-client')]
    public function processClient(Typo3SitemapLocator\Event\BeforeClientConfiguredEvent $event): void
    {
        $options = $event->getOptions();

        Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($options, $this->configuration->clientOptions);
        Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($options, [
            RequestOptions::HEADERS => [
                'User-Agent' => $this->requestOptions->getUserAgent(),
            ],
        ]);

        $event->setOptions($options);
    }
}
