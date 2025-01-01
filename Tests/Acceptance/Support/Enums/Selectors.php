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

namespace EliasHaeussler\Typo3Warming\Tests\Acceptance\Support\Enums;

/**
 * Selectors
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
enum Selectors: string
{
    case BackendPageModule = '[data-moduleroute-identifier="web_layout"]';
    case BackendListModule = '[data-moduleroute-identifier="web_list"]';
    case CheckboxMainGroup = '#tx-warming-checkbox-main-group';
    case CheckboxMainLanguage0 = '#tx-warming-checkbox-main-0';
    case CheckboxMainLanguage1 = '#tx-warming-checkbox-main-1';
    case CheckboxRoot2 = '#tx-warming-checkbox-root-2';
    case ContextMenu = '.context-menu';
    case ContextMenuGroup = '.context-menu .context-menu-group';
    case ContextMenuSubmenu = '.context-menu[data-parent]';
    case ModalTitle = '.modal .modal-title';
    case ProgressCounterTotal = '.tx-warming-progress-modal .tx-warming-progress-modal-counter > div:nth-child(1) > strong:nth-child(2)';
    case ProgressPlaceholder = '.tx-warming-progress-modal .tx-warming-progress-placeholder';
    case SelectAllCheckbox = '#tx-warming-sites-select-all';
    case SettingsLimit = '#tx-warming-settings-limit';
    case SettingsStrategy = '#tx-warming-settings-strategy';
    case SiteGroupSelector = '.tx-warming-sites-group-selector';
    case ToolbarItem = '#eliashaeussler-typo3warming-backend-toolbaritems-cachewarmuptoolbaritem';
    case UserAgentCopyButton = '.tx-warming-sites-modal .tx-warming-user-agent-copy-action';
}
