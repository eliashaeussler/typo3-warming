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

namespace EliasHaeussler\Typo3Warming\Backend\Action;

use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Domain;
use EliasHaeussler\Typo3Warming\Security;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;

/**
 * WarmupActionsProvider
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class WarmupActionsProvider
{
    public function __construct(
        private Configuration\Configuration $configuration,
        private Security\WarmupPermissionGuard $permissionGuard,
        private Core\Site\SiteFinder $siteFinder,
        private Domain\Repository\SiteLanguageRepository $siteLanguageRepository,
        private Domain\Repository\SiteRepository $siteRepository,
        private Typo3SitemapLocator\Sitemap\SitemapLocator $sitemapLocator,
        #[DependencyInjection\Attribute\Autowire('@cache.runtime')]
        private Core\Cache\Frontend\FrontendInterface $runtimeCache,
    ) {}

    /**
     * @return ($context is WarmupActionContext::Page ? PageWarmupActions|null : SiteWarmupActions|null)
     */
    public function provideActions(WarmupActionContext $context, int $pageId): PageWarmupActions|SiteWarmupActions|null
    {
        return match ($context) {
            WarmupActionContext::Page => $this->providePageActions($pageId),
            WarmupActionContext::Site => $this->provideSiteActions($pageId),
        };
    }

    public function provideSiteActions(int $pageId): ?SiteWarmupActions
    {
        if (!$this->isValidPage($pageId)) {
            return null;
        }

        $site = $this->siteRepository->findOneByRootPageId($pageId);

        // Early return if no associated site could be found
        if ($site === null) {
            return null;
        }

        // Get all languages of current site that are available for the current backend user
        $siteLanguages = array_filter(
            $this->siteLanguageRepository->findAll($site),
            fn(Core\Site\Entity\SiteLanguage $siteLanguage): bool => $this->canWarmupCachesOfSite($site, $siteLanguage),
        );

        return new SiteWarmupActions($site, $siteLanguages);
    }

    public function providePageActions(int $pageId): ?PageWarmupActions
    {
        if (!$this->isValidPage($pageId)) {
            return null;
        }

        try {
            // We cannot use SiteRepository here because it checks for associated site permissions,
            // where we want to check page permissions only
            $site = $this->siteFinder->getSiteByPageId($pageId);
        } catch (Core\Exception\SiteNotFoundException) {
            // Early return if no associated site could be found
            return null;
        }

        // Get all languages of current site that are available for the current backend user
        $siteLanguages = array_filter(
            $site->getAllLanguages(),
            fn(Core\Site\Entity\SiteLanguage $siteLanguage): bool => $this->permissionGuard->canWarmupCacheOfPage(
                $pageId,
                new Security\Context\PermissionContext($siteLanguage->getLanguageId()),
            ),
        );

        return new PageWarmupActions($pageId, $siteLanguages);
    }

    /**
     * @phpstan-assert-if-true non-negative-int $pageId
     */
    private function isValidPage(int $pageId): bool
    {
        $cacheIdentifier = 'warming_warmupActionsProvider_isValidPage_' . $pageId;

        // Return validation result from cache, if available
        if ($this->runtimeCache->has($cacheIdentifier)) {
            return $this->runtimeCache->get($cacheIdentifier);
        }

        // Root page cannot be used for cache warmup since it is not accessible in Frontend
        if ($pageId === 0) {
            return false;
        }

        $record = Backend\Utility\BackendUtility::getRecordWSOL('pages', $pageId);

        // Early return if page does not exist
        if ($record === null) {
            return false;
        }

        $doktype = (int)($record['doktype'] ?? 0);
        $isValidPage = $doktype > 0 && \in_array($doktype, $this->configuration->supportedDoktypes, true);

        // Store page validation in cache to avoid further lookups
        $this->runtimeCache->set($cacheIdentifier, $isValidPage);

        return $isValidPage;
    }

    private function canWarmupCachesOfSite(Core\Site\Entity\Site $site, Core\Site\Entity\SiteLanguage $siteLanguage): bool
    {
        try {
            // Check if any sitemap exists
            foreach ($this->sitemapLocator->locateBySite($site, $siteLanguage) as $sitemap) {
                if ($this->sitemapLocator->isValidSitemap($sitemap)) {
                    return true;
                }
            }
        } catch (\Exception) {
            // Unable to locate any sitemaps
        }

        return false;
    }
}
