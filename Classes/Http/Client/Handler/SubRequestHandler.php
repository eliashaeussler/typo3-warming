<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2026 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\Typo3Warming\Http;
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
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\Autoconfigure(public: true)]
final class SubRequestHandler
{
    /**
     * @var callable(Message\RequestInterface, array<string, mixed>): Promise\PromiseInterface
     */
    private $fallbackHandler;

    public function __construct(
        private readonly Frontend\Http\Application $application,
        private readonly Http\Message\UrlMetadataFactory $urlMetadataFactory,
        private readonly Core\Routing\SiteMatcher $siteMatcher,
    ) {
        $this->fallbackHandler = Utils::chooseHandler();
    }

    /**
     * @param array<string, mixed> $options
     */
    public function __invoke(Message\RequestInterface $request, array $options): Promise\PromiseInterface
    {
        $subRequest = new Core\Http\ServerRequest(
            $request->getUri(),
            $request->getMethod(),
            $request->getBody(),
            $request->getHeaders(),
        );

        if (!$this->isSupportedRequest($subRequest)) {
            return ($this->fallbackHandler)($request, $options);
        }

        $globalsBackup = $GLOBALS;

        try {
            return $this->sendSubRequest($request, $subRequest);
        } finally {
            foreach ($globalsBackup as $key => $value) {
                $GLOBALS[$key] = $value;
            }
        }
    }

    private function sendSubRequest(
        Message\RequestInterface $request,
        Message\ServerRequestInterface $subRequest,
    ): Promise\PromiseInterface {
        $response = null;

        try {
            return Promise\Create::promiseFor(
                $this->application->handle($subRequest),
            );
        } catch (Core\Http\ImmediateResponseException $exception) {
            $response = $exception->getResponse();
        } catch (Core\Error\Http\StatusException $exception) {
            $response = new Core\Http\Response(statusCode: 500);
            $metadata = $this->urlMetadataFactory->createFromResponseHeaders($exception->getStatusHeaders());

            if ($metadata !== null) {
                $response = $this->urlMetadataFactory->enrichResponse($response, $metadata);
            }
        } catch (\Exception $exception) {
        }

        return Promise\Create::rejectionFor(
            new Exception\RequestException($exception->getMessage(), $request, $response),
        );
    }

    private function isSupportedRequest(Message\ServerRequestInterface $subRequest): bool
    {
        try {
            $routeResult = $this->siteMatcher->matchRequest($subRequest);
        } catch (\Exception) {
            return false;
        }

        return $routeResult instanceof Core\Routing\SiteRouteResult
            && $routeResult->getSite() instanceof Core\Site\Entity\Site;
    }
}
