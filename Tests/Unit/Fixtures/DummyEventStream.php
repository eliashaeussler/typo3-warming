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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Fixtures;

use EliasHaeussler\SSE;
use Psr\Http\Message;

/**
 * DummyEventStream
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final class DummyEventStream implements SSE\Stream\EventStream
{
    /**
     * @var list<SSE\Event\Event>
     */
    public array $receivedEvents = [];

    /**
     * @throws void
     */
    public function open(): void
    {
        // Intentionally left blank.
    }

    /**
     * @throws void
     */
    public function close(string $eventName = 'done'): void
    {
        // Intentionally left blank.
    }

    /**
     * @throws void
     */
    public function sendEvent(SSE\Event\Event $event): void
    {
        $this->receivedEvents[] = $event;
    }

    /**
     * @throws void
     */
    public function sendMessage(string $name = 'message', float|bool|int|string|null $data = null): void
    {
        // Intentionally left blank.
    }

    public function isActive(): bool
    {
        return true;
    }

    public static function canHandle(Message\ServerRequestInterface $request): bool
    {
        return true;
    }
}
