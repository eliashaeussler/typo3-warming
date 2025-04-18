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

namespace EliasHaeussler\Typo3Warming\Http\Message;

use EliasHaeussler\Typo3Warming\View;
use Psr\Http\Message;
use TYPO3\CMS\Core;

/**
 * ResponseFactory
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class ResponseFactory
{
    public function __construct(
        private View\TemplateRenderer $renderer,
    ) {}

    public function ok(): Message\ResponseInterface
    {
        return new Core\Http\Response();
    }

    public function html(string $html): Message\ResponseInterface
    {
        return new Core\Http\HtmlResponse($html);
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function htmlTemplate(string $templatePath, array $variables = []): Message\ResponseInterface
    {
        $html = $this->renderer->render($templatePath, $variables);

        return $this->html($html);
    }

    /**
     * @param array<string, mixed> $json
     */
    public function json(array $json): Message\ResponseInterface
    {
        return new Core\Http\JsonResponse($json);
    }

    public function badRequest(string $reason): Message\ResponseInterface
    {
        return new Core\Http\Response(null, 400, [], $reason);
    }
}
