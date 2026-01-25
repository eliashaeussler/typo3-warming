<?php

declare(strict_types=1);

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

namespace EliasHaeussler\Typo3Warming\Form\FormDataProvider;

use EliasHaeussler\Typo3Warming\Domain;
use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;

/**
 * SimulateLogPageRow
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final class SimulateLogPageRow implements Backend\Form\FormDataProviderInterface
{
    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    public function addData(array $result): array
    {
        $tableName = $result['tableName'] ?? null;
        $databaseRow = $result['databaseRow'] ?? null;

        // Only log tables are handled by this data provider
        if ($tableName !== Domain\Model\Log::TABLE_NAME) {
            return $result;
        }

        // Early return if database row is an unexpected value
        if (!is_array($databaseRow)) {
            return $result;
        }

        $rootPageId = $databaseRow['site'] ?? null;
        $url = $databaseRow['url'] ?? null;

        if (is_string($url)) {
            $result['databaseRow']['pid'] = $this->resolvePageId($url) ?? $rootPageId;
        }

        return $result;
    }

    private function resolvePageId(string $url): ?int
    {
        $request = new Core\Http\ServerRequest($url);

        $siteMatcher = Core\Utility\GeneralUtility::makeInstance(Core\Routing\SiteMatcher::class);
        $routeResult = $siteMatcher->matchRequest($request);

        if (!($routeResult instanceof Core\Routing\SiteRouteResult) || !($routeResult->getSite() instanceof Core\Site\Entity\Site)) {
            return null;
        }

        $pageArguments = $routeResult->getSite()->getRouter()->matchRequest($request, $routeResult);

        if (!($pageArguments instanceof Core\Routing\PageArguments)) {
            return null;
        }

        return $pageArguments->getPageId();
    }
}
