<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Utility;

use Psr\Http\Message;
use TYPO3\CMS\Core;

/**
 * HttpUtility
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class HttpUtility
{
    /**
     * @throws Core\Exception\SiteNotFoundException
     */
    public static function generateUri(int $pageId, int $languageId = null): ?Message\UriInterface
    {
        $pageRepository = Core\Utility\GeneralUtility::makeInstance(Core\Domain\Repository\PageRepository::class);
        $siteFinder = Core\Utility\GeneralUtility::makeInstance(Core\Site\SiteFinder::class);
        $page = $pageRepository->getPage($pageId);

        // Early return if page does not exist
        if ($page === []) {
            return null;
        }

        // Resolve site and site language
        $site = $siteFinder->getSiteByPageId($pageId);
        $siteLanguage = $languageId !== null ? $site->getLanguageById($languageId) : $site->getDefaultLanguage();

        // Check if page is suitable for language
        if ($languageId > 0) {
            $languageAspect = Core\Context\LanguageAspectFactory::createFromSiteLanguage($siteLanguage);
            $page = $pageRepository->getLanguageOverlay('pages', $page, $languageAspect);

            if ($page === null || !$pageRepository->isPageSuitableForLanguage($page, $languageAspect)) {
                return null;
            }
        }

        return $site->getRouter()->generateUri((string)$pageId, ['_language' => $siteLanguage]);
    }
}
