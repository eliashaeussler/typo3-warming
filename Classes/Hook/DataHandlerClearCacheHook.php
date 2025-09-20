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

namespace EliasHaeussler\Typo3Warming\Hook;

use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Queue;
use EliasHaeussler\Typo3Warming\ValueObject;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;

/**
 * DataHandlerClearCacheHook
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 *
 * @phpstan-type HookParams array{table?: non-empty-string, uid?: positive-int, uid_page?: positive-int}
 */
#[DependencyInjection\Attribute\Autoconfigure(public: true)]
final readonly class DataHandlerClearCacheHook
{
    public function __construct(
        private Configuration\Configuration $configuration,
        private Queue\CacheWarmupQueue $queue,
    ) {}

    /**
     * @param HookParams $params
     */
    public function warmupPageCache(array $params): void
    {
        // Early return if hook is disabled
        if (!$this->configuration->runAfterCacheClear) {
            return;
        }

        [$pageUid, $languageIds] = $this->resolveCacheWarmupParameters($params);

        if ($pageUid !== null) {
            $this->queue->enqueue(new ValueObject\Request\PageWarmupRequest($pageUid, $languageIds));
        }
    }

    /**
     * @param HookParams $params
     * @return array{positive-int|null, array{0?: non-negative-int}}
     */
    private function resolveCacheWarmupParameters(array $params): array
    {
        $table = $params['table'] ?? null;
        $recordUid = $params['uid'] ?? null;
        $unsupportedParameters = [null, []];

        // Early return if table or record uid is not available
        if ($table === null || $recordUid === null) {
            return $unsupportedParameters;
        }

        if ($table === 'pages') {
            if ($this->isSupportedPage($recordUid)) {
                return [$recordUid, []];
            }

            return $unsupportedParameters;
        }

        $pageUid = $params['uid_page'] ?? null;

        if (!$this->isSupportedPage($pageUid)) {
            return $unsupportedParameters;
        }

        $record = Backend\Utility\BackendUtility::getRecord($table, $recordUid);

        // Early return if record cannot be resolved
        if ($record === null) {
            return [$pageUid, []];
        }

        if (\class_exists(Core\Schema\TcaSchemaFactory::class)) {
            // @todo Use DI once support for TYPO3 v12 is dropped
            $tcaSchemaFactory = Core\Utility\GeneralUtility::makeInstance(Core\Schema\TcaSchemaFactory::class);
            $tcaSchema = $tcaSchemaFactory->has($table) ? $tcaSchemaFactory->get($table) : null;

            if ($tcaSchema?->isLanguageAware() === true) {
                $languageField = $tcaSchema->getCapability(Core\Schema\Capability\TcaSchemaCapability::Language)->getLanguageField()->getName();
            } else {
                $languageField = null;
            }
        } else {
            // @todo Remove once support for TYPO3 v12 is dropped
            $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'] ?? null;
        }

        if ($languageField !== null) {
            $languageIds = [$record[$languageField] ?? 0];
        } else {
            $languageIds = [];
        }

        return [$pageUid, $languageIds];
    }

    private function isSupportedPage(?int $pageId): bool
    {
        if ($pageId === null) {
            return false;
        }

        $pageRecord = Backend\Utility\BackendUtility::getRecord('pages', $pageId);

        return $pageRecord !== null
            && in_array($pageRecord['doktype'] ?? 0, $this->configuration->supportedDoktypes, true)
        ;
    }
}
