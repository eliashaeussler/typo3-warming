..  include:: /Includes.rst.txt

..  _extension-configuration:

=======================
Extension configuration
=======================

The extension currently provides the following configuration options:

..  confval-menu::
    :name: confval-extension-configuration
    :display: list

..  _extension-configuration-crawler:

Crawler
=======

..  _extconf-crawler:

..  confval:: crawler
    :type: string (FQCN)
    :Default: :php:`\EliasHaeussler\Typo3Warming\Crawler\ConcurrentUserAgentCrawler`

    Default crawler to be used for crawling the requested pages.

    ..  note::

        Custom crawlers must implement the :php:interface:`\EliasHaeussler\CacheWarmup\Crawler\Crawler` interface.

..  _extconf-crawlerOptions:

..  confval:: crawlerOptions
    :type: string (JSON)

    JSON-encoded string of custom crawler options for the default
    :ref:`crawler <extconf-crawler>`. Applies only to crawlers implementing the
    :php:interface:`\EliasHaeussler\CacheWarmup\Crawler\ConfigurableCrawler`
    interface. For more information read :ref:`configurable-crawlers`.

    ..  versionchanged:: 4.0.0

        Client-related configuration was moved to :ref:`clientOptions <extconf-clientOptions>`
        in :ref:`version 4.0.0 <version-4.0.0>`.

..  _extconf-verboseCrawler:

..  confval:: verboseCrawler
    :type: string (FQCN)
    :Default: :php:`\EliasHaeussler\Typo3Warming\Crawler\OutputtingUserAgentCrawler`

    Verbose crawler to be used for cache warmup from the command-line.

    ..  note::

        Custom verbose crawlers must implement the
        :php:interface:`\EliasHaeussler\CacheWarmup\Crawler\VerboseCrawler` interface.

..  _extconf-verboseCrawlerOptions:

..  confval:: verboseCrawlerOptions
    :type: string (JSON)

    JSON-encoded string of custom crawler options for the
    :ref:`verbose crawler <extconf-verboseCrawler>`. Applies only to crawlers implementing
    the :php:interface:`\EliasHaeussler\CacheWarmup\Crawler\ConfigurableCrawler` interface.
    For more information read :ref:`configurable-crawlers`.

    ..  versionchanged:: 4.0.0

        Client-related configuration was moved to :ref:`clientOptions <extconf-clientOptions>`
        in :ref:`version 4.0.0 <version-4.0.0>`.

..  _extension-configuration-parser:

Parser
======

..  _extconf-parserOptions:

..  confval:: parserOptions
    :type: string (JSON)

    ..  versionadded:: 1.2.0

        `Feature: #502 - Allow configuration of XML parser client options <https://github.com/eliashaeussler/typo3-warming/pull/502>`__

    ..  versionchanged:: 4.0.0

        This configuration was renamed from `parserClientOptions` to `parserOptions`
        in :ref:`version 4.0.0 <version-4.0.0>`. Migrate existing configuration to
        :ref:`clientOptions <extconf-clientOptions>`.

    JSON-encoded string of parser options used within the XML parser to parse
    XML sitemaps. For more information read :ref:`parser-configuration`.

..  _extension-configuration-client:

Client
======

..  _extconf-clientOptions:

..  confval:: clientOptions
    :type: string (JSON)

    ..  versionadded:: 4.0.0

        `Feature: #844 - Introduce client options extension configuration <https://github.com/eliashaeussler/typo3-warming/pull/844>`__

    JSON-encoded string of options for the client used within the crawler and XML parser
    to parse and crawl XML sitemaps. All available
    `Guzzle client options <https://docs.guzzlephp.org/en/stable/quickstart.html#creating-a-client>`__
    are accepted and merged with :ref:`TYPO3's global client configuration <t3coreapi:typo3ConfVars_http>`
    stored in `$GLOBALS['TYPO3_CONF_VARS']['HTTP']`.

    ..  tip::

        If the XML sitemap is protected by HTTP authentication (basic auth), you can set the
        credentials as follows: :json:`{"auth": ["<username>", "<password>"]}`

        In case the XML sitemap does not have a valid SSL certificate, it is possible to disable
        the SSL verification: :json:`{"verify": false}`

        You can also combine both settings: :json:`{"auth": ["<username>", "<password>"], "verify": false}`

..  _extension-configuration-options:

Options
=======

..  _extconf-limit:

..  confval:: limit
    :name: extconf-limit
    :type: integer
    :Default: 250

    Allows to limit the number of crawled pages in one iteration.

    ..  tip::

        Can be set to :typoscript:`0` to crawl all available pages in XML sitemaps.

..  _extconf-exclude:

..  confval:: exclude
    :type: string (comma-separated list)

    Comma-separated list of exclude patterns to exclude URLs from cache
    warmup. The following formats are currently supported:

    -   Regular expressions with delimiter :php:`#`, e.g. :php:`#(no_cache|no_warming)=1#`
    -   Any pattern processable by the native PHP function `fnmatch <https://www.php.net/manual/de/function.fnmatch.php>`__,
        e.g. :php:`*no_cache=1*`

..  _extconf-strategy:

..  confval:: strategy
    :name: extconf-strategy
    :type: string

    Name of an available crawling strategy to use for cache warmup. Crawling
    strategies are used to prepare URLs before actually crawling them. This can
    be helpful to prioritize crawling of important URLs.

    ..  seealso::

        Read more at :ref:`crawling-strategies`.

..  _extension-configuration-page-tree:

Page tree
=========

..  _extconf-enablePageTree:

..  confval:: enablePageTree
    :type: boolean
    :Default: 1

    Enable cache warmup in the :ref:`page tree <page-tree>` context menu. This setting
    affects all users, including administrators.

..  _extconf-supportedDoktypes:

..  confval:: supportedDoktypes
    :type: string (comma-separated list)
    :Default: 1

    Comma-separated list of doktypes to be supported for cache warmup in the
    :ref:`page tree <page-tree>` context menu. Defaults to default pages with doktype
    :php:`1` only. If your project implements custom doktypes, you can add them here to
    support cache warmup from the context menu.

..  _extension-configuration-toolbar:

Toolbar
=======

..  _extconf-enableToolbar:

..  confval:: enableToolbar
    :type: boolean
    :Default: 1

    Enable cache warmup in the :ref:`backend toolbar <backend-toolbar>`. This setting
    affects all users, including administrators.
