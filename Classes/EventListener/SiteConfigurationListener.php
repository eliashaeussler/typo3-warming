<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\EventListener;

use EliasHaeussler\Typo3Warming\Cache;
use TYPO3\CMS\Core;

/**
 * SiteConfigurationListener
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class SiteConfigurationListener
{
    public function __construct(
        private readonly Cache\SitemapsCache $cache,
        private readonly Core\Site\SiteFinder $siteFinder,
    ) {
    }

    public function __invoke(Core\Configuration\Event\SiteConfigurationBeforeWriteEvent $event): void
    {
        try {
            $site = $this->siteFinder->getSiteByIdentifier($event->getSiteIdentifier());
        } catch (Core\Exception\SiteNotFoundException) {
            // Ignore new sites, they should not be cached anyway
            return;
        }

        $this->cache->remove($site);
    }
}
