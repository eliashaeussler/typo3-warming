<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Http\Client\Handler;

use GuzzleHttp\Exception;
use GuzzleHttp\Promise;
use GuzzleHttp\Utils;
use Psr\Http\Message;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;

/**
 * SubRequestHandler
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[DependencyInjection\Attribute\Autoconfigure(public: true)]
final class SubRequestHandler
{
    /**
     * @var list<string>
     */
    private readonly array $supportedBaseUrls;

    /**
     * @var callable(Message\RequestInterface, array<string, mixed>): Promise\PromiseInterface
     */
    private $fallbackHandler;

    public function __construct(
        private readonly Frontend\Http\Application $application,
        Core\Site\SiteFinder $siteFinder,
    ) {
        $this->supportedBaseUrls = $this->resolveBaseUrls($siteFinder);
        $this->fallbackHandler = Utils::chooseHandler();
    }

    /**
     * @param array<string, mixed> $options
     */
    public function __invoke(Message\RequestInterface $request, array $options): Promise\PromiseInterface
    {
        if (!$this->isSupportedRequestUrl($request->getUri())) {
            return ($this->fallbackHandler)($request, $options);
        }

        $initialTsfe = $GLOBALS['TSFE'] ?? null;
        $GLOBALS['TSFE'] = null;

        try {
            return $this->sendSubRequest($request);
        } finally {
            $GLOBALS['TSFE'] = $initialTsfe;
        }
    }

    private function sendSubRequest(Message\RequestInterface $request): Promise\PromiseInterface
    {
        $subRequest = new Core\Http\ServerRequest(
            $request->getUri(),
            $request->getMethod(),
            $request->getBody(),
            $request->getHeaders(),
        );

        try {
            $response = $this->application->handle($subRequest);
        } catch (\Exception $exception) {
            return Promise\Create::rejectionFor(
                new Exception\RequestException($exception->getMessage(), $request),
            );
        }

        return Promise\Create::promiseFor($response);
    }

    private function isSupportedRequestUrl(Message\UriInterface $uri): bool
    {
        $requestUrl = (string)$uri;

        foreach ($this->supportedBaseUrls as $baseUrl) {
            if (str_starts_with($requestUrl, $baseUrl)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Core\Site\SiteFinder $siteFinder
     * @return list<string>
     */
    private function resolveBaseUrls(Core\Site\SiteFinder $siteFinder): array
    {
        $sites = $siteFinder->getAllSites();
        $baseUrls = [];

        foreach ($sites as $site) {
            $baseUrls[] = (string)$site->getBase();
        }

        return $baseUrls;
    }
}
