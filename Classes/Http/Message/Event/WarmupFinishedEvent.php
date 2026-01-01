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

namespace EliasHaeussler\Typo3Warming\Http\Message\Event;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\SSE;
use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Enums;
use EliasHaeussler\Typo3Warming\Exception;
use EliasHaeussler\Typo3Warming\Result;
use EliasHaeussler\Typo3Warming\ValueObject;

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
        $state = Enums\WarmupState::fromCacheWarmupResult($this->result);

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
            'messages' => (new Result\ResultNotificationBuilder())->buildMessages($this->request, $this->result),
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
}
