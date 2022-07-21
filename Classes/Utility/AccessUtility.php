<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
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

/**
 * AccessUtility
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class AccessUtility
{
    use BackendUserAuthenticationTrait;

    public static function canWarmupCacheOfPage(int $pageId, int $languageId = null): bool
    {
        return static::checkPagePermissions($pageId, $languageId)
            && static::isPageAccessible($pageId)
            && ($languageId !== null ? static::isLanguageAccessible($languageId) : true);
    }

    public static function canWarmupCacheOfSite(Site $site, int $languageId = null): bool
    {
        return static::checkPagePermissions($site->getRootPageId(), $languageId)
            && static::isSiteAccessible($site->getIdentifier())
            && ($languageId !== null ? static::isLanguageAccessible($languageId) : true);
    }

    protected static function checkPagePermissions(int $pageId, int $languageId = null, int $permissions = Permission::PAGE_SHOW): bool
    {
        $backendUser = static::getBackendUser();

        if ($languageId === null && $backendUser->isAdmin()) {
            return true;
        }

        // Fetch record and record localization (if language is given and is not default language),
        // additionally check for available pages by adding hidden=0 as additional WHERE clause
        $record = BackendUtility::getRecord('pages', $pageId, '*', 'hidden = 0');
        if ($languageId !== null && $languageId > 0) {
            $record = BackendUtility::getRecordLocalization('pages', $pageId, $languageId, 'hidden = 0');
        }

        return !empty($record) && $backendUser->doesUserHaveAccess($record, $permissions);
    }

    protected static function isPageAccessible(int $pageId): bool
    {
        $backendUser = static::getBackendUser();

        if ($backendUser->isAdmin()) {
            return true;
        }

        $userTsConfig = $backendUser->getTSConfig();
        $allowedPages = (string)($userTsConfig['options.']['cacheWarmup.']['allowedPages'] ?? '');

        return GeneralUtility::inList($allowedPages, (string)$pageId);
    }

    protected static function isSiteAccessible(string $siteIdentifier): bool
    {
        $backendUser = static::getBackendUser();

        if ($backendUser->isAdmin()) {
            return true;
        }

        $userTsConfig = $backendUser->getTSConfig();
        $allowedSites = (string)($userTsConfig['options.']['cacheWarmup.']['allowedSites'] ?? '');

        return GeneralUtility::inList($allowedSites, $siteIdentifier);
    }

    protected static function isLanguageAccessible(int $languageId): bool
    {
        $backendUser = static::getBackendUser();

        if ($backendUser->isAdmin()) {
            return true;
        }

        return $backendUser->checkLanguageAccess($languageId);
    }
}
