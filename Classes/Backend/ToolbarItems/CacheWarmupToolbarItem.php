<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "cache_warmup".
 *
 * Copyright (C) 2021 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3CacheWarmup\Backend\ToolbarItems;

use EliasHaeussler\Typo3CacheWarmup\Configuration\Extension;
use EliasHaeussler\Typo3CacheWarmup\Exception\UnsupportedConfigurationException;
use EliasHaeussler\Typo3CacheWarmup\Exception\UnsupportedSiteException;
use EliasHaeussler\Typo3CacheWarmup\Sitemap\SitemapLocator;
use EliasHaeussler\Typo3CacheWarmup\Traits\TranslatableTrait;
use EliasHaeussler\Typo3CacheWarmup\Utility\AccessUtility;
use TYPO3\CMS\Backend\Toolbar\ToolbarItemInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * CacheWarmupToolbarItem
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class CacheWarmupToolbarItem implements ToolbarItemInterface
{
    use TranslatableTrait;

    /**
     * @var array[]
     */
    protected $actions;

    /**
     * @param PageRenderer $pageRenderer
     * @param SiteFinder $siteFinder
     * @param IconFactory $iconFactory
     * @param SitemapLocator $sitemapLocator
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function __construct(
        PageRenderer $pageRenderer,
        SiteFinder $siteFinder,
        IconFactory $iconFactory,
        SitemapLocator $sitemapLocator
    ) {
        $this->actions = [];

        $pageRenderer->loadRequireJsModule('TYPO3/CMS/CacheWarmup/Backend/Toolbar/CacheWarmupMenu');
        $pageRenderer->addInlineLanguageLabelArray([
            'cacheWarmup.notification.error.title' => static::translate('notification.error.title'),
            'cacheWarmup.notification.error.message' => static::translate('notification.error.message'),
            'cacheWarmup.notification.action.showReport' => static::translate('notification.action.showReport'),
            'cacheWarmup.modal.title' => static::translate('modal.title'),
            'cacheWarmup.modal.panel.failed' => static::translate('modal.panel.failed'),
            'cacheWarmup.modal.panel.successful' => static::translate('modal.panel.successful'),
            'cacheWarmup.modal.action.view' => static::translate('modal.action.view'),
        ]);

        foreach (array_filter($siteFinder->getAllSites(), [AccessUtility::class, 'canWarmupCacheOfSite']) as $site) {
            $row = BackendUtility::getRecord('pages', $site->getRootPageId());

            // Skip site if associated root page is not available
            if (!is_array($row)) {
                continue;
            }

            $action = [
                'title' => $site->getConfiguration()['websiteTitle'] ?: BackendUtility::getRecordTitle('pages', $row),
                'pageId' => $site->getRootPageId(),
                'iconIdentifier' => $iconFactory->getIconForRecord('pages', $row)->getIdentifier(),
            ];

            if ($sitemapLocator->siteContainsSitemap($site)) {
                $action['sitemapUrl'] = $sitemapLocator->locateBySite($site)->getUri();
            } else {
                $action['missing'] = true;
            }

            $this->actions[] = $action;
        }
    }

    public function checkAccess(): bool
    {
        return count($this->actions) > 0;
    }

    public function getItem(): string
    {
        return $this->buildView('CacheWarmupToolbarItem.html')->render();
    }

    public function hasDropDown(): bool
    {
        return true;
    }

    public function getDropDown(): string
    {
        $view = $this->buildView('CacheWarmupToolbarItemDropDown.html');
        $view->assign('actions', $this->actions);

        return $view->render();
    }

    /**
     * @return array<string, string>
     */
    public function getAdditionalAttributes(): array
    {
        return [];
    }

    public function getIndex(): int
    {
        // Clear cache toolbar item has index=25
        return 27;
    }

    protected function buildView(string $filename): StandaloneView
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateRootPaths(['EXT:cache_warmup/Resources/Private/Templates']);
        $view->setPartialRootPaths(['EXT:cache_warmup/Resources/Private/Partials']);
        $view->setTemplate($filename);
        $view->getRequest()->setControllerExtensionName(Extension::NAME);

        return $view;
    }
}
