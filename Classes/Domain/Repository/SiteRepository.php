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

namespace EliasHaeussler\Typo3Warming\Domain\Repository;

use EliasHaeussler\Typo3Warming\Security;
use TYPO3\CMS\Core;

/**
 * SiteRepository
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class SiteRepository
{
    public function __construct(
        private Core\Site\SiteFinder $siteFinder,
        private Security\WarmupPermissionGuard $permissionGuard,
    ) {}

    /**
     * @return array<string, Core\Site\Entity\Site>
     */
    public function findAll(): array
    {
        $sites = $this->siteFinder->getAllSites();
        $resolvedSites = [];

        foreach ($sites as $site) {
            if ($this->isAccessible($site)) {
                $resolvedSites[$site->getIdentifier()] = $site;
            }

        }

        return $resolvedSites;
    }

    public function countAll(): int
    {
        return count($this->findAll());
    }

    /**
     * @param non-negative-int $rootPageId
     */
    public function findOneByRootPageId(int $rootPageId): ?Core\Site\Entity\Site
    {
        try {
            $site = $this->siteFinder->getSiteByRootPageId($rootPageId);
        } catch (Core\Exception\SiteNotFoundException) {
            return null;
        }

        if ($this->isAccessible($site)) {
            return $site;
        }

        return null;
    }

    /**
     * @param non-negative-int $pageId
     */
    public function findOneByPageId(int $pageId): ?Core\Site\Entity\Site
    {
        try {
            $site = $this->siteFinder->getSiteByPageId($pageId);
        } catch (Core\Exception\SiteNotFoundException) {
            return null;
        }

        if ($this->isAccessible($site)) {
            return $site;
        }

        return null;
    }

    public function findOneByIdentifier(string $identifier): ?Core\Site\Entity\Site
    {
        try {
            $site = $this->siteFinder->getSiteByIdentifier($identifier);
        } catch (Core\Exception\SiteNotFoundException) {
            return null;
        }

        if ($this->isAccessible($site)) {
            return $site;
        }

        return null;
    }

    private function isAccessible(Core\Site\Entity\Site $site): bool
    {
        return !$this->isExcluded($site) && $this->permissionGuard->canWarmupCacheOfSite($site);
    }

    private function isExcluded(Core\Site\Entity\Site $site): bool
    {
        $configuration = $site->getConfiguration();

        if (!isset($configuration['warming_exclude'])) {
            return false;
        }

        return (bool)$configuration['warming_exclude'];
    }
}
