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
use Psr\Http\Message;
use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;

/**
 * UrlMetadataListener
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class UrlMetadataListener
{
    public function __construct(
        private Backend\Module\ModuleProvider $moduleProvider,
        private Backend\Routing\UriBuilder $uriBuilder,
        private Http\Message\UrlMetadataFactory $urlMetadataFactory,
    ) {}

    #[Core\Attribute\AsEventListener('eliashaeussler/typo3-warming/url-metadata/on-success')]
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

    #[Core\Attribute\AsEventListener('eliashaeussler/typo3-warming/url-metadata/on-failure')]
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
        $data['pageActions'] = $this->buildPageActions($metadata);

        return $data;
    }

    /**
     * @return array{
     *     editRecord?: string,
     *     viewLog?: string,
     * }
     */
    private function buildPageActions(Http\Message\UrlMetadata $metadata): array
    {
        // Early return if page id is missing
        if ($metadata->pageId === null) {
            return [];
        }

        // Early return if we're not in backend context
        if (!$this->isRunningInBackendContext()) {
            return [];
        }

        $backendUser = $this->getBackendUser();

        // Early return if backend user is not available (should never happen, but who knows)
        if ($backendUser === null) {
            return [];
        }

        $pageTranslationId = $metadata->pageId;
        $actions = [];

        // Fetch page translation
        if ($metadata->languageId > 0) {
            $pageTranslations = Backend\Utility\BackendUtility::getRecordLocalization(
                'pages',
                $metadata->pageId,
                $metadata->languageId,
            );

            if ($pageTranslations !== false && $pageTranslations !== []) {
                $pageTranslationId = (int)$pageTranslations[0]['uid'];
            }
        }

        // Add uri to edit current page record
        if ($this->moduleProvider->accessGranted('web_layout', $backendUser)) {
            $actions['editRecord'] = (string)$this->uriBuilder->buildUriFromRoute(
                'record_edit',
                [
                    'edit' => [
                        'pages' => [
                            $pageTranslationId => 'edit',
                        ],
                    ],
                    'returnUrl' => (string)$this->uriBuilder->buildUriFromRoute(
                        'web_layout',
                        [
                            'id' => $metadata->pageId,
                        ],
                    ),
                    'overrideVals' => [
                        'pages' => [
                            'sys_language_uid' => $metadata->languageId ?? 0,
                        ],
                    ],
                ],
                Backend\Routing\UriBuilder::SHAREABLE_URL,
            );
        }

        // Add uri to view logs for current page
        if (Core\Utility\ExtensionManagementUtility::isLoaded('belog') &&
            $this->moduleProvider->accessGranted('system_log', $backendUser)
        ) {
            $actions['viewLog'] = (string)$this->uriBuilder->buildUriFromRoute(
                'system_log.BackendLog_list',
                [
                    'constraint' => [
                        'pageId' => $pageTranslationId,
                    ],
                ],
                Backend\Routing\UriBuilder::SHAREABLE_URL,
            );
        }

        return $actions;
    }

    private function isRunningInBackendContext(): bool
    {
        $serverRequest = $this->getServerRequest();

        if ($serverRequest === null) {
            return false;
        }

        return Core\Http\ApplicationType::fromRequest($serverRequest)->isBackend();
    }

    private function getBackendUser(): ?Core\Authentication\BackendUserAuthentication
    {
        $backendUser = $GLOBALS['BE_USER'] ?? null;

        if ($backendUser instanceof Core\Authentication\BackendUserAuthentication) {
            return $backendUser;
        }

        return null;
    }

    private function getServerRequest(): ?Message\ServerRequestInterface
    {
        $serverRequest = $GLOBALS['TYPO3_REQUEST'] ?? null;

        if ($serverRequest instanceof Message\ServerRequestInterface) {
            return $serverRequest;
        }

        return null;
    }
}
