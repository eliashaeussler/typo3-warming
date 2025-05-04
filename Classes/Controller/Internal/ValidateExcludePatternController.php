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

namespace EliasHaeussler\Typo3Warming\Controller\Internal;

use EliasHaeussler\CacheWarmup;
use Psr\Http\Message;
use TYPO3\CMS\Core;

/**
 * ValidateExcludePatternController
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final class ValidateExcludePatternController
{
    use Core\Http\AllowedMethodsTrait;

    public function __invoke(Message\ServerRequestInterface $request): Message\ResponseInterface
    {
        $this->assertAllowedHttpMethod($request, 'POST');

        /** @var array{pattern?: string} $parsedBody */
        $parsedBody = $request->getParsedBody();
        $pattern = $parsedBody['pattern'] ?? null;

        if (!is_string($pattern)) {
            $error = 'Invalid type of pattern given.';
        } else {
            try {
                CacheWarmup\Config\Option\ExcludePattern::create($pattern);
                $error = null;
            } catch (CacheWarmup\Exception\RegularExpressionIsInvalid $exception) {
                $error = htmlspecialchars($exception->getMessage());
            }
        }

        return new Core\Http\JsonResponse([
            'valid' => $error === null,
            'error' => $error,
        ]);
    }
}
