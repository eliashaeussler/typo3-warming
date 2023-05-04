..  include:: /Includes.rst.txt

..  _crawling-strategies:

===================
Crawling strategies
===================

Before URLs are crawled by a :ref:`crawler <crawlers>`, they can be
prepared by a specific strategy. This, for example, allows to
prioritize specific URLs or provide additional information to URLs.

..  php:namespace:: EliasHaeussler\CacheWarmup\Crawler\Strategy

..  php:interface:: CrawlingStrategy

    Interface for crawling strategy to prepare URLs before crawling.

    ..  php:method:: prepareUrls($urls)

        Prepare given URLs for crawling.

        :param array $urls: List of URLs to be prepared for crawling.
        :returntype: :php:`list<EliasHaeussler\CacheWarmup\Sitemap\Url>`

    ..  php:staticmethod:: getName()

        Get name of crawling strategy for use as identifier.

        :returntype: string

..  _shipped-crawling-strategies:

Shipped crawling strategies
===========================

The extension ships with the following crawling strategies:

-   :php:`sort-by-changefreq`: Sorts given URLs by their
    `changefreq <https://www.sitemaps.org/protocol.html#changefreqdef>`__
    node value.
-   :php:`sort-by-lastmod`: Sorts given URLs by their
    `lastmod <https://www.sitemaps.org/protocol.html#lastmoddef>`__
    node value.
-   :php:`sort-by-priority`: Sorts given URLs by their
    `priority <https://www.sitemaps.org/protocol.html#prioritydef>`__
    node value.

..  _implement-a-custom-strategy:

Implement a custom strategy
===========================

..  rst-class:: bignums

1.  Create a new crawling strategy

    The new strategy must implement the
    :php:interface:`EliasHaeussler\\CacheWarmup\\Crawler\\Strategy\\CrawlingStrategy`
    interface. Make sure to properly implement the :php:meth:`EliasHaeussler\\CacheWarmup\\Crawler\\Strategy\\CrawlingStrategy::getName`
    method to identify the crawling strategy.

2.  Configure the new crawling strategy

    Add the new strategy to the :ref:`extension configuration <extension-configuration>`.
    Use the strategy's name as configuration value.

3.  Flush system caches

    Finally, flush all system caches to ensure the correct crawling
    strategy is used for further cache warmup requests.

..  seealso::
    View the sources on GitHub:

    -   `CrawlingStrategy <https://github.com/eliashaeussler/cache-warmup/blob/main/src/Crawler/Strategy/CrawlingStrategy.php>`__
    -   `SortByChangeFrequencyStrategy <https://github.com/eliashaeussler/cache-warmup/blob/main/src/Crawler/Strategy/SortByChangeFrequencyStrategy.php>`__
    -   `SortByLastModificationDateStrategy <https://github.com/eliashaeussler/cache-warmup/blob/main/src/Crawler/Strategy/SortByLastModificationDateStrategy.php>`__
    -   `SortByPriorityStrategy <https://github.com/eliashaeussler/cache-warmup/blob/main/src/Crawler/Strategy/SortByPriorityStrategy.php>`__
