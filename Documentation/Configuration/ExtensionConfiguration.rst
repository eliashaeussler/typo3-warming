.. include:: /Includes.rst.txt

.. _extension-configuration:

=======================
Extension configuration
=======================

The extension currently provides the following configuration options:

.. _extconf-limit:

.. confval:: limit

    :type: integer
    :Default: 250

    Allows to limit the amount of crawled pages in one iteration.

    .. tip::

        Can be set to :typoscript:`0` to crawl all available pages in
        XML sitemaps.

.. _extconf-crawler:

.. confval:: crawler

    :type: string (FQCN)
    :Default: :php:class:`EliasHaeussler\\Typo3Warming\\Crawler\\ConcurrentUserAgentCrawler`

    Default crawler to be used for crawling the requested pages.

    .. note::

        Custom crawlers must implement
        :php:interface:`EliasHaeussler\\CacheWarmup\\Crawler\\CrawlerInterface`.

.. _extconf-verboseCrawler:

.. confval:: verboseCrawler

    :type: string (FQCN)
    :Default: :php:class:`EliasHaeussler\\Typo3Warming\\Crawler\\OutputtingUserAgentCrawler`

    Verbose crawler to be used for cache warmup from the command-line.

    .. note::

        Custom verbose crawlers must implement
        :php:interface:`EliasHaeussler\\CacheWarmup\\Crawler\\VerboseCrawlerInterface`.
