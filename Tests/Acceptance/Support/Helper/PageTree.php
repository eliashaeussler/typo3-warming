<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2024 Elias Häußler <elias@haeussler.dev>
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

    public function __construct(Tests\Acceptance\Support\AcceptanceTester $tester)
    {
        $this->tester = $tester;
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

        $contextMenu = $context->findElement(WebDriver\WebDriverBy::cssSelector(self::$treeItemAnchorSelector));

        $I->executeInSelenium(function(WebDriver\Remote\RemoteWebDriver $webDriver) use ($contextMenu): void {
            $webDriver->getMouse()->contextClick($contextMenu->getCoordinates());
        });
        $I->waitForElementVisible('.context-menu');
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
                function(WebDriver\Remote\RemoteWebDriver $webDriver) use ($contextMenuId, $selector): void {
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
}
