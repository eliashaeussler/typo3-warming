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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3Warming\Crawler;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\SSE;
use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Http;
use GuzzleHttp\ClientInterface;
use TYPO3\CMS\Core;

/**
 * ConcurrentAgentCrawler
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 *
 * @extends CacheWarmup\Crawler\AbstractConfigurableCrawler<array{
 *     concurrency: int,
 *     request_method: string,
 *     request_headers: array<string, string>,
 *     request_options: array<string, mixed>,
 *     client_config: array<string, mixed>,
 * }>
 */
final class ConcurrentUserAgentCrawler extends CacheWarmup\Crawler\AbstractConfigurableCrawler implements StreamableCrawler
{
    use CacheWarmup\Crawler\ConcurrentCrawlerTrait {
        getRequestHeaders as getDefaultRequestHeaders;
    }

    protected static array $defaultOptions = [
        'concurrency' => 5,
        'request_method' => 'GET',
        'request_headers' => [],
        'request_options' => [],
        'client_config' => [],
    ];

    private readonly Http\Client\ClientFactory $clientFactory;
    private readonly Configuration\Configuration $configuration;
    private ClientInterface $client;
    private ?SSE\Stream\EventStream $stream = null;

    public function __construct(array $options = [])
    {
        $this->clientFactory = Core\Utility\GeneralUtility::makeInstance(Http\Client\ClientFactory::class);
        $this->configuration = Core\Utility\GeneralUtility::makeInstance(Configuration\Configuration::class);

        parent::__construct($options);
    }

    public function crawl(array $urls): CacheWarmup\Result\CacheWarmupResult
    {
        $numberOfUrls = \count($urls);
        $resultHandler = new CacheWarmup\Http\Message\Handler\ResultCollectorHandler();
        $handlers = [$resultHandler];

        if ($this->stream !== null) {
            $streamHandler = new Http\Message\Handler\StreamResponseHandler($this->stream, $numberOfUrls);
            $handlers[] = $streamHandler;
        }

        // Start crawling
        $pool = $this->createPool($urls, $this->client, $handlers);
        $pool->promise()->wait();

        return $resultHandler->getResult();
    }

    public function setOptions(array $options): void
    {
        parent::setOptions($options);

        // Recreate client with updated client config
        $this->client = $this->clientFactory->get($this->options['client_config']);
    }

    public function setStream(SSE\Stream\EventStream $stream): void
    {
        $this->stream = $stream;
    }

    /**
     * @return array<string, string>
     */
    protected function getRequestHeaders(): array
    {
        $headers = $this->getDefaultRequestHeaders();
        $headers['User-Agent'] = $this->configuration->getUserAgent();

        return $headers;
    }
}
