..  include:: /Includes.rst.txt

..  _configuration-api:

=================
Configuration API
=================

In order to access the :ref:`extension configuration <extension-configuration>`,
a slim PHP API exists. Each configuration option is accessible by
an appropriate class property.

..  versionchanged:: 4.2.0

    Several class methods were converted to class properties and deprecated in
    :ref:`version 4.2.0 <version-4.2.0>`. Calling those methods will be possible until
    version 5.0 of the extension, but deprecation notices will be logged on every method call.

..  php:namespace:: EliasHaeussler\Typo3Warming\Configuration

..  php:class:: Configuration

    API to access all available extension configuration options.

    ..  php:attr:: crawlerClass

        Get the configured :ref:`crawler class <extconf-crawler>`. To access the instantiated
        crawler, call the :php:`getCrawler` method instead.

    ..  php:attr:: crawlerOptions

        Get the configured :ref:`crawler options <extconf-crawlerOptions>`.

    ..  php:attr:: verboseCrawlerClass

        Get the configured :ref:`verbose crawler class <extconf-verboseCrawler>`. To access
        the instantiated crawler, call the :php:`getVerboseCrawler` method instead.

    ..  php:attr:: verboseCrawlerOptions

        Get the configured :ref:`verbose crawler options <extconf-verboseCrawlerOptions>`.

    ..  php:attr:: parserOptions

        Get the configured :ref:`parser options <extconf-parserOptions>`.

    ..  php:attr:: clientOptions

        Get the configured :ref:`client options <extconf-clientOptions>`.

    ..  php:attr:: limit

        Get the configured :ref:`crawler limit <extconf-limit>`.

    ..  php:attr:: excludePatterns

        Get the configured :ref:`exclude patterns <extconf-exclude>`.

    ..  php:attr:: crawlingStrategy

        Get the configured :ref:`crawling strategy <extconf-strategy>`.

    ..  php:attr:: enabledInPageTree

        Check whether cache warmup from :ref:`page tree <extconf-enablePageTree>` is enabled.

    ..  php:attr:: supportedDoktypes

        Get all :ref:`doktypes <t3coreapi:list-of-page-types>` that support cache warmup from
        page tree.

    ..  php:attr:: enabledInToolbar

        Check whether cache warmup from :ref:`toolbar <extconf-enableToolbar>` is enabled.

    ..  php:attr:: runAfterCacheClear

        Check whether cache warmup should be executed after a
        :ref:`page cache gets cleared <extconf-runAfterCacheClear>` by DataHandler.

    ..  php:method:: getCrawler()

        Get the configured :ref:`crawler <extconf-crawler>`.

        :returntype: :php:`\EliasHaeussler\CacheWarmup\Crawler\Crawler`

    ..  php:method:: getVerboseCrawler()

        Get the configured :ref:`verbose crawler <extconf-verboseCrawler>`.

        :returntype: :php:`\EliasHaeussler\CacheWarmup\Crawler\VerboseCrawler`

    ..  php:method:: getUserAgent()

        Get the calculated user-agent.

        ..  deprecated:: 4.2.0

            Call :php:`\EliasHaeussler\Typo3Warming\Http\Message\Request\RequestOptions::getUserAgent`
            instead. See :ref:`migration guide <version-4.2.0>` for more information.

        :returntype: :php:`string`

..  seealso::

    View the sources on GitHub:

    -   `Configuration <https://github.com/eliashaeussler/typo3-warming/blob/main/Classes/Configuration/Configuration.php>`__
