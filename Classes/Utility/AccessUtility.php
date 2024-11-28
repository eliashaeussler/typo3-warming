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

namespace EliasHaeussler\Typo3Warming\Utility;

use EliasHaeussler\Typo3Warming\Utility;
use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;

/**
 * AccessUtility
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class AccessUtility
{
    public static function canWarmupCacheOfPage(int $pageId, ?int $languageId = null): bool
    {
        return self::checkPagePermissions($pageId, $languageId)
            && self::isPageAccessible($pageId)
            && ($languageId === null || self::isLanguageAccessible($languageId))
        ;
    }

    public static function canWarmupCacheOfSite(Core\Site\Entity\Site $site, ?int $languageId = null): bool
    {
        return self::checkPagePermissions($site->getRootPageId(), $languageId)
            && self::isSiteAccessible($site->getIdentifier())
            && ($languageId === null || self::isLanguageAccessible($languageId))
        ;
    }

    private static function checkPagePermissions(int $pageId, ?int $languageId = null): bool
    {
        $backendUser = Utility\BackendUtility::getBackendUser();

        // Fetch record and record localization (if language is given and is not default language),
        // additionally check for available pages by adding hidden=0 as additional WHERE clause
        $record = Backend\Utility\BackendUtility::getRecord('pages', $pageId, '*', 'hidden = 0');
        if ($languageId !== null && $languageId > 0) {
            $record = Backend\Utility\BackendUtility::getRecordLocalization('pages', $pageId, $languageId, 'hidden = 0');
        }

        // Early return if record is inaccessible
        if (!\is_array($record) || $record === []) {
            return false;
        }

        // Early return if backend user has admin privileges
        if ($backendUser->isAdmin()) {
            return true;
        }

        // Select first record inside list of records which is potentially returned by
        // BackendUtility::getRecordLocalization()
        if (array_is_list($record)) {
            $record = reset($record);
        }

        // Early return if localized record is inaccessible
        // (this should never happen, but makes PHPStan happy)
        if (!\is_array($record) || $record === []) {
            return false;
        }

        return $backendUser->doesUserHaveAccess($record, Core\Type\Bitmask\Permission::PAGE_SHOW);
    }

    private static function isPageAccessible(int $pageId): bool
    {
        $backendUser = Utility\BackendUtility::getBackendUser();

        if ($backendUser->isAdmin()) {
            return true;
        }

        $userTsConfig = $backendUser->getTSConfig();
        $allowedPages = Core\Utility\GeneralUtility::trimExplode(',', (string)($userTsConfig['options.']['cacheWarmup.']['allowedPages'] ?? ''), true);

        // Early return if no allowed pages are configured
        if ($allowedPages === []) {
            return false;
        }

        // Fetch rootline of current page id
        $rootline = Core\Utility\GeneralUtility::makeInstance(Core\Utility\RootlineUtility::class, $pageId)->get();
        $rootlineIds = array_column($rootline, 'uid');

        foreach ($allowedPages as $allowedPage) {
            $recursiveLookup = str_ends_with($allowedPage, '+');
            $normalizedPageId = rtrim($allowedPage, '+');

            // Continue if configured page is not numeric
            if (!is_numeric($normalizedPageId)) {
                continue;
            }

            // Check if configured page id matches current page id
            if ((int)$normalizedPageId === $pageId) {
                return true;
            }

            // Check if current page is in rootline of configured page id
            if ($recursiveLookup && \in_array((int)$normalizedPageId, $rootlineIds, true)) {
                return true;
            }
        }

        return false;
    }

    private static function isSiteAccessible(string $siteIdentifier): bool
    {
        $backendUser = Utility\BackendUtility::getBackendUser();

        if ($backendUser->isAdmin()) {
            return true;
        }

        $userTsConfig = $backendUser->getTSConfig();
        $allowedSites = (string)($userTsConfig['options.']['cacheWarmup.']['allowedSites'] ?? '');

        return Core\Utility\GeneralUtility::inList($allowedSites, $siteIdentifier);
    }

    private static function isLanguageAccessible(int $languageId): bool
    {
        $backendUser = Utility\BackendUtility::getBackendUser();

        if ($backendUser->isAdmin()) {
            return true;
        }

        return $backendUser->checkLanguageAccess($languageId);
    }
}
