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

namespace EliasHaeussler\Typo3Warming\EventListener;

use EliasHaeussler\Typo3Warming\Backend\Action;
use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Utility;
use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;

/**
 * LiveSearchResultItemListener
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class LiveSearchResultItemListener
{
    public function __construct(
        private Action\WarmupActionsProvider $actionsProvider,
        private Configuration\Configuration $configuration,
        private Core\Imaging\IconFactory $iconFactory,
    ) {}

    #[Core\Attribute\AsEventListener('eliashaeussler/typo3-warming/live-search-result-item')]
    public function __invoke(Backend\Search\Event\ModifyResultItemInLiveSearchEvent $event): void
    {
        // Early return if feature is disabled
        if (!$this->configuration->enabledInLiveSearch) {
            return;
        }

        $resultItem = $event->getResultItem();

        // Only page records are supported
        if ($resultItem->getProviderClassName() !== Backend\Search\LiveSearch\PageRecordProvider::class) {
            return;
        }

        $row = $resultItem->getInternalData()['row'] ?? [];
        $pageId = (int)($row['uid'] ?? 0);
        $languageId = (int)($row['sys_language_uid'] ?? 0);

        // Resolve page id from default language
        if ($languageId > 0) {
            $pageId = (int)($row['l10n_parent'] ?? 0);
        }

        // Early return if invalid page uid is given
        if ($pageId <= 0) {
            return;
        }

        $siteActions = $this->actionsProvider->provideSiteActions($pageId);
        $pageActions = $this->actionsProvider->providePageActions($pageId);
        $languageService = Utility\BackendUtility::getLanguageService();
        $extraData = $resultItem->getExtraData();

        // Add action to warm up all caches (site-based cache warmup)
        foreach ($siteActions->siteLanguages ?? [] as $siteLanguage) {
            if ($siteLanguage->getLanguageId() !== $languageId) {
                continue;
            }

            // Provide site metadata
            $extraData['siteIdentifier'] = $siteActions?->site->getIdentifier();
            $extraData['languageId'] = $languageId;

            // Create result item action
            $siteContext = Action\WarmupActionContext::Site;
            $resultItemAction = new Backend\Search\LiveSearch\ResultItemAction($siteContext->callbackAction());
            $resultItemAction->setLabel($languageService->sL($siteContext->label()));
            $resultItemAction->setIcon(
                $this->iconFactory->getIcon($siteContext->icon(), Core\Imaging\IconSize::SMALL),
            );

            $resultItem->addAction($resultItemAction);

            break;
        }

        // Add action to warm up page caches (page-based cache warmup)
        foreach ($pageActions->siteLanguages ?? [] as $siteLanguage) {
            if ($siteLanguage->getLanguageId() !== $languageId) {
                continue;
            }

            // Provide page metadata
            $extraData['pageId'] = $pageId;
            $extraData['languageId'] = $languageId;

            // Create result item action
            $pageContext = Action\WarmupActionContext::Page;
            $resultItemAction = new Backend\Search\LiveSearch\ResultItemAction($pageContext->callbackAction());
            $resultItemAction->setLabel($languageService->sL($pageContext->label()));
            $resultItemAction->setIcon(
                $this->iconFactory->getIcon($pageContext->icon(), Core\Imaging\IconSize::SMALL),
            );

            $resultItem->addAction($resultItemAction);

            break;
        }

        $resultItem->setExtraData($extraData);
    }
}
