<?php

defined('TYPO3') or die();

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021 Elias Häußler <elias@haeussler.dev>
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

$GLOBALS['SiteConfiguration']['site_language']['columns']['xml_sitemap_path'] = [
    'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:sites.xml_sitemap_path.label',
    'description' => 'LLL:EXT:warming/Resources/Private/Language/locallang_db.xlf:sites.xml_sitemap_path.description',
    'displayCond' => 'FIELD:languageId:>:0',
    'config' => [
        'type' => 'input',
        'valuePicker' => [
            'items' => [
                [
                    \EliasHaeussler\Typo3Warming\Sitemap\Provider\DefaultProvider::DEFAULT_PATH,
                    \EliasHaeussler\Typo3Warming\Sitemap\Provider\DefaultProvider::DEFAULT_PATH,
                ],
            ],
        ],
        'eval' => 'trim',
    ],
];

$GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem'] = str_replace(
    '--palette--;;default,',
    '--palette--;;default, xml_sitemap_path,',
    $GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem']
);
