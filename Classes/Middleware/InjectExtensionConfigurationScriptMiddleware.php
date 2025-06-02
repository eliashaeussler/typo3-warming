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

use EliasHaeussler\CacheWarmup;
use Psr\Http\Message;
use Psr\Http\Server;
use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;

/**
 * InjectExtensionConfigurationScriptMiddleware
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class InjectExtensionConfigurationScriptMiddleware implements Server\MiddlewareInterface
{
    public function __construct(
        private CacheWarmup\Crawler\Strategy\CrawlingStrategyFactory $crawlingStrategyFactory,
    ) {}

    public function process(
        Message\ServerRequestInterface $request,
        Server\RequestHandlerInterface $handler,
    ): Message\ResponseInterface {
        $response = $handler->handle($request);
        /** @var Backend\Routing\Route|null $route */
        $route = $request->getAttribute('route');
        /** @var Core\Authentication\BackendUserAuthentication|null $backedUser */
        $backedUser = $GLOBALS['BE_USER'] ?? null;

        // Early return if we're not on main route
        if ($route?->getPath() !== '/main') {
            return $response;
        }

        // Early return if response is invalid
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        // Early return on insufficient privileges (only system maintainers can access settings module)
        if (!($backedUser?->isSystemMaintainer() ?? false)) {
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
