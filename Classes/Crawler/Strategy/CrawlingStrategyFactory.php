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

namespace EliasHaeussler\Typo3Warming\Crawler\Strategy;

use EliasHaeussler\CacheWarmup;
use Symfony\Component\DependencyInjection;

/**
 * CrawlingStrategyFactory
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class CrawlingStrategyFactory
{
    /**
     * @param DependencyInjection\ServiceLocator<CacheWarmup\Crawler\Strategy\CrawlingStrategy> $strategies
     */
    public function __construct(
        #[DependencyInjection\Attribute\AutowireLocator('warming.crawling_strategy', defaultIndexMethod: 'getName')]
        private readonly DependencyInjection\ServiceLocator $strategies,
    ) {}

    public function get(string $strategy): ?CacheWarmup\Crawler\Strategy\CrawlingStrategy
    {
        if ($this->strategies->has($strategy)) {
            return $this->strategies->get($strategy);
        }

        return null;
    }

    /**
     * @return array<string, CacheWarmup\Crawler\Strategy\CrawlingStrategy>
     */
    public function getAll(): array
    {
        $strategies = [];

        foreach (array_keys($this->strategies->getProvidedServices()) as $crawlingStrategy) {
            $strategies[$crawlingStrategy] = $this->strategies->get($crawlingStrategy);
        }

        return $strategies;
    }

    public function has(string $strategy): bool
    {
        return $this->strategies->has($strategy);
    }
}
