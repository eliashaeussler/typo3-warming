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

namespace EliasHaeussler\Typo3Warming\Http\Message;

use EliasHaeussler\Typo3Warming\Domain;
use Psr\Http\Message;
use TYPO3\CMS\Core;

/**
 * PageUriBuilder
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class PageUriBuilder
{
    public function __construct(
        private readonly Core\Domain\Repository\PageRepository $pageRepository,
        private readonly Domain\Repository\SiteRepository $siteRepository,
        private readonly Domain\Repository\SiteLanguageRepository $siteLanguageRepository,
    ) {}

    /**
     * @param positive-int $pageId
     */
    public function build(int $pageId, ?int $languageId = null): ?Message\UriInterface
    {
        $page = $this->pageRepository->getPage($pageId);

        // Early return if page does not exist
        if ($page === []) {
            return null;
        }

        // Resolve site
        $site = $this->siteRepository->findOneByPageId($pageId);

        // Early return if site is inaccessible
        if ($site === null) {
            return null;
        }

        // Resolve site language
        $siteLanguage = $this->siteLanguageRepository->findOneByLanguageId(
            $site,
            $languageId ?? $site->getDefaultLanguage()->getLanguageId(),
        );

        // Early return if site language is inaccessible
        if ($siteLanguage === null) {
            return null;
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
