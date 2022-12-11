<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Controller;

use EliasHaeussler\CacheWarmup\Crawler\CrawlerInterface;
use EliasHaeussler\CacheWarmup\CrawlingState;
use EliasHaeussler\Typo3Warming\Exception\MissingPageIdException;
use EliasHaeussler\Typo3Warming\Exception\UnsupportedConfigurationException;
use EliasHaeussler\Typo3Warming\Exception\UnsupportedSiteException;
use EliasHaeussler\Typo3Warming\Request\WarmupRequest;
use EliasHaeussler\Typo3Warming\Service\CacheWarmupService;
use EliasHaeussler\Typo3Warming\Sitemap\SitemapLocator;
use EliasHaeussler\Typo3Warming\Traits\BackendUserAuthenticationTrait;
use EliasHaeussler\Typo3Warming\Traits\TranslatableTrait;
use EliasHaeussler\Typo3Warming\Traits\ViewTrait;
use EliasHaeussler\Typo3Warming\Utility\AccessUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * CacheWarmupController
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class CacheWarmupController
{
    use BackendUserAuthenticationTrait;
    use TranslatableTrait;
    use ViewTrait;

    public const MODE_SITE = 'site';
    public const MODE_PAGE = 'page';

    public const STATE_FAILED = 'failed';
    public const STATE_WARNING = 'warning';
    public const STATE_SUCCESS = 'success';
    public const STATE_UNKNOWN = 'unknown';

    private SiteFinder $siteFinder;
    private CacheWarmupService $warmupService;
    private IconFactory $iconFactory;
    private SitemapLocator $sitemapLocator;
    private DenormalizerInterface $denormalizer;

    public function __construct(
        SiteFinder $siteFinder,
        CacheWarmupService $warmupService,
        IconFactory $iconFactory,
        SitemapLocator $sitemapLocator
    ) {
        $this->siteFinder = $siteFinder;
        $this->warmupService = $warmupService;
        $this->iconFactory = $iconFactory;
        $this->sitemapLocator = $sitemapLocator;
        $this->denormalizer = new ObjectNormalizer();
    }

    /**
     * @throws MissingPageIdException
     * @throws SiteNotFoundException
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function mainAction(ServerRequestInterface $request): ResponseInterface
    {
        // Ensure request was sent using the EventSource API performing an SSE request
        if (['text/event-stream'] !== $request->getHeader('Accept')) {
            return $this->buildBadRequestResponse();
        }

        // Build warmup request object
        try {
            $warmupRequest = $this->createWarmupRequest($request->getQueryParams());
            $warmupRequest->setUpdateCallback([$this, 'sendWarmupProgressEvent']);
            $warmupRequest->setSite($this->determineSite($warmupRequest->getPageId()));
        } catch (ExceptionInterface $e) {
            return $this->buildBadRequestResponse();
        }

        // Send headers for SSE
        $headers = [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ];
        foreach ($headers as $key => $value) {
            header(sprintf('%s: %s', $key, $value));
        }

        // Perform cache warmup
        $crawler = $this->performCacheWarmup($warmupRequest);

        // Send final response
        $this->sendServerEvent(
            $warmupRequest->getId(),
            'warmupFinished',
            $this->buildJsonResponseData($warmupRequest, $crawler)
        );

        return new Response();
    }

    public function sendWarmupProgressEvent(WarmupRequest $request): void
    {
        $successfulUrls = iterator_to_array($request->getSuccessfulCrawls());
        $failedUrls = iterator_to_array($request->getFailedCrawls());

        $eventData = [
            'progress' => [
                'current' => \count($successfulUrls) + \count($failedUrls),
                'total' => $request->getTotal(),
            ],
            'urls' => [
                'successful' => array_map([$this, 'decorateCrawlingState'], $successfulUrls),
                'failed' => array_map([$this, 'decorateCrawlingState'], $failedUrls),
            ],
        ];
        $this->sendServerEvent($request->getId(), 'warmupProgress', $eventData, 50);
    }

    /**
     * @param array<string, mixed> $eventData
     */
    private function sendServerEvent(string $id, string $eventName, array $eventData, int $retry = null): void
    {
        // Build event
        $event = [
            'id' => $id,
            'event' => $eventName,
            'data' => json_encode($eventData, JSON_THROW_ON_ERROR),
        ];
        if ($retry !== null && $retry > 0) {
            $event['retry'] = $retry;
        }

        // Print event data to buffer
        foreach ($event as $key => $value) {
            echo sprintf('%s: %s', $key, $value) . PHP_EOL;
        }
        echo PHP_EOL;

        // Flush output buffer
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }

    /**
     * @throws MissingPageIdException
     * @throws SiteNotFoundException
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function legacyWarmupAction(ServerRequestInterface $request): ResponseInterface
    {
        // Build warmup request object
        try {
            $warmupRequest = $this->createWarmupRequest($request->getQueryParams());
            $warmupRequest->setSite($this->determineSite($warmupRequest->getPageId()));
        } catch (ExceptionInterface $e) {
            return $this->buildBadRequestResponse();
        }

        // Perform cache warmup
        $crawler = $this->performCacheWarmup($warmupRequest);

        // Redirect to requested URL
        $redirectUrl = $this->getRedirectUrl($request);
        if ($redirectUrl !== '') {
            return new RedirectResponse(GeneralUtility::locationHeaderUrl($redirectUrl), 301);
        }

        return new JsonResponse($this->buildJsonResponseData($warmupRequest, $crawler));
    }

    /**
     * @throws MissingPageIdException
     * @throws SiteNotFoundException
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    private function performCacheWarmup(WarmupRequest $warmupRequest): CrawlerInterface
    {
        switch ($warmupRequest->getMode()) {
            case self::MODE_PAGE:
                if (empty($warmupRequest->getPageId())) {
                    throw UnsupportedConfigurationException::forMissingPageId();
                }

                return $this->warmupService->warmupPages([$warmupRequest->getPageId()], $warmupRequest);

            case self::MODE_SITE:
            default:
                $site = $warmupRequest->getSite();

                if ($site === null) {
                    throw MissingPageIdException::create();
                }

                return $this->warmupService->warmupSites([$site], $warmupRequest);
        }
    }

    /**
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    public function fetchSitesAction(): ResponseInterface
    {
        $actions = [];

        foreach (array_filter($this->siteFinder->getAllSites(), [AccessUtility::class, 'canWarmupCacheOfSite']) as $site) {
            $row = BackendUtility::getRecord('pages', $site->getRootPageId(), '*', ' AND hidden = 0');

            // Skip site if associated root page is not available
            if (!\is_array($row)) {
                continue;
            }

            $sitemapsFound = false;
            $action = [
                'title' => ($site->getConfiguration()['websiteTitle'] ?? null) ?: BackendUtility::getRecordTitle('pages', $row),
                'pageId' => $site->getRootPageId(),
                'iconIdentifier' => $this->iconFactory->getIconForRecord('pages', $row)->getIdentifier(),
                'sitemaps' => [],
            ];

            // Check all available languages for possible sitemaps
            foreach ($this->sitemapLocator->locateAllBySite($site) as $sitemap) {
                $siteLanguage = $sitemap->getSiteLanguage();
                \assert($siteLanguage instanceof SiteLanguage);

                $sitemapConfiguration = [
                    'language' => $siteLanguage,
                    'isDefaultLanguage' => $siteLanguage === $site->getDefaultLanguage(),
                ];

                if ($this->sitemapLocator->siteContainsSitemap($site, $siteLanguage)) {
                    $sitemapConfiguration['url'] = (string)$sitemap->getUri();
                    $sitemapsFound = true;
                } else {
                    $sitemapConfiguration['missing'] = true;
                }

                $action['sitemaps'][] = $sitemapConfiguration;
            }

            // Add flag if no site language has a sitemap
            if (!$sitemapsFound) {
                $action['missing'] = true;
            }

            $actions[] = $action;
        }

        $view = $this->buildView('CacheWarmupToolbarItemActions.html');
        $view->assign('actions', $actions);

        return new HtmlResponse($view->render());
    }

    /**
     * @param array<string, mixed> $queryParams
     * @throws ExceptionInterface
     */
    private function createWarmupRequest(array $queryParams): WarmupRequest
    {
        return $this->denormalizer->denormalize($queryParams, WarmupRequest::class);
    }

    /**
     * @throws MissingPageIdException
     * @throws SiteNotFoundException
     */
    private function determineSite(?int $pageId): Site
    {
        $allSites = $this->siteFinder->getAllSites();

        if ($pageId !== null) {
            return $this->siteFinder->getSiteByPageId($pageId);
        }

        if (\count($allSites) !== 1) {
            throw MissingPageIdException::create();
        }

        return end($allSites);
    }

    private function getRedirectUrl(ServerRequestInterface $request): string
    {
        $parsedBody = $request->getParsedBody();

        if (\is_array($parsedBody)) {
            $redirect = $parsedBody['redirect'] ?? null;
        } elseif (\is_object($parsedBody) && property_exists($parsedBody, 'redirect')) {
            $redirect = $parsedBody->redirect;
        }

        $redirect ??= $request->getQueryParams()['redirect'] ?? '';

        return GeneralUtility::sanitizeLocalUrl($redirect);
    }

    /**
     * @return array<string, mixed>
     * @throws MissingPageIdException
     */
    private function buildJsonResponseData(WarmupRequest $warmupRequest, CrawlerInterface $crawler): array
    {
        $successfulCount = \count($crawler->getSuccessfulUrls());
        $failedCount = \count($crawler->getFailedUrls());
        $state = $this->determineCrawlState($successfulCount, $failedCount);

        $data = [
            'state' => $state,
            'title' => static::translate('notification.title.' . $state),
            'urls' => [
                'failed' => array_map([$this, 'decorateCrawlingState'], $crawler->getFailedUrls()),
                'successful' => array_map([$this, 'decorateCrawlingState'], $crawler->getSuccessfulUrls()),
            ],
        ];

        // Throw exception if page ID is not available
        $pageId = $warmupRequest->getPageId();
        if ($pageId === null) {
            throw MissingPageIdException::create();
        }

        switch ($warmupRequest->getMode()) {
            case self::MODE_PAGE:
                $pageTitle = $this->getPageTitle($pageId);
                $data['message'] = static::translate('notification.message.page.' . $state, [$pageTitle, $pageId]);
                break;

            case self::MODE_SITE:
            default:
                $site = $warmupRequest->getSite();

                if ($site === null) {
                    throw MissingPageIdException::create();
                }

                $pageTitle = $this->getPageTitle($site->getRootPageId());
                $data['message'] = static::translate('notification.message.site', [$pageTitle, $pageId, $successfulCount, $failedCount]);
                break;
        }

        return $data;
    }

    private function buildBadRequestResponse(string $reason = 'Invalid Request Headers'): ResponseInterface
    {
        return new Response(null, 400, [], $reason);
    }

    /**
     * @throws MissingPageIdException
     */
    private function getPageTitle(int $pageId): string
    {
        $record = BackendUtility::getRecord('pages', $pageId);

        if ($record === null) {
            throw MissingPageIdException::create();
        }

        return BackendUtility::getRecordTitle('pages', $record);
    }

    private function determineCrawlState(int $successfulCount, int $failedCount): string
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

    private function decorateCrawlingState(CrawlingState $crawlingState): string
    {
        return (string)$crawlingState->getUri();
    }
}
