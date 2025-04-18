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

    ..  php:method:: warmup($sites, $pages, $limit, $strategy)

        Run cache warmup for given sites and pages.

        :param array $sites: List of site warmup requests.
        :param array $pages: List of page warmup requests.
        :param int|null $limit: Optional cache warmup limit.
        :param \EliasHaeussler\CacheWarmup\Crawler\Strategy\CrawlingStrategy|null $strategy: Optional crawling strategy.
        :returntype: :php:`\EliasHaeussler\Typo3Warming\Result\CacheWarmupResult`

..  _api-example:

Example
=======

..  code-block:: php

    use EliasHaeussler\CacheWarmup;
    use EliasHaeussler\Typo3Warming;
    use TYPO3\CMS\Core;

    $cacheWarmupService = Core\Utility\GeneralUtility::makeInstance(Typo3Warming\Service\CacheWarmupService::class);
    $siteFinder = Core\Utility\GeneralUtility::makeInstance(Core\Site\SiteFinder::class);

    $sites = [];
    $pages = [];

    // Create site warmup requests
    foreach ($siteFinder->getAllSites() as $site) {
        $sites[] = new Typo3Warming\ValueObject\Request\SiteWarmupRequest($site);
    }

    // Create page warmup requests
    foreach ([1, 2, 3] as $page) {
        $pages[] = new Typo3Warming\ValueObject\Request\PageWarmupRequest($page);
    }

    // Define optional cache warmup options
    $limit = 100;
    $strategy = new CacheWarmup\Crawler\Strategy\SortByPriorityStrategy();

    // Run cache warmup for sites and pages
    $result = $cacheWarmupService->warmup($sites, $pages, $limit, $strategy);

    // Fetch crawling states
    $failedUrls = $result->getResult()->getFailed();
    $successfulUrls = $result->getResult()->getSuccessful();

    // Fetch excluded URLs
    $excludedUrls = $result->getExcludedUrls();
