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

$GLOBALS['SiteConfiguration']['site']['columns']['warming_exclude'] = [
    'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:site.warming_exclude.label',
    'description' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:site.warming_exclude.description',
    'config' => [
        'type' => 'check',
        'renderType' => 'checkboxLabeledToggle',
        'items' => [
            [
                'label' => '',
                'labelChecked' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:site.warming_exclude.checked',
                'labelUnchecked' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:site.warming_exclude.unchecked',
                'invertStateDisplay' => true,
            ],
        ],
    ],
];

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] = str_replace(
    '--palette--;;xml_sitemap,',
    '--palette--;;xml_sitemap, --palette--;;warming,',
    (string)$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'],
);

$GLOBALS['SiteConfiguration']['site']['palettes']['warming'] = [
    'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:palettes.warming',
    'showitem' => 'warming_exclude',
];
