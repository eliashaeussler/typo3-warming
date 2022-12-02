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

.. _extconf-crawlerOptions:

.. confval:: crawlerOptions

    :type: string (JSON)

    JSON-encoded string of custom crawler options for the default
    :ref:`crawler <extconf-crawler>`. Applies only to crawlers implementing the
    :php:interface:`EliasHaeussler\\CacheWarmup\\Crawler\\ConfigurableCrawlerInterface`.
    For more information read :ref:`configurable-crawlers`.

.. _extconf-verboseCrawler:

.. confval:: verboseCrawler

    :type: string (FQCN)
    :Default: :php:class:`EliasHaeussler\\Typo3Warming\\Crawler\\OutputtingUserAgentCrawler`

    Verbose crawler to be used for cache warmup from the command-line.

    .. note::

        Custom verbose crawlers must implement
        :php:interface:`EliasHaeussler\\CacheWarmup\\Crawler\\VerboseCrawlerInterface`.

.. _extconf-verboseCrawlerOptions:

.. confval:: verboseCrawlerOptions

    :type: string (JSON)

    JSON-encoded string of custom crawler options for the verbose
    :ref:`crawler <extconf-verboseCrawler>`. Applies only to crawlers implementing the
    :php:interface:`EliasHaeussler\\CacheWarmup\\Crawler\\ConfigurableCrawlerInterface`.
    For more information read :ref:`configurable-crawlers`.

.. _extconf-supportedDoktypes:

.. confval:: supportedDoktypes

    :type: string (comma-separated list)
    :Default: 1

    Comma-separated list of doktypes to be supported for cache warmup in the
    :ref:`page tree <page-tree>` context menu. Defaults to default pages with doktype
    :php:`1` only. If your project implements custom doktypes, you can add them here to
    support cache warmup from the context menu.
