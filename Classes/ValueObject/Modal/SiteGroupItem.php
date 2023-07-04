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
 * SiteGroupItem
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class SiteGroupItem
{
    public function __construct(
        private readonly Core\Site\Entity\SiteLanguage $language,
        private readonly bool $defaultLanguage,
        private readonly ?string $url = null,
    ) {
    }

    public function getLanguage(): Core\Site\Entity\SiteLanguage
    {
        return $this->language;
    }

    public function isDefaultLanguage(): bool
    {
        return $this->defaultLanguage;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function isMissing(): bool
    {
        return $this->url === null;
    }
}
