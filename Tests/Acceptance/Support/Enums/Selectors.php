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

namespace EliasHaeussler\Typo3Warming\Tests\Acceptance\Support\Enums;

/**
 * Selectors
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
enum Selectors: string
{
    public const BackendRecordsModule = '[data-moduleroute-identifier="records"]';
    public const BackendLayoutModule = '[data-moduleroute-identifier="web_layout"]';
    public const BackendSettingsModule = '[data-moduleroute-identifier="system_settings"]';
    public const CheckboxMainGroup = '#tx-warming-checkbox-main-group';
    public const CheckboxMainLanguage0 = '#tx-warming-checkbox-main-0';
    public const CheckboxMainLanguage1 = '#tx-warming-checkbox-main-1';
    public const CheckboxRoot2 = '#tx-warming-checkbox-root-2';
    public const CodeMirrorWrapper = 'typo3-t3editor-codemirror';
    public const ConfigureExtensionsButton = '[data-import="@typo3/install/module/settings/extension-configuration.js"]';
    public const ContextMenu = '.context-menu';
    public const ContextMenuGroup = '.context-menu .context-menu-group';
    public const ContextMenuSubmenu = '.context-menu[data-contextmenu-parent]';
    public const ExtensionConfigurationModalCollapseHeader = '#heading-warming';
    public const InformationModal = 'typo3-backend-modal[content^="/typo3/record/info"]';
    public const ModalHeader = '.tx-warming-modal-header';
    public const ModalTitle = '.t3js-modal[open] .modal-header-title';
    public const ProgressCounterTotal = '.tx-warming-progress-modal .tx-warming-progress-modal-counter > div:nth-child(1) > strong:nth-child(2)';
    public const ProgressPlaceholder = '.tx-warming-progress-modal .tx-warming-progress-placeholder';
    public const ReportPanelActionButtonViewLogs = 'warming-report-panel .panel-collapse a[href*="/BackendLog/"]';
    public const ReportPanelActionButtonEditRecord = 'warming-report-panel .panel-collapse a[href*="/record/edit"]';
    public const ReportPanelActionButtonShowInfo = 'warming-report-panel .panel-collapse button[title*="information"]';
    public const SelectAllCheckbox = '#tx-warming-sites-select-all';
    public const SettingsLimit = '#tx-warming-settings-limit';
    public const SettingsStrategy = '#tx-warming-settings-strategy';
    public const ShowAllButton = '.tx-warming-sites-show-all';
    public const SiteGroupSelector = '.tx-warming-sites-group-selector';
    public const ToolbarItem = '#eliashaeussler-typo3warming-backend-toolbaritems-cachewarmuptoolbaritem';
    public const UserAgentCopyButton = '.tx-warming-sites-modal .tx-warming-user-agent-copy-action';

    // @todo Remove once support for TYPO3 v13 is dropped
    public const BackendRecordsModuleLegacy = '[data-moduleroute-identifier="web_list"]';
    public const BackendSettingsModuleLegacy = '[data-moduleroute-identifier="tools_toolssettings"]';
    public const ModalTitleLegacy = '.modal .modal-title';
}
