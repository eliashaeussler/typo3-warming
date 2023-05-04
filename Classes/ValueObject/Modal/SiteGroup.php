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

namespace EliasHaeussler\Typo3Warming\ValueObject\Modal;

use TYPO3\CMS\Core;

/**
 * SiteGroup
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class SiteGroup
{
    /**
     * @param list<SiteGroupItem> $items
     */
    public function __construct(
        private readonly Core\Site\Entity\Site $site,
        private readonly string $title,
        private readonly string $iconIdentifier,
        private readonly array $items,
    ) {
    }

    public function getSite(): Core\Site\Entity\Site
    {
        return $this->site;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getIconIdentifier(): string
    {
        return $this->iconIdentifier;
    }

    /**
     * @return list<SiteGroupItem>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function hasOnlyDefaultLanguage(): bool
    {
        if (\count($this->items) > 1) {
            return false;
        }

        foreach ($this->items as $item) {
            return $item->isDefaultLanguage();
        }

        return false;
    }

    public function isMissing(): bool
    {
        foreach ($this->items as $item) {
            if (!$item->isMissing()) {
                return false;
            }
        }

        return true;
    }
}
