<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3Warming\ValueObject\Request;

/**
 * WarmupRequest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class WarmupRequest
{
    /**
     * @var non-empty-string
     */
    private readonly string $id;

    /**
     * @param list<SiteWarmupRequest> $sites
     * @param list<PageWarmupRequest> $pages
     */
    public function __construct(
        private readonly array $sites = [],
        private readonly array $pages = [],
        private readonly RequestConfiguration $configuration = new RequestConfiguration(),
    ) {
        $this->id = uniqid('_', true);
    }

    /**
     * @return non-empty-string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return list<SiteWarmupRequest>
     */
    public function getSites(): array
    {
        return $this->sites;
    }

    /**
     * @return list<PageWarmupRequest>
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    public function getConfiguration(): RequestConfiguration
    {
        return $this->configuration;
    }
}
