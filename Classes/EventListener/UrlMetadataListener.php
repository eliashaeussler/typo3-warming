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

namespace EliasHaeussler\Typo3Warming\EventListener;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming\Http;
use GuzzleHttp\Exception;

/**
 * UrlMetadataListener
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class UrlMetadataListener
{
    public function __construct(
        private Http\Message\UrlMetadataFactory $urlMetadataFactory,
    ) {}

    // @todo Enable attribute once support for TYPO3 v12 is dropped
    // #[\TYPO3\CMS\Core\Attribute\AsEventListener('eliashaeussler/typo3-warming/url-metadata/on-success')]
    public function onSuccess(CacheWarmup\Event\Crawler\UrlCrawlingSucceeded $event): void
    {
        $metadata = $this->urlMetadataFactory->createFromResponse($event->response());

        if ($metadata !== null) {
            $result = CacheWarmup\Result\CrawlingResult::createSuccessful(
                $event->result()->getUri(),
                $this->extendResultData($event->result(), $metadata),
            );

            $event->setResult($result);
        }
    }

    // @todo Enable attribute once support for TYPO3 v12 is dropped
    // #[\TYPO3\CMS\Core\Attribute\AsEventListener('eliashaeussler/typo3-warming/url-metadata/on-failure')]
    public function onFailure(CacheWarmup\Event\Crawler\UrlCrawlingFailed $event): void
    {
        $metadata = null;
        $exception = $event->exception();

        if ($exception instanceof Exception\RequestException && ($response = $exception->getResponse()) !== null) {
            $metadata = $this->urlMetadataFactory->createFromResponse($response);
        }

        if ($metadata !== null) {
            $result = CacheWarmup\Result\CrawlingResult::createFailed(
                $event->result()->getUri(),
                $this->extendResultData($event->result(), $metadata),
            );

            $event->setResult($result);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function extendResultData(
        CacheWarmup\Result\CrawlingResult $result,
        Http\Message\UrlMetadata $metadata,
    ): array {
        $data = $result->getData();
        $data['urlMetadata'] = $metadata;

        return $data;
    }
}
