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
use GuzzleHttp\Promise;
use Psr\Http\Message;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;

/**
 * UrlMetadataPreparation
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\Exclude]
final class UrlMetadataPreparation
{
    /**
     * @var callable(Message\RequestInterface, array<string, mixed>): Promise\PromiseInterface
     */
    private $nextHandler;
    private readonly Http\Message\UrlMetadataFactory $urlMetadataFactory;

    /**
     * @param callable(Message\RequestInterface, array<string, mixed>): Promise\PromiseInterface $nextHandler
     */
    public function __construct(callable $nextHandler)
    {
        $this->nextHandler = $nextHandler;
        $this->urlMetadataFactory = Core\Utility\GeneralUtility::makeInstance(Http\Message\UrlMetadataFactory::class);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function __invoke(Message\RequestInterface $request, array $options): Promise\PromiseInterface
    {
        $request = $this->urlMetadataFactory->enrichRequest($request);

        return ($this->nextHandler)($request, $options);
    }

    /**
     * @return callable(callable): self
     */
    public static function create(): callable
    {
        return static fn(callable $handler) => new self($handler);
    }
}
