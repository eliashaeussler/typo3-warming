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
        $contextMenu = $context;

        foreach ($path as $pageName) {
            $contextMenu = $this->ensureTreeNodeIsOpen($pageName, $context);
        }

        $I->executeInSelenium(function (WebDriver\Remote\RemoteWebDriver $webDriver) use ($contextMenu): void {
            $webDriver->getMouse()->contextClick($contextMenu->getCoordinates());
        });
        $I->waitForElementVisible(Tests\Acceptance\Support\Enums\Selectors::ContextMenuGroup);
    }

    /**
     * @param list<non-empty-string> $path
     */
    public function selectInContextMenu(array $path): void
    {
        $I = $this->tester;

        $remaining = \count($path);
        $contextMenuIdentifier = '[data-contextmenu-parent="root"]';

        foreach ($path as $depth => $selector) {
            --$remaining;

            $I->waitForElementVisible($contextMenuIdentifier, 5);
            $I->executeInSelenium(
                function (WebDriver\Remote\RemoteWebDriver $webDriver) use (&$contextMenuIdentifier, $remaining, $selector): void {
                    $contextMenu = $webDriver->findElement(WebDriver\WebDriverBy::cssSelector($contextMenuIdentifier));
                    $items = $contextMenu->findElements(WebDriver\WebDriverBy::tagName('li'));

                    foreach ($items as $item) {
                        if ($item->getText() === $selector) {
                            $webDriver->getMouse()->click($item->getCoordinates());

                            if ($remaining > 0) {
                                $button = $item->findElement(WebDriver\WebDriverBy::tagName('button'));
                                $contextMenuIdentifier = \sprintf(
                                    '[data-contextmenu-parent="%s"]',
                                    (string)$button->getAttribute('data-contextmenu-id'),
                                );
                            }

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
        $I = $this->tester;
        $I->see($nodeText, 'div.nodes-list > .node');

        $xpath = './/*[text()=\'' . $nodeText . '\']/../../..';

        /** @var WebDriver\Remote\RemoteWebElement $context */
        $context = $I->executeInSelenium(
            static fn() => $context->findElement(WebDriver\WebDriverBy::xpath($xpath)),
        );

        if ($context->getAttribute('aria-expanded') === '1') {
            return $context;
        }

        try {
            $context->findElement(WebDriver\WebDriverBy::cssSelector('.node-toggle'))->click();
        } catch (WebDriver\Exception\NoSuchElementException) {
            // element not found so it may be already opened...
        } catch (WebDriver\Exception\ElementNotInteractableException) {
            // another possible exception if the chevron isn't there ... depends on facebook driver version
        }

        return $context;
    }
}
