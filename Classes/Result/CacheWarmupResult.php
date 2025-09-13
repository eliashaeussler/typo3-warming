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

namespace EliasHaeussler\Typo3Warming\Result;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming\Domain;
use EliasHaeussler\Typo3Warming\Http;
use TYPO3\CMS\Core;

/**
 * CacheWarmupResult
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class CacheWarmupResult
{
    /**
     * @param list<CacheWarmup\Sitemap\Sitemap> $excludedSitemaps
     * @param list<CacheWarmup\Sitemap\Url> $excludedUrls
     */
    public function __construct(
        private CacheWarmup\Result\CacheWarmupResult $result,
        private array $excludedSitemaps = [],
        private array $excludedUrls = [],
    ) {}

    public function getResult(): CacheWarmup\Result\CacheWarmupResult
    {
        return $this->result;
    }

    /**
     * @return array{
     *     successful: list<CacheWarmup\Result\CrawlingResult>,
     *     failed: list<CacheWarmup\Result\CrawlingResult>,
     * }
     */
    public function getCrawlingResultsBySite(
        Core\Site\Entity\Site $site,
        Core\Site\Entity\SiteLanguage $siteLanguage,
    ): array {
        return [
            'successful' => array_values(
                array_filter(
                    $this->result->getSuccessful(),
                    fn(CacheWarmup\Result\CrawlingResult $crawlingResult) => $this->filterBySite(
                        $crawlingResult,
                        $site,
                        $siteLanguage,
                    ),
                ),
            ),
            'failed' => array_values(
                array_filter(
                    $this->result->getFailed(),
                    fn(CacheWarmup\Result\CrawlingResult $crawlingResult) => $this->filterBySite(
                        $crawlingResult,
                        $site,
                        $siteLanguage,
                    ),
                ),
            ),
        ];
    }

    /**
     * @return array{
     *     successful: list<CacheWarmup\Result\CrawlingResult>,
     *     failed: list<CacheWarmup\Result\CrawlingResult>,
     * }
     */
    public function getCrawlingResultsByPage(int $pageId, ?string $pageType = null, ?int $languageId = null): array
    {
        return [
            'successful' => array_values(
                array_filter(
                    $this->result->getSuccessful(),
                    fn(CacheWarmup\Result\CrawlingResult $crawlingResult) => $this->filterByPage(
                        $crawlingResult,
                        $pageId,
                        $pageType,
                        $languageId,
                    ),
                ),
            ),
            'failed' => array_values(
                array_filter(
                    $this->result->getFailed(),
                    fn(CacheWarmup\Result\CrawlingResult $crawlingResult) => $this->filterByPage(
                        $crawlingResult,
                        $pageId,
                        $pageType,
                        $languageId,
                    ),
                ),
            ),
        ];
    }

    /**
     * @return list<CacheWarmup\Sitemap\Sitemap>
     */
    public function getExcludedSitemaps(): array
    {
        return $this->excludedSitemaps;
    }

    /**
     * @return list<CacheWarmup\Sitemap\Url>
     */
    public function getExcludedUrls(): array
    {
        return $this->excludedUrls;
    }

    private function filterBySite(
        CacheWarmup\Result\CrawlingResult $crawlingResult,
        Core\Site\Entity\Site $site,
        Core\Site\Entity\SiteLanguage $siteLanguage,
    ): bool {
        $url = $crawlingResult->getUri();

        if (!($url instanceof CacheWarmup\Sitemap\Url)) {
            return false;
        }

        $rootOrigin = $url->getRootOrigin();

        return $rootOrigin instanceof Domain\Model\SiteAwareSitemap
            && $rootOrigin->getSite() === $site
            && $rootOrigin->getSiteLanguage() === $siteLanguage
        ;
    }

    private function filterByPage(
        CacheWarmup\Result\CrawlingResult $crawlingResult,
        int $pageId,
        ?string $pageType = null,
        ?int $languageId = null,
    ): bool {
        $urlMetadata = $crawlingResult->getData()['urlMetadata'] ?? null;

        if (!($urlMetadata instanceof Http\Message\UrlMetadata)) {
            return false;
        }

        return $urlMetadata->pageId === $pageId
            && ($pageType === null || $urlMetadata->pageType === $pageType)
            && ($languageId === null || $urlMetadata->languageId === $languageId)
        ;
    }
}
