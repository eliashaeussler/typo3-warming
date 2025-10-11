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

namespace EliasHaeussler\Typo3Warming\Backend\Action;

use TYPO3\CMS\Core;

/**
 * WarmupAction
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class WarmupAction
{
    private function __construct(
        public string $name,
        public string|int $identifier,
        public string $label,
        public string $icon,
    ) {}

    public static function language(int $languageId, string $label, string $icon): self
    {
        return new self('lang_' . $languageId, $languageId, $label, $icon);
    }

    public static function fromSiteLanguage(Core\Site\Entity\SiteLanguage $siteLanguage): self
    {
        return self::language(
            $siteLanguage->getLanguageId(),
            $siteLanguage->getTitle(),
            $siteLanguage->getFlagIdentifier(),
        );
    }

    public static function special(string $type, string $label, string $icon): self
    {
        return new self('special_' . $type, $type, $label, $icon);
    }
}
