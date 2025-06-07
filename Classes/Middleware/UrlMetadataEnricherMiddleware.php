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

use EliasHaeussler\Typo3Warming\Http;
use Psr\Http\Message;
use Psr\Http\Server;
use TYPO3\CMS\Core;

/**
 * UrlMetadataEnricherMiddleware
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class UrlMetadataEnricherMiddleware implements Server\MiddlewareInterface
{
    public function __construct(
        private Http\Message\UrlMetadataFactory $urlMetadataFactory,
    ) {}

    /**
     * @throws Core\Error\Http\StatusException
     * @throws Core\Http\ImmediateResponseException
     */
    public function process(
        Message\ServerRequestInterface $request,
        Server\RequestHandlerInterface $handler,
    ): Message\ResponseInterface {
        $metadata = $this->urlMetadataFactory->createForRequest($request);

        if ($metadata === null) {
            return $handler->handle($request);
        }

        $this->enrichUrlMetadata($metadata, $request);

        try {
            return $this->urlMetadataFactory->enrichResponse($handler->handle($request), $metadata);
        } catch (Core\Http\ImmediateResponseException|Core\Error\Http\StatusException $exception) {
            $this->urlMetadataFactory->enrichException($exception, $metadata);

            throw $exception;
        }
    }

    private function enrichUrlMetadata(Http\Message\UrlMetadata $metadata, Message\ServerRequestInterface $request): void
    {
        $pageArguments = $request->getAttribute('routing');
        $siteLanguage = $request->getAttribute('language');

        if ($pageArguments instanceof Core\Routing\PageArguments) {
            $metadata->pageId = $pageArguments->getPageId();
            $metadata->pageType = $pageArguments->getPageType();
        }

        if ($siteLanguage instanceof Core\Site\Entity\SiteLanguage) {
            $metadata->languageId = $siteLanguage->getLanguageId();
        }
    }
}
