<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Crawler;

use EliasHaeussler\CacheWarmup\Crawler\ConcurrentCrawler;
use EliasHaeussler\CacheWarmup\CrawlingState;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\Client\GuzzleClientFactory;

/**
 * ConcurrentAgentCrawler
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class ConcurrentUserAgentCrawler extends ConcurrentCrawler implements RequestAwareInterface
{
    use RequestAwareTrait;
    use UserAgentTrait;

    protected function getRequests(): \Iterator
    {
        /** @var Request $request */
        foreach (parent::getRequests() as $request) {
            yield $this->applyUserAgentHeader($request->withMethod('GET'));
        }
    }

    protected function initializeClient(): ClientInterface
    {
        return GuzzleClientFactory::getClient();
    }

    public function onSuccess(ResponseInterface $response, int $index): void
    {
        $data = [
            'response' => $response,
        ];

        $this->successfulUrls[] = $crawlingState = CrawlingState::createSuccessful($this->urls[$index], $data);
        $this->updateRequest($crawlingState);
    }

    public function onFailure(\Throwable $exception, int $index): void
    {
        $data = [
            'exception' => $exception,
        ];

        $this->failedUrls[] = $crawlingState = CrawlingState::createFailed($this->urls[$index], $data);
        $this->updateRequest($crawlingState);
    }
}
