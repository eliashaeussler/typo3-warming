<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Http\Message\Event;

use EliasHaeussler\SSE;

/**
 * WarmupProgressEvent
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final class WarmupProgressEvent implements SSE\Event\Event
{
    /**
     * @param list<string> $successfulUrls
     * @param list<string> $failedUrls
     */
    public function __construct(
        private readonly string $currentUrl,
        private readonly array $successfulUrls,
        private readonly array $failedUrls,
        private readonly int $numberOfUrls,
    ) {
    }

    public function getName(): string
    {
        return 'warmupProgress';
    }

    /**
     * @return array{
     *     progress: array{
     *         current: int,
     *         total: int,
     *     },
     *     urls: array{
     *         current: string,
     *         successful: list<string>,
     *         failed: list<string>,
     *     },
     * }
     */
    public function getData(): array
    {
        return [
            'progress' => [
                'current' => \count($this->successfulUrls) + \count($this->failedUrls),
                'total' => $this->numberOfUrls,
            ],
            'urls' => [
                'current' => $this->currentUrl,
                'successful' => $this->successfulUrls,
                'failed' => $this->failedUrls,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->getData();
    }
}
