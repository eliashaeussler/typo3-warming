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

namespace EliasHaeussler\Typo3Warming\Queue;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming\Result;
use EliasHaeussler\Typo3Warming\Service;
use EliasHaeussler\Typo3Warming\ValueObject;
use GuzzleHttp\Exception;
use TYPO3\CMS\Core;

/**
 * CacheWarmupQueue
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final class CacheWarmupQueue implements Core\SingletonInterface
{
    /**
     * @var list<ValueObject\Request\SiteWarmupRequest>
     */
    private array $siteWarmupRequests = [];

    /**
     * @var list<ValueObject\Request\PageWarmupRequest>
     */
    private array $pageWarmupRequests = [];

    public function __construct(
        private readonly Service\CacheWarmupService $cacheWarmupService,
    ) {}

    public function enqueue(
        ValueObject\Request\SiteWarmupRequest|ValueObject\Request\PageWarmupRequest $cacheWarmupRequest,
    ): self {
        if ($cacheWarmupRequest instanceof ValueObject\Request\SiteWarmupRequest) {
            $this->siteWarmupRequests[] = $cacheWarmupRequest;
        }

        if ($cacheWarmupRequest instanceof ValueObject\Request\PageWarmupRequest) {
            $this->pageWarmupRequests[] = $cacheWarmupRequest;
        }

        return $this;
    }

    /**
     * @throws CacheWarmup\Exception\Exception
     * @throws Typo3SitemapLocator\Exception\BaseUrlIsNotSupported
     * @throws Typo3SitemapLocator\Exception\SitemapIsMissing
     * @throws Exception\GuzzleException
     */
    public function process(): ?Result\CacheWarmupResult
    {
        // Early return if no cache warmup requests were enqueued
        if ($this->isEmpty()) {
            return null;
        }

        try {
            return $this->cacheWarmupService->warmup($this->siteWarmupRequests, $this->pageWarmupRequests);
        } finally {
            $this->clear();
        }
    }

    public function wrapInWarmupRequest(): ValueObject\Request\WarmupRequest
    {
        return new ValueObject\Request\WarmupRequest(
            /* @phpstan-ignore argument.type (until return type annotation is fixed in core) */
            Core\Utility\StringUtility::getUniqueId(),
            $this->siteWarmupRequests,
            $this->pageWarmupRequests,
        );
    }

    /**
     * @phpstan-assert-if-true null $this->process()
     */
    public function isEmpty(): bool
    {
        return $this->pageWarmupRequests === [] && $this->siteWarmupRequests === [];
    }

    private function clear(): void
    {
        $this->siteWarmupRequests = [];
        $this->pageWarmupRequests = [];
    }
}
