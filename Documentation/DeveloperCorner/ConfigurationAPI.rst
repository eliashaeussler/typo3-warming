.. include:: /Includes.rst.txt

.. _configuration-api:

=================
Configuration API
=================

In order to access the :ref:`extension configuration <extension-configuration>`,
a slim PHP API exists. Each configuration option is accessible by
an appropriate class method.

..  php:namespace:: EliasHaeussler\Typo3Warming\Configuration

..  php:class:: Configuration

    API to access all available extension configuration options.

    ..  php:method:: getLimit()

        Get the configured :ref:`crawler limit <extconf-limit>`.

        :returntype: int

    ..  php:method:: getCrawler()

        Get the configured :ref:`crawler class <extconf-crawler>`.

        :returntype: class-string<EliasHaeussler\CacheWarmup\Crawler\CrawlerInterface>

    ..  php:method:: getVerboseCrawler()

        Get the configured :ref:`verbose crawler class <extconf-verboseCrawler>`.

        :returntype: class-string<EliasHaeussler\CacheWarmup\Crawler\VerboseCrawlerInterface>

    ..  php:method:: getUserAgent()

        Get the calculated user-agent.

        :returntype: string

    ..  php:method:: getAll()

        Get all extension configuration options of this extension.

        :returntype: array

.. seealso::

    View the sources on GitHub:

    - `Configuration <https://github.com/eliashaeussler/typo3-warming/blob/main/Classes/Configuration/Configuration.php>`__
