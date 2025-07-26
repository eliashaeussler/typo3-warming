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

namespace EliasHaeussler\Typo3Warming\Controller;

use CuyZ\Valinor;
use EliasHaeussler\CacheWarmup;
use EliasHaeussler\SSE;
use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Crawler;
use EliasHaeussler\Typo3Warming\Http;
use EliasHaeussler\Typo3Warming\Service;
use EliasHaeussler\Typo3Warming\ValueObject;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message;
use Psr\Log;
use Symfony\Component\DependencyInjection;

/**
 * CacheWarmupController
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\Autoconfigure(public: true)]
final readonly class CacheWarmupController
{
    public function __construct(
        private Configuration\Configuration $configuration,
        private Log\LoggerInterface $logger,
        private Valinor\Mapper\TreeMapper $mapper,
        private Http\Message\ResponseFactory $responseFactory,
        private Service\CacheWarmupService $warmupService,
    ) {}

    /**
     * @throws CacheWarmup\Exception\Exception
     * @throws GuzzleException
     * @throws SSE\Exception\StreamIsActive
     * @throws SSE\Exception\StreamIsClosed
     * @throws SSE\Exception\StreamIsInactive
     * @throws Typo3SitemapLocator\Exception\BaseUrlIsNotSupported
     * @throws Typo3SitemapLocator\Exception\SitemapIsMissing
     * @throws \JsonException
     */
    public function __invoke(Message\ServerRequestInterface $request): Message\ResponseInterface
    {
        // Ensure request was sent using the EventSource API performing an SSE request
        if (!SSE\Stream\SelfEmittingEventStream::canHandle($request)) {
            return $this->responseFactory->badRequest('Invalid request headers');
        }

        // Build warmup request object
        try {
            $warmupRequest = $this->mapper->map(ValueObject\Request\WarmupRequest::class, $request->getQueryParams());
        } catch (Valinor\Mapper\MappingError $error) {
            $messages = array_map(strval(...), $error->messages()->toArray());

            $this->logger->error(
                'Error during mapping of query parameters to warmup request object.',
                ['errors' => $messages],
            );

            return $this->responseFactory->badRequest('Invalid request parameters');
        }

        // Open event stream
        $eventStream = SSE\Stream\SelfEmittingEventStream::create($warmupRequest->getId());
        $eventStream->open();

        // Apply event stream to crawler
        $crawler = $this->configuration->getCrawler();
        if ($crawler instanceof Crawler\StreamableCrawler) {
            $crawler->setStream($eventStream);
        }

        // Perform cache warmup
        $result = $this->warmupService->warmup(
            $warmupRequest->getSites(),
            $warmupRequest->getPages(),
            $warmupRequest->getConfiguration()->getLimit(),
            $warmupRequest->getConfiguration()->getStrategy(),
        );

        // Send final response
        $warmupFinishedEvent = new Http\Message\Event\WarmupFinishedEvent($warmupRequest, $result);
        $eventStream->sendEvent($warmupFinishedEvent);

        // Close event stream
        $eventStream->close();

        return $this->responseFactory->ok();
    }
}
