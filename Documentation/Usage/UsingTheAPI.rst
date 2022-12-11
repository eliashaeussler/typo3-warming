..  include:: /Includes.rst.txt

..  _using-the-api:

=============
Using the API
=============

Besides the usage via the TYPO3 backend and the console commands,
there is also a public PHP API. It can be used to execute the cache
warmup directly in PHP code.

..  php:namespace:: EliasHaeussler\Typo3Warming\Service

..  php:class:: CacheWarmupService

    Service to run cache warmup for sites and pages.

    ..  php:method:: warmupSites($sites, $request)

        Run cache warmup for given list of sites.

        :param array $sites: List of sites to be warmed up.
        :param EliasHaeussler\\Typo3Warming\\Request\\WarmupRequest $request: Additional cache warmup request parameters.
        :returntype: EliasHaeussler\\CacheWarmup\\Crawler\\CrawlerInterface

    ..  php:method:: warmupPages($pageIds, $request)

        Run cache warmup for given list of pages.

        :param array $pageIds: List of pages to be warmed up.
        :param EliasHaeussler\\Typo3Warming\\Request\\WarmupRequest $request: Additional cache warmup request parameters.
        :returntype: EliasHaeussler\\CacheWarmup\\Crawler\\CrawlerInterface

    ..  php:method:: generateUri($pageId, $languageId = null)

        Generate uri for given page and optional language.

        :param int $pageId: ID of the page for which the uri is to be generated.
        :param int $languageId: Optional language ID to respect when generating the uri.
        :returntype: Psr\\Http\\Message\\UriInterface

    ..  php:method:: getCrawler()

        Return crawler being used for cache warmup.

        :returntype: Psr\\Http\\Message\\UriInterface

    ..  php:method:: setCrawler($crawler)

        Set crawler to use for cache warmup.

        :param string|EliasHaeussler\\CacheWarmup\\Crawler\\CrawlerInterface $crawler: The crawler to use for cache warmup.
        :returns: The service object (fluent setter).

..  _api-example:

Example
=======

::

    use EliasHaeussler\Typo3Warming;
    use TYPO3\CMS\Core;

    $cacheWarmupService = Core\Utility\GeneralUtility::makeInstance(Typo3Warming\Service\CacheWarmupService::class);
    $request = new Typo3Warming\Request\WarmupRequest();

    // Get all sites
    $siteFinder = Core\Utility\GeneralUtility::makeInstance(Core\Site\SiteFinder::class);
    $sites = $siteFinder->getAllSites();

    // Run cache warmup for all sites
    $crawler = $cacheWarmupService->warmupSites($sites, $request);

    // Run cache warmup for single pages only
    $crawler = $cacheWarmupService->warmupPages([1, 2, 3], $request);

    // Evaluate crawling states
    $failedUrls = $crawler->getFailedUrls();
    $successfulUrls = $crawler->getSuccessfulUrls();
