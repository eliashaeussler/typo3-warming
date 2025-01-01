<?php

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

return [
    'BE' => [
        'debug' => true,
        // password = password
        'installToolPassword' => '$argon2i$v=19$m=65536,t=16,p=1$VEZPRGVuQ2kuNjZQNUJVSA$d84veaTY5pZUsg6d4rxXB/2QCmRhNOaleBhx2joQIa0',
        'loginRateLimit' => 0,
        'loginRateLimitIpExcludeList' => '*',
        'passwordHashing' => [
            'className' => 'TYPO3\\CMS\\Core\\Crypto\\PasswordHashing\\Argon2iPasswordHash',
            'options' => [],
        ],
    ],
    'DB' => [
        'Connections' => [
            'Default' => [
                'charset' => 'utf8mb4',
                'dbname' => 'db',
                'driver' => 'mysqli',
                'host' => 'db',
                'password' => 'db',
                'port' => 3306,
                'tableoptions' => [
                    'charset' => 'utf8mb4',
                    'collate' => 'utf8mb4_unicode_ci',
                ],
                'user' => 'db',
            ],
        ],
    ],
    'EXTENSIONS' => [
        'backend' => [
            'backendFavicon' => '',
            'backendLogo' => '',
            'loginBackgroundImage' => '',
            'loginFootnote' => '',
            'loginHighlightColor' => '',
            'loginLogo' => '',
            'loginLogoAlt' => '',
        ],
        'warming' => [
            'crawler' => 'EliasHaeussler\\Typo3Warming\\Crawler\\ConcurrentUserAgentCrawler',
            'crawlerOptions' => '',
            'enablePageTree' => '1',
            'enableToolbar' => true,
            'exclude' => '',
            'limit' => '250',
            'parserClientOptions' => '',
            'strategy' => '',
            'supportedDoktypes' => '1',
            'verboseCrawler' => 'EliasHaeussler\\Typo3Warming\\Crawler\\OutputtingUserAgentCrawler',
            'verboseCrawlerOptions' => '',
        ],
    ],
    'FE' => [
        'debug' => true,
        'passwordHashing' => [
            'className' => 'TYPO3\\CMS\\Core\\Crypto\\PasswordHashing\\Argon2iPasswordHash',
            'options' => [],
        ],
    ],
    // This GFX configuration allows processing by installed ImageMagick 6
    'GFX' => [
        'processor' => 'ImageMagick',
        'processor_path' => '/usr/bin/',
        'processor_path_lzw' => '/usr/bin/',
    ],
    'LOG' => [
        'TYPO3' => [
            'CMS' => [
                'deprecations' => [
                    'writerConfiguration' => [
                        'notice' => [
                            \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                                'disabled' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    // This mail configuration sends all emails to mailpit
    'MAIL' => [
        'transport' => 'smtp',
        'transport_smtp_encrypt' => false,
        'transport_smtp_server' => 'localhost:1025',
    ],
    'SYS' => [
        'caching' => [
            'cacheConfigurations' => [
                'hash' => [
                    'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                ],
                'pages' => [
                    'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                    'options' => [
                        'compression' => true,
                    ],
                ],
                'rootline' => [
                    'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                    'options' => [
                        'compression' => true,
                    ],
                ],
            ],
        ],
        'devIPmask' => '*',
        'displayErrors' => 1,
        'encryptionKey' => '22be11b3acb2d0a7427e9f23c6c1d8d2c19b05312d4961c025b9a8b74bd7f4087ad38eca173788364b3cccf7398ed682',
        'exceptionalErrors' => 12290,
        'sitename' => 'EXT:warming',
        'systemMaintainers' => [
            1,
        ],
        'trustedHostsPattern' => '.*.*',
    ],
];
