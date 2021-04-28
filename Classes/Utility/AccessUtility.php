<?php

declare(strict_types=1);

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

namespace EliasHaeussler\Typo3Warming\Utility;

use Doctrine\DBAL\FetchMode;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\PagePermissionRestriction;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * AccessUtility
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class AccessUtility
{
    /**
     * @var ConnectionPool
     */
    protected static $connectionPool;

    public static function canWarmupCacheOfPage(int $pageId): bool
    {
        return static::checkPagePermissions($pageId) && static::isPageAccessible($pageId);
    }

    public static function canWarmupCacheOfSite(Site $site): bool
    {
        return static::checkPagePermissions($site->getRootPageId()) && static::isSiteAccessible($site->getIdentifier());
    }

    protected static function checkPagePermissions(int $pageId, int $permissions = 1): bool
    {
        $userAspect = GeneralUtility::makeInstance(Context::class)->getAspect('backend.user');
        $restriction = GeneralUtility::makeInstance(PagePermissionRestriction::class, $userAspect, $permissions);

        $queryBuilder = static::getConnectionPool()->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll()->add($restriction);

        $result = $queryBuilder->count('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($pageId, Connection::PARAM_INT)
                )
            )
            ->execute()
            ->fetch(FetchMode::COLUMN);

        return $result === 1;
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

    protected static function getConnectionPool(): ConnectionPool
    {
        if (static::$connectionPool === null) {
            static::$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        }

        return static::$connectionPool;
    }

    protected static function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
