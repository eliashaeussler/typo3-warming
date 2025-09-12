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

namespace EliasHaeussler\Typo3Warming\Http\Message\Event;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\SSE;
use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Enums;
use EliasHaeussler\Typo3Warming\Exception;
use EliasHaeussler\Typo3Warming\Result;
use EliasHaeussler\Typo3Warming\ValueObject;
use TYPO3\CMS\Backend;

/**
 * WarmupFinishedEvent
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final readonly class WarmupFinishedEvent implements SSE\Event\Event
{
    public function __construct(
        private ValueObject\Request\WarmupRequest $request,
        private Result\CacheWarmupResult $result,
    ) {}

    public function getName(): string
    {
        return 'warmupFinished';
    }

    /**
     * @return array{
     *     state: string,
     *     title: string|null,
     *     progress: array{
     *         current: int,
     *         total: int,
     *     },
     *     results: array{
     *         failed: list<array{url: string, data: array<string, mixed>}>,
     *         successful: list<array{url: string, data: array<string, mixed>}>,
     *     },
     *     excluded: array{
     *         sitemaps: list<string>,
     *         urls: list<string>,
     *     },
     *     messages: array<string>,
     * }
     * @throws Exception\MissingPageIdException
     */
    public function getData(): array
    {
        $state = $this->determineWarmupState();

        $failedUrls = $this->result->getResult()->getFailed();
        $successfulUrls = $this->result->getResult()->getSuccessful();

        return [
            'state' => $state->value,
            'title' => Configuration\Localization::translate('notification.title.' . $state->value),
            'progress' => [
                'current' => \count($failedUrls) + \count($successfulUrls),
                'total' => \count($failedUrls) + \count($successfulUrls),
            ],
            'results' => [
                'failed' => array_map($this->decorateResult(...), $failedUrls),
                'successful' => array_map($this->decorateResult(...), $successfulUrls),
            ],
            'excluded' => [
                'sitemaps' => array_map(strval(...), $this->result->getExcludedSitemaps()),
                'urls' => array_map(strval(...), $this->result->getExcludedUrls()),
            ],
            'messages' => $this->buildMessages($state),
        ];
    }

    /**
     * @return array<string, mixed>
     * @throws Exception\MissingPageIdException
     */
    public function jsonSerialize(): array
    {
        return $this->getData();
    }

    private function determineWarmupState(): Enums\WarmupState
    {
        $failed = \count($this->result->getResult()->getFailed());
        $successful = \count($this->result->getResult()->getSuccessful());

        if ($failed > 0 && $successful === 0) {
            return Enums\WarmupState::Failed;
        }

        if ($failed > 0 && $successful > 0) {
            return Enums\WarmupState::Warning;
        }

        if ($failed === 0) {
            return Enums\WarmupState::Success;
        }

        return Enums\WarmupState::Unknown;
    }

    /**
     * @return array{url: string, data: array<string, mixed>}
     */
    private function decorateResult(CacheWarmup\Result\CrawlingResult $result): array
    {
        return [
            'url' => (string)$result->getUri(),
            'data' => $result->getData(),
        ];
    }

    /**
     * @return array<string>
     * @throws Exception\MissingPageIdException
     */
    private function buildMessages(Enums\WarmupState $state): array
    {
        $messages = [];
        $emptyMessage = Configuration\Localization::translate('notification.message.empty');

        foreach ($this->request->getSites() as $siteWarmupRequest) {
            foreach ($siteWarmupRequest->getLanguageIds() as $languageId) {
                $site = $siteWarmupRequest->getSite();
                $siteLanguage = $site->getLanguageById($languageId);

                ['successful' => $successful, 'failed' => $failed] = $this->result->getCrawlingResultsBySite(
                    $site,
                    $siteLanguage,
                );

                $messages[] = Configuration\Localization::translate('notification.message.site', [
                    $this->getPageTitle($site->getRootPageId(), $siteLanguage->getLanguageId()),
                    $site->getRootPageId(),
                    $siteLanguage->getTitle(),
                    $languageId,
                    \count($successful),
                    \count($failed),
                ]);
            }
        }

        foreach ($this->request->getPages() as $pageWarmupRequest) {
            $languageIds = $pageWarmupRequest->getLanguageIds();

            if ($languageIds === []) {
                $languageIds = [null];
            }

            foreach ($languageIds as $languageId) {
                $messages[] = Configuration\Localization::translate('notification.message.page.' . $state->value, [
                    $this->getPageTitle($pageWarmupRequest->getPage(), $languageId),
                    $pageWarmupRequest->getPage(),
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

    /**
     * @throws Exception\MissingPageIdException
     */
    private function getPageTitle(int $pageId, ?int $languageId): string
    {
        if ($languageId > 0) {
            $record = Backend\Utility\BackendUtility::getRecordLocalization('pages', $pageId, $languageId);
        } else {
            $record = Backend\Utility\BackendUtility::getRecord('pages', $pageId);
        }

        if (!is_array($record) || $record === []) {
            throw Exception\MissingPageIdException::create();
        }

        if (\array_is_list($record)) {
            $record = \reset($record);
        }

        return Backend\Utility\BackendUtility::getRecordTitle('pages', $record);
    }
}
