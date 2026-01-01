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

namespace EliasHaeussler\Typo3Warming\Backend\Action;

use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * PageWarmupActions
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 *
 * @implements \IteratorAggregate<WarmupAction>
 */
final readonly class PageWarmupActions implements \Countable, \IteratorAggregate
{
    /**
     * @param SiteLanguage[] $siteLanguages
     */
    public function __construct(
        public int $pageId,
        public array $siteLanguages,
    ) {}

    /**
     * @return \Generator<WarmupAction>
     */
    public function getActions(): \Generator
    {
        foreach ($this->siteLanguages as $siteLanguage) {
            yield WarmupAction::fromSiteLanguage($siteLanguage);
        }

        yield from [];
    }

    public function count(): int
    {
        return count($this->siteLanguages);
    }

    public function getIterator(): \Generator
    {
        yield from $this->getActions();
    }
}
