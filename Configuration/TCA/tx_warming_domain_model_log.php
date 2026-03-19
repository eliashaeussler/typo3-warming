<?php

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

use TYPO3\CMS\Core;

$tca = [
    'ctrl' => [
        'title' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:tx_warming_domain_model_log',
        'label' => 'url',
        'label_userFunc' => \EliasHaeussler\Typo3Warming\UserFunc\LogTableFormatter::class . '->formatTitle',
        'default_sortby' => 'date DESC',
        'delete' => 'deleted',
        'rootLevel' => 1,
        'iconfile' => 'EXT:warming/Resources/Public/Icons/tx_warming_domain_model_log.svg',
        'typeicon_classes' => [
            'default' => 'overlay-scheduled',
            \EliasHaeussler\Typo3Warming\Enums\WarmupState::Success->value => 'overlay-approved',
            \EliasHaeussler\Typo3Warming\Enums\WarmupState::Failed->value => 'overlay-missing',
        ],
        'typeicon_column' => 'state',
    ],
    'columns' => [
        'request_id' => [
            'exclude' => true,
            'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:tx_warming_domain_model_log.request_id',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim',
                'required' => true,
                'max' => 50,
                'readOnly' => true,
            ],
        ],
        'date' => [
            'exclude' => true,
            'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:tx_warming_domain_model_log.date',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'eval' => 'int',
                'required' => true,
                'readOnly' => true,
            ],
        ],
        'url' => [
            'exclude' => true,
            'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:tx_warming_domain_model_log.url',
            'config' => [
                'type' => 'link',
                'allowedTypes' => ['url'],
                'size' => 50,
                'required' => true,
                'readOnly' => true,
            ],
        ],
        'message' => [
            'exclude' => true,
            'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:tx_warming_domain_model_log.message',
            'description' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:tx_warming_domain_model_log.message.description',
            'config' => [
                'type' => 'text',
                'readOnly' => true,
            ],
        ],
        'state' => [
            'exclude' => true,
            'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:tx_warming_domain_model_log.state',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:tx_warming_domain_model_log.state.success',
                        'value' => \EliasHaeussler\Typo3Warming\Enums\WarmupState::Success->value,
                        'icon' => 'overlay-approved',
                    ],
                    [
                        'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:tx_warming_domain_model_log.state.failed',
                        'value' => \EliasHaeussler\Typo3Warming\Enums\WarmupState::Failed->value,
                        'icon' => 'overlay-missing',
                    ],
                ],
                'default' => \EliasHaeussler\Typo3Warming\Enums\WarmupState::Success->value,
                'required' => true,
                'readOnly' => true,
            ],
        ],
        'sitemap' => [
            'exclude' => true,
            'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:tx_warming_domain_model_log.sitemap',
            'config' => [
                'type' => 'link',
                'allowedTypes' => ['url'],
                'size' => 50,
                'readOnly' => true,
            ],
        ],
        'site' => [
            'exclude' => true,
            'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:tx_warming_domain_model_log.site',
            'config' => [
                'type' => 'group',
                'allowed' => 'pages',
                'maxitems' => 1,
                'size' => 1,
                'readOnly' => true,
            ],
        ],
        'site_language' => [
            'exclude' => true,
            'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:tx_warming_domain_model_log.site_language',
            'config' => [
                'type' => 'language',
                'readOnly' => true,
            ],
        ],
    ],
    'palettes' => [
        'request' => [
            'showitem' => '
                request_id,
                date,
            ',
        ],
    ],
    'types' => [
        0 => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;request,
                    url,
                    message,
                    state,
                --div--;LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:tabs.sitemap,
                    sitemap,
                    site,
                    site_language,
            ',
        ],
    ],
];

// @todo Remove once support for TYPO3 v13 is dropped
if ((new Core\Information\Typo3Version())->getMajorVersion() < 14) {
    $tca['ctrl']['searchFields'] = 'request_id, date, url, message, state, sitemap, site, site_language';
}

return $tca;
