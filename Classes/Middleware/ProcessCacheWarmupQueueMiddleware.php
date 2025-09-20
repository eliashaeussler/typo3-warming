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

namespace EliasHaeussler\Typo3Warming\Middleware;

use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Enums;
use EliasHaeussler\Typo3Warming\Queue;
use EliasHaeussler\Typo3Warming\Result;
use EliasHaeussler\Typo3Warming\ValueObject;
use Psr\Http\Message;
use Psr\Http\Server;
use TYPO3\CMS\Core;

/**
 * ProcessCacheWarmupQueueMiddleware
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final readonly class ProcessCacheWarmupQueueMiddleware implements Server\MiddlewareInterface
{
    private Result\ResultNotificationBuilder $resultNotificationBuilder;

    public function __construct(
        private Core\Messaging\FlashMessageService $flashMessageService,
        private Queue\CacheWarmupQueue $queue,
    ) {
        $this->resultNotificationBuilder = new Result\ResultNotificationBuilder();
    }

    public function process(
        Message\ServerRequestInterface $request,
        Server\RequestHandlerInterface $handler,
    ): Message\ResponseInterface {
        $response = $handler->handle($request);

        try {
            $cacheWarmupRequest = $this->queue->wrapInWarmupRequest();
            $cacheWarmupResult = $this->queue->process();

            if ($cacheWarmupResult !== null) {
                $this->enqueueFlashMessage($cacheWarmupRequest, $cacheWarmupResult);
            }
        } catch (\Throwable) {
            // Avoid treating the whole request as failed if cache warmup fails
        }

        return $response;
    }

    private function enqueueFlashMessage(ValueObject\Request\WarmupRequest $request, Result\CacheWarmupResult $result): void
    {
        $messages = $this->resultNotificationBuilder->buildMessages($request, $result);
        $state = Enums\WarmupState::fromCacheWarmupResult($result);

        $flashMessageQueue = $this->flashMessageService->getMessageQueueByIdentifier(
            Core\Messaging\FlashMessageQueue::NOTIFICATION_QUEUE,
        );
        $flashMessage = Core\Utility\GeneralUtility::makeInstance(
            Core\Messaging\FlashMessage::class,
            implode("\n\n", $messages),
            Configuration\Localization::translate('notification.title.' . $state->value),
            match ($state) {
                Enums\WarmupState::Failed => Core\Type\ContextualFeedbackSeverity::ERROR,
                Enums\WarmupState::Success => Core\Type\ContextualFeedbackSeverity::OK,
                Enums\WarmupState::Unknown => Core\Type\ContextualFeedbackSeverity::NOTICE,
                Enums\WarmupState::Warning => Core\Type\ContextualFeedbackSeverity::WARNING,
            },
            true,
        );
        $flashMessageQueue->enqueue($flashMessage);
    }
}
