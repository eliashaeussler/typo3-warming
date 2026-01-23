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

namespace EliasHaeussler\Typo3Warming\Result;

use EliasHaeussler\Typo3Warming\Enums;
use EliasHaeussler\Typo3Warming\Exception;
use EliasHaeussler\Typo3Warming\Utility;
use EliasHaeussler\Typo3Warming\ValueObject;
use TYPO3\CMS\Backend;

/**
 * ResultNotificationBuilder
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final class ResultNotificationBuilder
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private static array $resolvedPageRecords = [];

    /**
     * @return array<string>
     */
    public function buildMessages(ValueObject\Request\WarmupRequest $request, CacheWarmupResult $result): array
    {
        $messages = [];
        $emptyMessage = $this->translate('notification.message.empty');

        $sites = \array_unique($request->getSites(), SORT_REGULAR);
        $pages = \array_unique($request->getPages(), SORT_REGULAR);

        foreach ($sites as $siteWarmupRequest) {
            foreach ($siteWarmupRequest->getLanguageIds() as $languageId) {
                $site = $siteWarmupRequest->getSite();
                $siteLanguage = $site->getLanguageById($languageId);

                ['successful' => $successful, 'failed' => $failed] = $result->getCrawlingResultsBySite(
                    $site,
                    $siteLanguage,
                );

                $messages[] = $this->translate('notification.message.site', [
                    $this->getPageTitle($site->getRootPageId(), $languageId),
                    $this->resolvePageId($site->getRootPageId(), $languageId),
                    $siteLanguage->getTitle(),
                    $languageId,
                    \count($successful),
                    \count($failed),
                ]);
            }
        }

        foreach ($pages as $pageWarmupRequest) {
            $languageIds = $pageWarmupRequest->getLanguageIds();

            if ($languageIds === []) {
                $languageIds = [null];
            }

            foreach ($languageIds as $languageId) {
                ['successful' => $successful, 'failed' => $failed] = $result->getCrawlingResultsByPage(
                    $pageWarmupRequest->getPage(),
                    languageId: $languageId,
                );

                $state = Enums\WarmupState::fromCrawlingResults($successful, $failed);

                $messages[] = $this->translate('notification.message.page.' . $state->value, [
                    $this->getPageTitle($pageWarmupRequest->getPage(), $languageId),
                    $this->resolvePageId($pageWarmupRequest->getPage(), $languageId),
                ]);
            }
        }

        // Remove invalid messages
        $messages = array_filter($messages, static fn(string $message) => \trim($message) !== '');

        // Handle no cache warmup
        if ($messages === []) {
            $messages[] = $emptyMessage;
        }

        return $messages;
    }

    private function getPageTitle(int $pageId, ?int $languageId): string
    {
        $record = $this->getPageRecord($pageId, $languageId);

        return Backend\Utility\BackendUtility::getRecordTitle('pages', $record);
    }

    private function resolvePageId(int $pageId, ?int $languageId): int
    {
        $record = $this->getPageRecord($pageId, $languageId);

        return (int)($record['uid'] ?? 0);
    }

    /**
     * @return array<string, mixed>
     * @throws Exception\MissingPageIdException
     */
    private function getPageRecord(int $pageId, ?int $languageId): array
    {
        $cacheIdentifier = $pageId . '_' . $languageId;

        // Return page record from cache, if available
        if (isset(self::$resolvedPageRecords[$cacheIdentifier])) {
            return self::$resolvedPageRecords[$cacheIdentifier];
        }

        // Fetch localized page, if requested
        if ($languageId > 0) {
            $record = Backend\Utility\BackendUtility::getRecordLocalization('pages', $pageId, $languageId);
        } else {
            $record = Backend\Utility\BackendUtility::getRecord('pages', $pageId);
        }

        // Fail if page record could not be fetched
        if (!is_array($record) || $record === []) {
            throw Exception\MissingPageIdException::create();
        }

        // Use first record from list of localized pages
        if (\array_is_list($record)) {
            $record = \reset($record);
        }

        return self::$resolvedPageRecords[$cacheIdentifier] = $record;
    }

    /**
     * @param list<scalar> $arguments
     */
    private function translate(string $key, array $arguments = []): string
    {
        $languageService = Utility\BackendUtility::getLanguageService();
        $translation = $languageService->sL('LLL:EXT:warming/Resources/Private/Language/locallang.xlf:' . $key);

        return vsprintf($translation, $arguments);
    }
}
