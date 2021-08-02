<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3Warming\Request;

use EliasHaeussler\CacheWarmup\CrawlingState;
use EliasHaeussler\Typo3Warming\Controller\CacheWarmupController;
use Psr\Http\Message\UriInterface;

/**
 * WarmupRequest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class WarmupRequest
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var int|null
     */
    protected $languageId;

    /**
     * @var UriInterface[]
     */
    protected $requestedUrls = [];

    /**
     * @var CrawlingState[]
     */
    protected $crawlingStates = [];

    /**
     * @var callable|null
     */
    protected $updateCallback;

    public function __construct(string $id, string $mode = CacheWarmupController::MODE_SITE, int $languageId = null)
    {
        $this->id = $id;
        $this->mode = $mode;
        $this->languageId = $languageId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getLanguageId(): ?int
    {
        return $this->languageId;
    }

    public function getTotal(): int
    {
        return count($this->requestedUrls);
    }

    public function getProcessed(): int
    {
        return count($this->crawlingStates);
    }

    public function isSuccessful(): bool
    {
        foreach ($this->crawlingStates as $crawlingState) {
            if (!$crawlingState->isSuccessful()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return UriInterface[]
     */
    public function getRequestedUrls(): array
    {
        return $this->requestedUrls;
    }

    /**
     * @param UriInterface[] $requestedUrls
     * @return self
     */
    public function setRequestedUrls(array $requestedUrls): self
    {
        $this->requestedUrls = $requestedUrls;

        return $this;
    }

    /**
     * @return CrawlingState[]
     */
    public function getCrawlingStates(): array
    {
        return $this->crawlingStates;
    }

    public function addCrawlingState(CrawlingState $state): self
    {
        $this->crawlingStates[] = $state;
        $this->triggerUpdate();

        return $this;
    }

    /**
     * @return \Generator<CrawlingState>
     */
    public function getSuccessfulCrawls(): \Generator
    {
        yield from $this->filterByState(CrawlingState::SUCCESSFUL);
    }

    /**
     * @return \Generator<CrawlingState>
     */
    public function getFailedCrawls(): \Generator
    {
        yield from $this->filterByState(CrawlingState::FAILED);
    }

    public function getUpdateCallback(): ?callable
    {
        return $this->updateCallback;
    }

    public function setUpdateCallback(?callable $updateCallback): self
    {
        $this->updateCallback = $updateCallback;

        return $this;
    }

    /**
     * @param int $state
     * @return \Generator<CrawlingState>
     */
    protected function filterByState(int $state): \Generator
    {
        foreach ($this->crawlingStates as $crawlingState) {
            if ($crawlingState->is($state)) {
                yield $crawlingState;
            }
        }
    }

    protected function triggerUpdate(): void
    {
        if (is_callable($this->updateCallback)) {
            ($this->updateCallback)($this);
        }
    }
}
