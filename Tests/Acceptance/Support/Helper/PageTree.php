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

namespace EliasHaeussler\Typo3Warming\Tests\Acceptance\Support\Helper;

use EliasHaeussler\Typo3Warming\Tests;
use Facebook\WebDriver;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * PageTree
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class PageTree extends TestingFramework\Core\Acceptance\Helper\AbstractPageTree
{
    /**
     * @var Tests\Acceptance\Support\AcceptanceTester
     */
    protected $tester;

    private readonly Core\Information\Typo3Version $typo3Version;

    public function __construct(Tests\Acceptance\Support\AcceptanceTester $tester)
    {
        $this->tester = $tester;
        $this->typo3Version = new Core\Information\Typo3Version();
    }

    /**
     * @param non-empty-list<non-empty-string> $path
     */
    public function openContextMenu(array $path): void
    {
        $I = $this->tester;

        $I->waitForElementVisible(self::$pageTreeFrameSelector);

        $context = $this->getPageTreeElement();

        foreach ($path as $pageName) {
            $context = $this->ensureTreeNodeIsOpen($pageName, $context);
        }

        if ($this->typo3Version->getMajorVersion() >= 13) {
            $contextMenu = $context;
        } else {
            // @todo Remove once support for TYPO3 v12 is dropped
            $contextMenu = $context->findElement(WebDriver\WebDriverBy::cssSelector(self::$treeItemAnchorSelector));
        }

        $I->executeInSelenium(function (WebDriver\Remote\RemoteWebDriver $webDriver) use ($contextMenu): void {
            $webDriver->getMouse()->contextClick($contextMenu->getCoordinates());
        });
        $I->waitForElementVisible(Tests\Acceptance\Support\Enums\Selectors::ContextMenuGroup->value);
    }

    /**
     * @param list<non-empty-string> $path
     */
    public function selectInContextMenu(array $path): void
    {
        $I = $this->tester;

        foreach ($path as $depth => $selector) {
            $contextMenuId = sprintf('#contentMenu%d', $depth);

            $I->waitForElementVisible($contextMenuId, 5);
            $I->executeInSelenium(
                function (WebDriver\Remote\RemoteWebDriver $webDriver) use ($contextMenuId, $selector): void {
                    $contextMenu = $webDriver->findElement(WebDriver\WebDriverBy::cssSelector($contextMenuId));
                    $items = $contextMenu->findElements(WebDriver\WebDriverBy::tagName('li'));

                    foreach ($items as $item) {
                        if ($item->getText() === $selector) {
                            $webDriver->getMouse()->click($item->getCoordinates());
                            break;
                        }
                    }
                },
            );
        }
    }

    protected function ensureTreeNodeIsOpen(
        string $nodeText,
        WebDriver\Remote\RemoteWebElement $context,
    ): WebDriver\Remote\RemoteWebElement {
        // @todo Remove once support for TYPO3 v12 is dropped
        if ($this->typo3Version->getMajorVersion() < 13) {
            return parent::ensureTreeNodeIsOpen($nodeText, $context);
        }

        // @todo Remove once TF properly handles new page tree rendering
        $I = $this->tester;
        $I->see($nodeText, 'div.nodes-list > .node');

        /** @var WebDriver\Remote\RemoteWebElement $context */
        $context = $I->executeInSelenium(
            static fn() => $context->findElement(
                WebDriver\WebDriverBy::xpath('//*[text()=\'' . $nodeText . '\']/../../..'),
            ),
        );

        if ($context->getAttribute('aria-expanded') === '1') {
            return $context;
        }

        try {
            $context->findElement(WebDriver\WebDriverBy::cssSelector('.node-toggle'))->click();
        } catch (WebDriver\Exception\NoSuchElementException|WebDriver\Exception\ElementNotVisibleException) {
            // element not found so it may be already opened...
        } catch (WebDriver\Exception\ElementNotInteractableException) {
            // another possible exception if the chevron isn't there ... depends on facebook driver version
        }

        return $context;
    }
}
