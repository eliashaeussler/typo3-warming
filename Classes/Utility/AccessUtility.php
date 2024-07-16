<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Utility;

use EliasHaeussler\Typo3Warming\Traits\BackendUserAuthenticationTrait;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

/**
 * AccessUtility
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class AccessUtility
{
    use BackendUserAuthenticationTrait;

    public static function canWarmupCacheOfPage(int $pageId, int $languageId = null): bool
    {
        return self::checkPagePermissions($pageId, $languageId)
            && self::isPageAccessible($pageId)
            && ($languageId !== null ? self::isLanguageAccessible($languageId) : true);
    }

    public static function canWarmupCacheOfSite(Site $site, int $languageId = null): bool
    {
        return self::checkPagePermissions($site->getRootPageId(), $languageId)
            && self::isSiteAccessible($site->getIdentifier())
            && ($languageId !== null ? self::isLanguageAccessible($languageId) : true);
    }

    private static function checkPagePermissions(int $pageId, int $languageId = null, int $permissions = Permission::PAGE_SHOW): bool
    {
        $backendUser = self::getBackendUser();

        // Fetch record and record localization (if language is given and is not default language),
        // additionally check for available pages by adding hidden=0 as additional WHERE clause
        $record = BackendUtility::getRecord('pages', $pageId, '*', 'hidden = 0');
        if ($languageId !== null && $languageId > 0) {
            $record = BackendUtility::getRecordLocalization('pages', $pageId, $languageId, 'hidden = 0');
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

        return $backendUser->doesUserHaveAccess($record, $permissions);
    }

    private static function isPageAccessible(int $pageId): bool
    {
        $backendUser = self::getBackendUser();

        if ($backendUser->isAdmin()) {
            return true;
        }

        $userTsConfig = $backendUser->getTSConfig();
        $allowedPages = GeneralUtility::trimExplode(',', (string)($userTsConfig['options.']['cacheWarmup.']['allowedPages'] ?? ''), true);

        // Early return if no allowed pages are configured
        if ($allowedPages === []) {
            return false;
        }

        // Fetch rootline of current page id
        $rootline = GeneralUtility::makeInstance(RootlineUtility::class, $pageId)->get();
        $rootlineIds = array_column($rootline, 'uid');

        foreach ($allowedPages as $allowedPage) {
            $recursiveLookup = str_ends_with($allowedPage, '+');
            $normalizedPageId = rtrim($allowedPage, '+');

            // Continue if configured page must not be checked recursively
            // or configured page is not numeric
            if (!$recursiveLookup || !is_numeric($normalizedPageId)) {
                continue;
            }

            // Check if configured page id matches current page id
            if ((int)$normalizedPageId === $pageId) {
                return true;
            }

            // Check if current page is in rootline of configured page id
            if (\in_array((int)$normalizedPageId, $rootlineIds, true)) {
                return true;
            }
        }

        return false;
    }

    private static function isSiteAccessible(string $siteIdentifier): bool
    {
        $backendUser = self::getBackendUser();

        if ($backendUser->isAdmin()) {
            return true;
        }

        $userTsConfig = $backendUser->getTSConfig();
        $allowedSites = (string)($userTsConfig['options.']['cacheWarmup.']['allowedSites'] ?? '');

        return GeneralUtility::inList($allowedSites, $siteIdentifier);
    }

    private static function isLanguageAccessible(int $languageId): bool
    {
        $backendUser = self::getBackendUser();

        if ($backendUser->isAdmin()) {
            return true;
        }

        return $backendUser->checkLanguageAccess($languageId);
    }
}
