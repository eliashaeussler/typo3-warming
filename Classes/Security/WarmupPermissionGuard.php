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

namespace EliasHaeussler\Typo3Warming\Security;

use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;

/**
 * WarmupPermissionGuard
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class WarmupPermissionGuard
{
    public function __construct(
        #[DependencyInjection\Attribute\Autowire('@cache.runtime')]
        private readonly Core\Cache\Frontend\FrontendInterface $cache,
    ) {}

    public function canWarmupCacheOfPage(
        int $pageId,
        Context\PermissionContext $context = new Context\PermissionContext(),
    ): bool {
        return $this->getFromCache(
            ['canWarmupCacheOfPage', $pageId, $context],
            fn() => $this->hasPageAccess($pageId, $context)
                && $this->isAllowedPage($pageId, $context)
                && $this->hasLanguageAccess($context),
        );
    }

    public function canWarmupCacheOfSite(
        Core\Site\Entity\Site $site,
        Context\PermissionContext $context = new Context\PermissionContext(),
    ): bool {
        return $this->getFromCache(
            ['canWarmupCacheOfSite', $site, $context],
            fn() => $this->hasPageAccess($site->getRootPageId(), $context)
                && $this->isAllowedSite($site->getIdentifier(), $context)
                && $this->hasLanguageAccess($context),
        );
    }

    private function hasPageAccess(int $pageId, Context\PermissionContext $context): bool
    {
        $record = Backend\Utility\BackendUtility::getRecord('pages', $pageId, '*', 'hidden = 0');

        // Fetch record localization (if language is given and is not default language),
        // additionally check for available pages by adding hidden=0 as additional WHERE clause
        if ($context->languageId !== null && $context->languageId > 0) {
            $record = Backend\Utility\BackendUtility::getRecordLocalization('pages', $pageId, $context->languageId, 'hidden = 0');
        }

        // Early return if record is inaccessible
        if (!\is_array($record) || $record === []) {
            return false;
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

        // Early return if backend user has admin privileges
        if ($context->backendUser->isAdmin()) {
            return true;
        }

        return $context->backendUser->doesUserHaveAccess($record, Core\Type\Bitmask\Permission::PAGE_SHOW);
    }

    private function hasLanguageAccess(Context\PermissionContext $context): bool
    {
        if ($context->languageId === null) {
            return true;
        }

        if ($context->backendUser->isAdmin()) {
            return true;
        }

        return $context->backendUser->checkLanguageAccess($context->languageId);
    }

    private function isAllowedPage(int $pageId, Context\PermissionContext $context): bool
    {
        if ($context->backendUser->isAdmin()) {
            return true;
        }

        $userTsConfig = $context->backendUser->getTSConfig();
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

    private function isAllowedSite(string $siteIdentifier, Context\PermissionContext $context): bool
    {
        if ($context->backendUser->isAdmin()) {
            return true;
        }

        $userTsConfig = $context->backendUser->getTSConfig();
        $allowedSites = (string)($userTsConfig['options.']['cacheWarmup.']['allowedSites'] ?? '');

        return Core\Utility\GeneralUtility::inList($allowedSites, $siteIdentifier);
    }

    /**
     * @param list<mixed> $identifiers
     * @param \Closure(): bool $fn
     */
    private function getFromCache(array $identifiers, \Closure $fn): bool
    {
        $identifier = 'warming_warmupPermissionGuard_' . sha1(serialize($identifiers));
        $result = null;

        if ($this->cache->has($identifier)) {
            $result = $this->cache->get($identifier);
        }

        if (!is_bool($result)) {
            $result = $fn();
        }

        $this->cache->set($identifier, $result);

        return $result;
    }
}
