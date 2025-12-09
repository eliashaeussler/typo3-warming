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
    private const CONTEXTS = [
        Action\WarmupActionContext::Page,
        Action\WarmupActionContext::Site,
    ];

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
    protected $itemsConfiguration = [];

    /**
     * @var array<non-empty-string, array{Action\WarmupAction, Action\WarmupActionContext}>
     */
    private array $actions = [];

    public function __construct(
        private readonly Domain\Repository\SiteRepository $siteRepository,
        private readonly Configuration\Configuration $configuration,
        private readonly Action\WarmupActionsProvider $actionsProvider,
        Core\Schema\TcaSchemaFactory $tcaSchemaFactory,
        Backend\Routing\UriBuilder $uriBuilder,
    ) {
        if ((new Core\Information\Typo3Version())->getMajorVersion() >= 14) {
            /* @phpstan-ignore arguments.count */
            parent::__construct($tcaSchemaFactory, $uriBuilder);
        } else {
            // @todo Remove once support for TYPO3 v13 is dropped
            parent::__construct();
        }
    }

    protected function canRender(string $itemName, string $type): bool
    {
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
        $this->initItems();
    }

    public function getPriority(): int
    {
        return 45;
    }

    private function initItems(): void
    {
        // Early return if cache warmup from page tree is disabled globally
        if (!$this->configuration->enabledInPageTree) {
            return;
        }

        $pageId = $this->getCurrentPageId();
        $dividerAdded = false;

        // Early return on system root page
        if ($pageId === null || $pageId === 0) {
            return;
        }

        foreach (self::CONTEXTS as $context) {
            $actions = $this->actionsProvider->provideActions($context, $pageId);

            // Skip invalid and unsupported actions
            if ($actions === null || $actions->siteLanguages === []) {
                continue;
            }

            $itemName = $context->value;

            // Skip non-renderable items
            if (!$this->canRender($itemName, 'item')) {
                continue;
            }

            $itemConfiguration = [
                'type' => 'submenu',
                'label' => $context->label(),
                'iconIdentifier' => $context->icon(),
                'childItems' => [],
            ];

            // Add actions as child elements to the current item
            foreach ($actions->getActions() as $action) {
                $childItemName = $itemName . '_' . $action->name;
                $itemConfiguration['childItems'][$childItemName] = [
                    'label' => $action->label,
                    'iconIdentifier' => $action->icon,
                    'callbackAction' => $context->callbackAction(),
                ];

                // Store action for further processing
                $this->actions[$childItemName] = [$action, $context];
            }

            // Add divider if not already added
            if (!$dividerAdded) {
                $this->itemsConfiguration['cacheWarmupDivider'] = [
                    'type' => 'divider',
                ];
                $dividerAdded = true;
            }

            // Add item configuration
            $this->itemsConfiguration[$itemName] = $itemConfiguration;
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
        $pageId = $this->getCurrentPageId();

        if ($pageId === null) {
            return null;
        }

        return $this->siteRepository->findOneByPageId($pageId);
    }

    /**
     * @return non-negative-int|null
     */
    private function getCurrentPageId(): ?int
    {
        if ($this->record === null) {
            return null;
        }

        /** @var non-negative-int $pageId */
        $pageId = $this->getPreviewPid();

        return $pageId;
    }
}
