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

namespace EliasHaeussler\Typo3Warming\Http\Message;

use EliasHaeussler\Typo3Warming\Security;
use Psr\Http\Message;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;

/**
 * PageUriBuilder
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class PageUriBuilder
{
    public function __construct(
        #[DependencyInjection\Attribute\Lazy]
        private Core\Domain\Repository\PageRepository $pageRepository,
        private Security\WarmupPermissionGuard $permissionGuard,
        private Core\Site\SiteFinder $siteFinder,
    ) {}

    /**
     * @param positive-int $pageId
     */
    public function build(int $pageId, ?int $languageId = null): ?Message\UriInterface
    {
        // Early return if page access is denied
        if (!$this->permissionGuard->canWarmupCacheOfPage($pageId, new Security\Context\PermissionContext($languageId))) {
            return null;
        }

        $page = $this->pageRepository->getPage($pageId);

        // Early return if page does not exist
        if ($page === []) {
            return null;
        }

        // Fetch page in default language, if necessary
        if (($page['sys_language_uid'] ?? 0) > 0) {
            return $this->build($page['l10n_parent'], $languageId);
        }

        try {
            // We don't use SiteRepository here, because it would return NULL on inaccessible pages,
            // which is undesirable here, because we're in page context (not site context)
            $site = $this->siteFinder->getSiteByPageId($pageId);
        } catch (Core\Exception\SiteNotFoundException) {
            return null;
        }

        // Resolve site language
        if ($languageId !== null) {
            try {
                $siteLanguage = $site->getLanguageById($languageId);
            } catch (\InvalidArgumentException) {
                return null;
            }
        } else {
            $siteLanguage = $site->getDefaultLanguage();
        }

        // Check if page is suitable for language
        if ($siteLanguage->getLanguageId() > 0) {
            $languageAspect = Core\Context\LanguageAspectFactory::createFromSiteLanguage($siteLanguage);
            $page = $this->pageRepository->getLanguageOverlay('pages', $page, $languageAspect);

            if ($page === null || !$this->pageRepository->isPageSuitableForLanguage($page, $languageAspect)) {
                return null;
            }
        }

        return $site->getRouter()->generateUri((string)$pageId, ['_language' => $siteLanguage]);
    }
}
