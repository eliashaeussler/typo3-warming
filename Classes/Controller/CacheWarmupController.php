<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "cache_warmup".
 *
 * Copyright (C) 2021 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3CacheWarmup\Controller;

use EliasHaeussler\CacheWarmup\Crawler\CrawlerInterface;
use EliasHaeussler\Typo3CacheWarmup\Exception\MissingPageIdException;
use EliasHaeussler\Typo3CacheWarmup\Exception\UnsupportedConfigurationException;
use EliasHaeussler\Typo3CacheWarmup\Exception\UnsupportedSiteException;
use EliasHaeussler\Typo3CacheWarmup\Service\CacheWarmupService;
use EliasHaeussler\Typo3CacheWarmup\Traits\TranslatableTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * CacheWarmupController
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class CacheWarmupController
{
    use TranslatableTrait;

    public const MODE_SITE = 'site';
    public const MODE_PAGE = 'page';

    public const STATE_FAILED = 'failed';
    public const STATE_WARNING = 'warning';
    public const STATE_SUCCESS = 'success';
    public const STATE_UNKNOWN = 'unknown';

    /**
     * @var SiteFinder
     */
    protected $siteFinder;

    /**
     * @var CacheWarmupService
     */
    protected $warmupService;

    public function __construct(SiteFinder $siteFinder, CacheWarmupService $warmupService)
    {
        $this->siteFinder = $siteFinder;
        $this->warmupService = $warmupService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws MissingPageIdException
     * @throws SiteNotFoundException
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function mainAction(ServerRequestInterface $request): ResponseInterface
    {
        $mode = $request->getQueryParams()['mode'] ?: self::MODE_SITE;
        $pageId = (int)$request->getQueryParams()['pageId'] ?: null;
        $site = $this->determineSite($pageId);

        switch ($mode) {
            case self::MODE_PAGE:
                if (empty($pageId)) {
                    throw UnsupportedConfigurationException::forMissingPageId();
                }

                $crawler = $this->warmupService->warmupPages([$pageId]);
                break;

            case self::MODE_SITE:
            default:
                $crawler = $this->warmupService->warmupSites([$site]);
                break;
        }

        $redirectUrl = $this->getRedirectUrl($request);
        if ($redirectUrl !== '') {
            return new RedirectResponse(GeneralUtility::locationHeaderUrl($redirectUrl), 301);
        }

        return $this->buildJsonResponse($mode, $pageId, $site, $crawler);
    }

    /**
     * @param int|null $pageId
     * @return Site
     * @throws MissingPageIdException
     * @throws SiteNotFoundException
     */
    protected function determineSite(?int $pageId): Site
    {
        $allSites = $this->siteFinder->getAllSites();

        if ($pageId !== null) {
            return $this->siteFinder->getSiteByPageId($pageId);
        }

        if (count($allSites) > 1) {
            throw MissingPageIdException::create();
        }

        return end($allSites);
    }

    protected function getRedirectUrl(ServerRequestInterface $request): string
    {
        $redirect = $request->getParsedBody()['redirect'] ?? $request->getQueryParams()['redirect'] ?? '';

        return GeneralUtility::sanitizeLocalUrl($redirect);
    }

    protected function buildJsonResponse(string $mode, ?int $pageId, Site $site, CrawlerInterface $crawler): JsonResponse
    {
        $successfulCount = count($crawler->getSuccessfulUrls());
        $failedCount = count($crawler->getFailedUrls());
        $state = $this->determineCrawlState($successfulCount, $failedCount);

        $data = [
            'state' => $state,
            'title' => static::translate('notification.title.' . $state),
        ];

        switch ($mode) {
            case self::MODE_PAGE:
                $pageTitle = $this->getPageTitle($pageId);
                $data['message'] = static::translate('notification.message.page.' . $state, [$pageTitle, $pageId]);
                break;

            case self::MODE_SITE:
            default:
                $pageTitle = $this->getPageTitle($site->getRootPageId());
                $data['message'] = static::translate('notification.message.site', [$pageTitle, $pageId, $successfulCount, $failedCount]);
                break;
        }

        return new JsonResponse($data);
    }

    protected function getPageTitle(int $pageId): string
    {
        return BackendUtility::getRecordTitle('pages', BackendUtility::getRecord('pages', $pageId));
    }

    protected function determineCrawlState(int $successfulCount, int $failedCount): string
    {
        if ($failedCount > 0 && $successfulCount === 0) {
            return self::STATE_FAILED;
        }
        if ($failedCount > 0 && $successfulCount > 0) {
            return self::STATE_WARNING;
        }
        if ($failedCount === 0) {
            return self::STATE_SUCCESS;
        }

        return self::STATE_UNKNOWN;
    }
}
