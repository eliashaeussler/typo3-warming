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

namespace EliasHaeussler\Typo3Warming\Middleware;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Utility;
use Psr\Http\Message;
use Psr\Http\Server;
use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;

/**
 * ScriptInjectionMiddleware
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class ScriptInjectionMiddleware implements Server\MiddlewareInterface
{
    private const LANGUAGE_LABELS = [
        // Notification
        'warming.notification.aborted.title' => 'notification.aborted.title',
        'warming.notification.aborted.message' => 'notification.aborted.message',
        'warming.notification.error.title' => 'notification.error.title',
        'warming.notification.error.message' => 'notification.error.message',
        'warming.notification.action.showReport' => 'notification.action.showReport',
        'warming.notification.action.retry' => 'notification.action.retry',
        'warming.notification.noSitesSelected.title' => 'notification.noSitesSelected.title',
        'warming.notification.noSitesSelected.message' => 'notification.noSitesSelected.message',

        // Progress Modal
        'warming.modal.progress.title' => 'modal.progress.title',
        'warming.modal.progress.title.failed' => 'modal.progress.title.failed',
        'warming.modal.progress.title.warning' => 'modal.progress.title.warning',
        'warming.modal.progress.title.success' => 'modal.progress.title.success',
        'warming.modal.progress.title.aborted' => 'modal.progress.title.aborted',
        'warming.modal.progress.title.unknown' => 'modal.progress.title.unknown',
        'warming.modal.progress.button.report' => 'modal.progress.button.report',
        'warming.modal.progress.button.retry' => 'modal.progress.button.retry',
        'warming.modal.progress.button.close' => 'modal.progress.button.close',
        'warming.modal.progress.failedCounter' => 'modal.progress.failedCounter',
        'warming.modal.progress.allCounter' => 'modal.progress.allCounter',
        'warming.modal.progress.placeholder' => 'modal.progress.placeholder',

        // Report Modal
        'warming.modal.report.title' => 'modal.report.title',
        'warming.modal.report.panel.failed' => 'modal.report.panel.failed',
        'warming.modal.report.panel.failed.summary' => 'modal.report.panel.failed.summary',
        'warming.modal.report.panel.successful' => 'modal.report.panel.successful',
        'warming.modal.report.panel.successful.summary' => 'modal.report.panel.successful.summary',
        'warming.modal.report.panel.excluded' => 'modal.report.panel.excluded',
        'warming.modal.report.panel.excluded.summary' => 'modal.report.panel.excluded.summary',
        'warming.modal.report.panel.excluded.sitemaps' => 'modal.report.panel.excluded.sitemaps',
        'warming.modal.report.panel.excluded.urls' => 'modal.report.panel.excluded.urls',
        'warming.modal.report.action.edit' => 'modal.report.action.edit',
        'warming.modal.report.action.info' => 'modal.report.action.info',
        'warming.modal.report.action.log' => 'modal.report.action.log',
        'warming.modal.report.action.view' => 'modal.report.action.view',
        'warming.modal.report.message.requestId' => 'modal.report.message.requestId',
        'warming.modal.report.message.total' => 'modal.report.message.total',
        'warming.modal.report.message.noUrlsCrawled' => 'modal.report.message.noUrlsCrawled',

        // Sites Modal
        'warming.modal.sites.title' => 'modal.sites.title',
        'warming.modal.sites.userAgent.action.successful' => 'modal.sites.userAgent.action.successful',
        'warming.modal.sites.button.start' => 'modal.sites.button.start',
    ];

    public function __construct(
        private CacheWarmup\Crawler\Strategy\CrawlingStrategyFactory $crawlingStrategyFactory,
        private Core\Page\PageRenderer $pageRenderer,
    ) {}

    public function process(
        Message\ServerRequestInterface $request,
        Server\RequestHandlerInterface $handler,
    ): Message\ResponseInterface {
        $this->injectLanguageLabels();

        return $this->injectExtensionConfigurationScript($request, $handler->handle($request));
    }

    private function injectLanguageLabels(): void
    {
        $this->pageRenderer->addInlineLanguageLabelArray(
            \array_map(
                static fn(string $key) => Configuration\Localization::translate($key),
                self::LANGUAGE_LABELS,
            ),
        );
    }

    private function injectExtensionConfigurationScript(
        Message\ServerRequestInterface $request,
        Message\ResponseInterface $response,
    ): Message\ResponseInterface {
        /** @var Backend\Routing\Route|null $route */
        $route = $request->getAttribute('route');
        $backendUser = Utility\BackendUtility::getBackendUser();

        // Early return if we're not on main route
        if ($route?->getPath() !== '/main') {
            return $response;
        }

        // Early return if EXT:install is not loaded and extension settings module is not available
        if (!Core\Utility\ExtensionManagementUtility::isLoaded('install')) {
            return $response;
        }

        // Early return if response is invalid
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        // Early return on insufficient privileges (only system maintainers can access settings module)
        if (!($backendUser->isSystemMaintainer())) {
            return $response;
        }

        // Inject scripts into <head>
        $body = $response->getBody();
        $contents = (string)$body;
        $body->rewind();
        $body->write(
            str_replace('</head>', $this->renderScriptTag($request) . '</head>', $contents),
        );

        return $response;
    }

    private function renderScriptTag(Message\ServerRequestInterface $request): string
    {
        /** @var Core\Security\ContentSecurityPolicy\ConsumableNonce $nonce */
        $nonce = $request->getAttribute('nonce');
        $nonceValue = $nonce->consume();
        $strategies = json_encode($this->crawlingStrategyFactory->getAll());

        return <<<JS
<script async nonce="{$nonceValue}" id="tx-warming-script-inject">
import('@eliashaeussler/typo3-warming/backend/extension-configuration.js').then(({default: extensionConfiguration}) => {
    extensionConfiguration.initializeModalListener('{$nonceValue}', {$strategies});
});
</script>
JS;
    }
}
