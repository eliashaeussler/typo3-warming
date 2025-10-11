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

namespace EliasHaeussler\Typo3Warming\Backend\ContextMenu\ItemProviders;

use EliasHaeussler\Typo3Warming\Backend\Action;
use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Domain;
use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;

/**
 * CacheWarmupProvider
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class CacheWarmupProvider extends Backend\ContextMenu\ItemProviders\PageProvider
{
    /**
     * @var array<string, array{
     *     type: string
     * }|array{
     *     type: string,
     *     label: string,
     *     iconIdentifier: string,
     *     callbackAction: string,
     *     childItems?: array<string, array{
     *         label?: string,
     *         iconIdentifier?: string,
     *         callbackAction?: string,
     *     }>,
     * }>
     */
    protected $itemsConfiguration = [
        'cacheWarmupDivider' => [
            'type' => 'divider',
        ],
        Action\WarmupActionContext::Page->value => [
            'type' => 'item',
            'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang.xlf:contextMenu.item.cacheWarmup',
            'iconIdentifier' => 'cache-warmup-page',
            'callbackAction' => 'warmupPageCache',
        ],
        Action\WarmupActionContext::Site->value => [
            'type' => 'item',
            'label' => 'LLL:EXT:warming/Resources/Private/Language/locallang.xlf:contextMenu.item.cacheWarmupAll',
            'iconIdentifier' => 'cache-warmup-site',
            'callbackAction' => 'warmupSiteCache',
        ],
    ];

    /**
     * @var array<non-empty-string, array{Action\WarmupAction, Action\WarmupActionContext}>
     */
    private array $actions = [];

    public function __construct(
        private readonly Domain\Repository\SiteRepository $siteRepository,
        private readonly Configuration\Configuration $configuration,
        private readonly Action\WarmupActionsProvider $actionsProvider,
    ) {
        parent::__construct();
    }

    protected function canRender(string $itemName, string $type): bool
    {
        // Early return if cache warmup from page tree is disabled globally
        if (!$this->configuration->enabledInPageTree) {
            return false;
        }

        if (\in_array($itemName, $this->disabledItems, true)) {
            return false;
        }

        return true;
    }

    /**
     * @param array<string, array<string, mixed>> $items
     * @return array<string, array<string, mixed>>
     */
    public function addItems(array $items): array
    {
        $this->initialize();

        $localItems = $this->prepareItems($this->itemsConfiguration);
        $items += $localItems;

        return $items;
    }

    protected function initialize(): void
    {
        parent::initialize();
        $this->initSubMenus();
    }

    public function getPriority(): int
    {
        return 45;
    }

    private function initSubMenus(): void
    {
        $site = $this->getCurrentSite();

        // Early return if site cannot be resolved
        if ($site === null) {
            return;
        }

        $pageId = $this->getPreviewPid();

        foreach ($this->itemsConfiguration as $itemName => &$configuration) {
            $type = $configuration['type'];

            // Skip pseudo types and non-renderable items
            if ($type !== 'item' || !$this->canRender($itemName, $type)) {
                continue;
            }

            $context = Action\WarmupActionContext::from($itemName);
            $actions = match ($context) {
                Action\WarmupActionContext::Page => $this->actionsProvider->providePageActions($pageId),
                Action\WarmupActionContext::Site => $this->actionsProvider->provideSiteActions($pageId),
            };

            // Ignore item if no languages are available
            if ($actions === null || $actions->siteLanguages === []) {
                $this->disabledItems[] = $itemName;
                continue;
            }

            // Treat current item as submenu
            $configuration['type'] = 'submenu';
            $configuration['childItems'] = [];

            // Add actions as child elements to the current item
            foreach ($actions->getActions() as $action) {
                $childItemName = $itemName . '_' . $action->name;
                $configuration['childItems'][$childItemName] = [
                    'label' => $action->label,
                    'iconIdentifier' => $action->icon,
                    'callbackAction' => $configuration['callbackAction'] ?? null,
                ];

                // Store action for further processing
                $this->actions[$childItemName] = [$action, $context];
            }

            // Callback action is not required on the parent item
            unset($configuration['callbackAction']);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getAdditionalAttributes(string $itemName): array
    {
        $attributes = [
            'data-callback-module' => '@eliashaeussler/typo3-warming/backend/context-menu-action',
        ];

        // Early return if current item is not part of a submenu
        // within the configured context menu items
        if (!isset($this->actions[$itemName])) {
            return $attributes;
        }

        [$action, $context] = $this->actions[$itemName];

        // Add site identifier as data attribute
        if ($context === Action\WarmupActionContext::Site) {
            $attributes['data-site-identifier'] = $this->getCurrentSite()?->getIdentifier();
        }

        // Add action identifier (language ID or special item) as data attribute
        $attributes['data-action-identifier'] = $action->identifier;

        return $attributes;
    }

    private function getCurrentSite(): ?Core\Site\Entity\Site
    {
        if ($this->record === null) {
            return null;
        }

        /** @var non-negative-int $pageId */
        $pageId = $this->getPreviewPid();

        return $this->siteRepository->findOneByPageId($pageId);
    }
}
