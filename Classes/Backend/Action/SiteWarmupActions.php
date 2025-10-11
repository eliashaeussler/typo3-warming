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

namespace EliasHaeussler\Typo3Warming\Backend\Action;

use EliasHaeussler\Typo3Warming\Configuration;
use TYPO3\CMS\Core;

/**
 * SiteWarmupActions
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 *
 * @implements \IteratorAggregate<WarmupAction>
 */
final readonly class SiteWarmupActions implements \Countable, \IteratorAggregate
{
    /**
     * @param Core\Site\Entity\SiteLanguage[] $siteLanguages
     */
    public function __construct(
        public Core\Site\Entity\Site $site,
        public array $siteLanguages,
    ) {}

    /**
     * @return \Generator<WarmupAction>
     */
    public function getActions(): \Generator
    {
        if ($this->hasSelectAction()) {
            yield WarmupAction::special(
                'select',
                Configuration\Localization::translate('cacheWarmupAction.context.site.select'),
                'flags-multiple',
            );
        }

        foreach ($this->siteLanguages as $siteLanguage) {
            yield WarmupAction::fromSiteLanguage($siteLanguage);
        }

        yield from [];
    }

    public function count(): int
    {
        $count = \count($this->siteLanguages);

        if ($this->hasSelectAction()) {
            $count++;
        }

        return $count;
    }

    public function getIterator(): \Generator
    {
        yield from $this->getActions();
    }

    private function hasSelectAction(): bool
    {
        return \count($this->siteLanguages) > 1;
    }
}
