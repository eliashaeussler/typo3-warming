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

namespace EliasHaeussler\Typo3Warming\Http\Message\Request;

use EliasHaeussler\Typo3Warming\Configuration;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;

/**
 * RequestOptions
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\Autoconfigure(public: true)]
final readonly class RequestOptions
{
    public function getUserAgent(): string
    {
        $string = 'TYPO3/tx_warming_crawler';

        if (class_exists(Core\Crypto\HashService::class)) {
            return Core\Utility\GeneralUtility::makeInstance(Core\Crypto\HashService::class)->appendHmac(
                $string,
                // @todo Change to self::class in v5.0
                Configuration\Configuration::class,
            );
        }

        // @todo Remove once support for TYPO3 v12 is dropped
        /* @phpstan-ignore classConstant.deprecatedClass, method.deprecatedClass */
        return Core\Utility\GeneralUtility::makeInstance(Extbase\Security\Cryptography\HashService::class)->appendHmac(
            $string,
        );
    }
}
