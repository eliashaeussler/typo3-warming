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

namespace EliasHaeussler\Typo3Warming\Crawler;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Http;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use Psr\EventDispatcher;
use Psr\Log;
use Symfony\Component\Console;
use Symfony\Component\OptionsResolver;
use TYPO3\CMS\Core;

/**
 * OutputtingUserAgentCrawler
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 *
 * @extends CacheWarmup\Crawler\AbstractConfigurableCrawler<array{
 *     concurrency: int,
 *     request_method: string,
 *     request_headers: array<string, string>,
 *     request_options: array<string, mixed>,
 *     write_response_body: bool,
 *     perform_subrequests: bool,
 * }>
 */
final class OutputtingUserAgentCrawler extends CacheWarmup\Crawler\AbstractConfigurableCrawler implements CacheWarmup\Crawler\LoggingCrawler, CacheWarmup\Crawler\VerboseCrawler
{
    use CacheWarmup\Crawler\ConcurrentCrawlerTrait {
        configureOptions as configureDefaultOptions;
        createRequestFactory as createBaseRequestFactory;
    }
    use LoggingCrawlerTrait;

    private readonly Configuration\Configuration $configuration;
    private readonly Http\Client\Handler\SubRequestHandler $subRequestHandler;
    private Console\Output\OutputInterface $output;

    public function __construct(
        array $options = [],
        ?Log\LoggerInterface $logger = null,
        private readonly ClientInterface $client = new Client(),
        private readonly ?EventDispatcher\EventDispatcherInterface $eventDispatcher = null,
    ) {
        $this->configuration = Core\Utility\GeneralUtility::makeInstance(Configuration\Configuration::class);
        $this->subRequestHandler = Core\Utility\GeneralUtility::makeInstance(Http\Client\Handler\SubRequestHandler::class);
        $this->logger = $logger;

        parent::__construct($options);
    }

    public function crawl(array $urls): CacheWarmup\Result\CacheWarmupResult
    {
        $numberOfUrls = \count($urls);
        $resultHandler = new CacheWarmup\Http\Message\Handler\ResultCollectorHandler($this->eventDispatcher);
        $logHandler = $this->createLogHandler();

        // Create progress response handler (depends on the available output)
        if ($this->output instanceof Console\Output\ConsoleOutputInterface && $this->output->isVerbose()) {
            $progressBarHandler = new CacheWarmup\Http\Message\Handler\VerboseProgressHandler($this->output, $numberOfUrls);
        } else {
            $progressBarHandler = new CacheWarmup\Http\Message\Handler\CompactProgressHandler($this->output, $numberOfUrls);
        }

        // Inject sub request handler
        if ($this->options['perform_subrequests'] && !isset($this->options['request_options']['handler'])) {
            $this->options['request_options']['handler'] = HandlerStack::create($this->subRequestHandler);
        }

        // Create request pool
        $pool = $this->createPool($urls, $this->client, [$resultHandler, $progressBarHandler, $logHandler]);

        // Start crawling
        $progressBarHandler->startProgressBar();
        $pool->promise()->wait();
        $progressBarHandler->finishProgressBar();

        return $resultHandler->getResult();
    }

    public function setOutput(Console\Output\OutputInterface $output): void
    {
        $this->output = $output;
    }

    protected function configureOptions(OptionsResolver\OptionsResolver $optionsResolver): void
    {
        $this->configureDefaultOptions($optionsResolver);

        // Use GET instead of HEAD as default request method
        $optionsResolver->setDefault('request_method', 'GET');

        // Add option for sub request handler
        $optionsResolver->define('perform_subrequests')
            ->allowedTypes('bool')
            ->default(false)
        ;
    }

    protected function createRequestFactory(): CacheWarmup\Http\Message\RequestFactory
    {
        $headers = $this->options['request_headers'];
        $headers['User-Agent'] = $this->configuration->getUserAgent();

        return $this->createBaseRequestFactory()->withHeaders($headers);
    }
}
