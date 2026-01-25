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

namespace EliasHaeussler\Typo3Warming\Backend\ToolbarItems;

use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Domain;
use EliasHaeussler\Typo3Warming\View;
use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;

/**
 * CacheWarmupToolbarItem
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class CacheWarmupToolbarItem implements Backend\Toolbar\ToolbarItemInterface
{
    public function __construct(
        private Configuration\Configuration $configuration,
        private View\TemplateRenderer $renderer,
        private Domain\Repository\SiteRepository $siteRepository,
        Core\Page\PageRenderer $pageRenderer,
    ) {
        $pageRenderer->loadJavaScriptModule('@eliashaeussler/typo3-warming/backend/toolbar-menu.js');
    }

    public function checkAccess(): bool
    {
        // Early return if cache warmup from backend toolbar is disabled globally
        if (!$this->configuration->enabledInToolbar) {
            return false;
        }

        return $this->siteRepository->countAll() > 0;
    }

    public function getItem(): string
    {
        return $this->renderer->render('Toolbar/CacheWarmupToolbarItem');
    }

    public function hasDropDown(): bool
    {
        return false;
    }

    public function getDropDown(): string
    {
        return '';
    }

    /**
     * @return array<string, string>
     */
    public function getAdditionalAttributes(): array
    {
        return [
            'class' => 'tx-warming-toolbar-item',
        ];
    }

    public function getIndex(): int
    {
        // Clear cache toolbar item has index=25
        return 27;
    }
}
