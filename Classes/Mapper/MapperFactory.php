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

namespace EliasHaeussler\Typo3Warming\Mapper;

use CuyZ\Valinor;
use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming\Domain;
use EliasHaeussler\Typo3Warming\Exception;
use TYPO3\CMS\Core;

/**
 * MapperFactory
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final readonly class MapperFactory
{
    public function __construct(
        private CacheWarmup\Crawler\Strategy\CrawlingStrategyFactory $crawlingStrategyFactory,
        private Domain\Repository\SiteRepository $siteRepository,
    ) {}

    public function get(): Valinor\Mapper\TreeMapper
    {
        return (new Valinor\MapperBuilder())
            ->registerConstructor(
                $this->crawlingStrategyFactory->get(...),
                $this->mapSites(...),
            )
            ->allowSuperfluousKeys()
            ->allowScalarValueCasting()
            ->allowUndefinedValues()
            ->mapper()
        ;
    }

    /**
     * @throws Exception\SiteCannotBeWarmed
     */
    private function mapSites(string $siteIdentifier): Core\Site\Entity\Site
    {
        return $this->siteRepository->findOneByIdentifier($siteIdentifier)
            ?? throw new Exception\SiteCannotBeWarmed($siteIdentifier);
    }
}
