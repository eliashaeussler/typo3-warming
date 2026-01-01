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

namespace EliasHaeussler\Typo3Warming\Mapper;

use CuyZ\Valinor;
use EliasHaeussler\CacheWarmup;
use mteu\TypedExtConf;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;

/**
 * ConfigurationMapperFactory
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
#[DependencyInjection\Attribute\AsAlias(TypedExtConf\Mapper\MapperFactory::class)]
final readonly class ConfigurationMapperFactory implements TypedExtConf\Mapper\MapperFactory
{
    public function __construct(
        private CacheWarmup\Crawler\Strategy\CrawlingStrategyFactory $crawlingStrategyFactory,
        private CacheWarmup\Config\Component\OptionsParser $optionsParser,
    ) {}

    public function create(): Valinor\Mapper\TreeMapper
    {
        return (new Valinor\MapperBuilder())
            ->allowPermissiveTypes()
            ->allowScalarValueCasting()
            ->allowUndefinedValues()
            ->registerConverter($this->mapOptions(...))
            ->registerConverter($this->mapCrawlingStrategy(...))
            ->registerConverter($this->mapIntegerList(...))
            ->registerConverter($this->mapStringList(...))
            ->mapper()
        ;
    }

    /**
     * @return array<string, mixed>
     * @throws CacheWarmup\Exception\OptionsAreInvalid
     * @throws CacheWarmup\Exception\OptionsAreMalformed
     */
    private function mapOptions(string $options): array
    {
        if ($options === '') {
            return [];
        }

        return $this->optionsParser->parse($options);
    }

    /**
     * @throws CacheWarmup\Exception\CrawlingStrategyDoesNotExist
     */
    private function mapCrawlingStrategy(string $strategy): ?CacheWarmup\Crawler\Strategy\CrawlingStrategy
    {
        if (!$this->crawlingStrategyFactory->has($strategy)) {
            return null;
        }

        return $this->crawlingStrategyFactory->get($strategy);
    }

    /**
     * @return list<int>
     */
    private function mapIntegerList(string $list): array
    {
        return Core\Utility\GeneralUtility::intExplode(',', $list, true);
    }

    /**
     * @return list<non-empty-string>
     */
    private function mapStringList(string $list): array
    {
        return Core\Utility\GeneralUtility::trimExplode(',', $list, true);
    }
}
