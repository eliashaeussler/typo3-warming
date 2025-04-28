..  include:: /Includes.rst.txt

..  _configuration-api:

=================
Configuration API
=================

In order to access the :ref:`extension configuration <extension-configuration>`,
a slim PHP API exists. Each configuration option is accessible by
an appropriate class method.

..  php:namespace:: EliasHaeussler\Typo3Warming\Configuration

..  php:class:: Configuration

    API to access all available extension configuration options.

    ..  php:method:: getCrawler()

        Get the configured :ref:`crawler <extconf-crawler>`.

        :returntype: :php:`\EliasHaeussler\CacheWarmup\Crawler\Crawler`

    ..  php:method:: getCrawlerOptions()

        Get the configured :ref:`crawler options <extconf-crawlerOptions>`.

        :returntype: :php:`array`

    ..  php:method:: getVerboseCrawler()

        Get the configured :ref:`verbose crawler <extconf-verboseCrawler>`.

        :returntype: :php:`\EliasHaeussler\CacheWarmup\Crawler\VerboseCrawler`

    ..  php:method:: getVerboseCrawlerOptions()

        Get the configured :ref:`verbose crawler options <extconf-verboseCrawlerOptions>`.

        :returntype: :php:`array`

    ..  php:method:: getParserOptions()

        Get the configured :ref:`parser options <extconf-parserOptions>`.

        :returntype: :php:`array`

    ..  php:method:: getClientOptions()

        Get the configured :ref:`client options <extconf-clientOptions>`.

        :returntype: :php:`array`

    ..  php:method:: getLimit()

        Get the configured :ref:`crawler limit <extconf-limit>`.

        :returntype: :php:`int`

    ..  php:method:: getExcludePatterns()

        Get the configured :ref:`exclude patterns <extconf-exclude>`.

        :returntype: :php:`list<string>`

    ..  php:method:: getStrategy()

        Get the configured :ref:`crawling strategy <extconf-strategy>`.

        :returntype: :php:`\EliasHaeussler\CacheWarmup\Crawler\Strategy\CrawlingStrategy|null`

    ..  php:method:: isEnabledInPageTree()

        Check whether cache warmup from :ref:`page tree <extconf-enablePageTree>` is enabled.

        :returntype: :php:`bool`

    ..  php:method:: getSupportedDoktypes()

        Get all :ref:`doktypes <t3coreapi:list-of-page-types>` that support cache warmup from
        page tree.

        :returntype: :php:`list<int>`

    ..  php:method:: isEnabledInToolbar()

        Check whether cache warmup from :ref:`toolbar <extconf-enableToolbar>` is enabled.

        :returntype: :php:`bool`

    ..  php:method:: getUserAgent()

        Get the calculated user-agent.

        :returntype: :php:`string`

..  seealso::

    View the sources on GitHub:

    -   `Configuration <https://github.com/eliashaeussler/typo3-warming/blob/main/Classes/Configuration/Configuration.php>`__
