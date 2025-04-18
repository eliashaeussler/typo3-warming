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
 * SiteLanguageRepository
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class SiteLanguageRepository
{
    public function __construct(
        private SiteRepository $siteRepository,
        private Security\WarmupPermissionGuard $permissionGuard,
    ) {}

    /**
     * @return array<int, Core\Site\Entity\SiteLanguage>
     */
    public function findAll(Core\Site\Entity\Site $site): array
    {
        if (!$this->isSiteAccessible($site)) {
            return [];
        }

        $resolvedSiteLanguages = [];

        foreach ($site->getLanguages() as $siteLanguage) {
            if ($this->isAccessible($site, $siteLanguage)) {
                $resolvedSiteLanguages[$siteLanguage->getLanguageId()] = $siteLanguage;
            }
        }

        return $resolvedSiteLanguages;
    }

    public function countAll(Core\Site\Entity\Site $site): int
    {
        return count($this->findAll($site));
    }

    public function findOneByLanguageId(Core\Site\Entity\Site $site, int $languageId): ?Core\Site\Entity\SiteLanguage
    {
        if (!$this->isSiteAccessible($site)) {
            return null;
        }

        try {
            $siteLanguage = $site->getLanguageById($languageId);
        } catch (\InvalidArgumentException) {
            return null;
        }

        if (!$this->isAccessible($site, $siteLanguage)) {
            return null;
        }

        return $siteLanguage;
    }

    private function isAccessible(Core\Site\Entity\Site $site, Core\Site\Entity\SiteLanguage $siteLanguage): bool
    {
        $context = new Security\Context\PermissionContext($siteLanguage->getLanguageId());

        return !$this->isExcluded($siteLanguage) && $this->permissionGuard->canWarmupCacheOfSite($site, $context);
    }

    private function isExcluded(Core\Site\Entity\SiteLanguage $siteLanguage): bool
    {
        $configuration = $siteLanguage->toArray();

        if (!isset($configuration['warming_exclude'])) {
            return false;
        }

        return (bool)$configuration['warming_exclude'];
    }

    private function isSiteAccessible(Core\Site\Entity\Site $site): bool
    {
        return $this->siteRepository->findOneByIdentifier($site->getIdentifier()) !== null;
    }
}
