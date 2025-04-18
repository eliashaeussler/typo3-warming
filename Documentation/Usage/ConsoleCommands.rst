..  include:: /Includes.rst.txt

..  _console-commands:

================
Console commands
================

The extension provides the following console commands:

..  _warming-cachewarmup:

`warming:cachewarmup`
=====================

A command to trigger cache warmup of single pages and/or whole sites
using their XML sitemaps.

..  important::

    You must pass at least the `--pages` or `--sites` command option.

..  tip::

    If you use `warming:cachewarmup` in your deployment process and the sitemap is protected with HTTP authentication
    (basic auth) or the website does not have a valid SSL certificate, you can configure this in the extension
    configuration with :ref:`verboseCrawlerOptions <extconf-verboseCrawlerOptions>` and
    :ref:`parserOptions <extconf-parserOptions>`.

..  tabs::

    ..  group-tab:: Composer-based installation

        ..  code-block:: bash

            vendor/bin/typo3 warming:cachewarmup

    ..  group-tab:: Legacy installation

        ..  code-block:: bash

            typo3/sysext/core/bin/typo3 warming:cachewarmup

The following command options are available:

..  confval:: -p|--pages
    :Required: false
    :type: integer
    :Default: none
    :Multiple allowed: true

    Use this option to provide IDs of pages whose Frontend caches
    should be warmed up. You can pass this option multiple times,
    either as single integer values or as a comma-separated list of
    integer values.

    Example:

    ..  tabs::

        ..  group-tab:: Composer-based installation

            ..  code-block:: bash

                vendor/bin/typo3 warming:cachewarmup -p 1 -p 2 -p 3
                vendor/bin/typo3 warming:cachewarmup -p 1,2,3

        ..  group-tab:: Legacy installation

            ..  code-block:: bash

                typo3/sysext/core/bin/typo3 warming:cachewarmup -p 1 -p 2 -p 3
                typo3/sysext/core/bin/typo3 warming:cachewarmup -p 1,2,3

..  confval:: -s|--sites
    :Required: false
    :type: integer or string (site identifier or `all`)
    :Default: none
    :Multiple allowed: true

    Use this option to provide a list of sites to be warmed up. You
    can either pass the appropriate site identifiers or the site's
    root page IDs.

    ..  versionadded:: 3.1.0

        `Feature: #720 - Allow warming up all available sites at once <https://github.com/eliashaeussler/typo3-warming/pull/720>`__

        You can also pass the special keyword `all` to warm up all
        available sites at once.

    Example:

    ..  tabs::

        ..  group-tab:: Composer-based installation

            ..  code-block:: bash

                vendor/bin/typo3 warming:cachewarmup -s my-cool-site
                vendor/bin/typo3 warming:cachewarmup -s 1
                vendor/bin/typo3 warming:cachewarmup -s all

        ..  group-tab:: Legacy installation

            ..  code-block:: bash

                typo3/sysext/core/bin/typo3 warming:cachewarmup -s my-cool-site
                typo3/sysext/core/bin/typo3 warming:cachewarmup -s 1
                typo3/sysext/core/bin/typo3 warming:cachewarmup -s all

..  confval:: -l|--languages
    :Required: false
    :type: integer
    :Default: :php:`-1` (all languages)
    :Multiple allowed: true

    You can optionally limit the languages of sites to be warmed up
    to a given list of language IDs. If this option is omitted, all
    site languages will be included in the cache warmup.

    Example:

    ..  tabs::

        ..  group-tab:: Composer-based installation

            ..  code-block:: bash

                vendor/bin/typo3 warming:cachewarmup -l 0 -l 1
                vendor/bin/typo3 warming:cachewarmup -l 0,1

        ..  group-tab:: Legacy installation

            ..  code-block:: bash

                typo3/sysext/core/bin/typo3 warming:cachewarmup -l 0 -l 1
                typo3/sysext/core/bin/typo3 warming:cachewarmup -l 0,1

..  confval:: -c|--config
    :Required: false
    :type: string
    :Default: none
    :Multiple allowed: false

    An external configuration file can be used to provide a more
    fine-grained configuration for cache warmup. The file path may
    contain references to extensions (see example below).

    ..  note::
        Config file options will be merged with command options,
        whereas command options receive higher priority. It's even
        possible to use environment variables for configuration. Read
        more in the `official documentation <https://cache-warmup.dev/configuration.html>`__.

    At the moment, the following file formats are supported:

    -   JSON
    -   PHP
    -   YAML

    Example:

    ..  tabs::

        ..  group-tab:: Composer-based installation

            ..  code-block:: bash

                vendor/bin/typo3 warming:cachewarmup --config cache-warmup.yaml
                vendor/bin/typo3 warming:cachewarmup --config EXT:sitepackage/Configuration/cache-warmup.yaml

        ..  group-tab:: Legacy installation

            ..  code-block:: bash

                typo3/sysext/core/bin/typo3 warming:cachewarmup --config cache-warmup.yaml
                typo3/sysext/core/bin/typo3 warming:cachewarmup --config EXT:sitepackage/Configuration/cache-warmup.yaml

..  confval:: --limit
    :name: command-limit
    :Required: false
    :type: integer
    :Default: :typoscript:`limit` value from :ref:`extension configuration <extension-configuration>`
    :Multiple allowed: false

    Maximum number of pages to be crawled. Set to :php:`0` to disable
    the limit. If this option is omitted, the :typoscript:`limit` value
    from :ref:`extension configuration <extension-configuration>` is
    used.

    Example:

    ..  tabs::

        ..  group-tab:: Composer-based installation

            ..  code-block:: bash

                vendor/bin/typo3 warming:cachewarmup --limit 100

        ..  group-tab:: Legacy installation

            ..  code-block:: bash

                typo3/sysext/core/bin/typo3 warming:cachewarmup --limit 100

..  confval:: --strategy
    :name: command-strategy
    :Required: false
    :type: string
    :Default: :typoscript:`strategy` value from :ref:`extension configuration <extension-configuration>`
    :Multiple allowed: false

    Name of an available crawling strategy to use for cache warmup. If
    this option is omitted, the :typoscript:`strategy` value from
    :ref:`extension configuration <extension-configuration>` is used.

    Example:

    ..  tabs::

        ..  group-tab:: Composer-based installation

            ..  code-block:: bash

                vendor/bin/typo3 warming:cachewarmup --strategy sort-by-priority

        ..  group-tab:: Legacy installation

            ..  code-block:: bash

                typo3/sysext/core/bin/typo3 warming:cachewarmup --strategy sort-by-priority

..  confval:: -x|--strict
    :Required: false
    :type: boolean
    :Default: false
    :Multiple allowed: false

    Exit with a non-zero status code in case cache warmup fails or
    errors occur during cache warmup.

    Example:

    ..  tabs::

        ..  group-tab:: Composer-based installation

            ..  code-block:: bash

                vendor/bin/typo3 warming:cachewarmup --strict

        ..  group-tab:: Legacy installation

            ..  code-block:: bash

                typo3/sysext/core/bin/typo3 warming:cachewarmup --strict

..  _warming-showuseragent:

`warming:showuseragent`
=======================

A command that shows the custom `User-Agent` header that is used for
cache warmup requests by default crawlers.

..  note::

    This command is not :ref:`schedulable <t3coreapi:schedulable>`.

..  tabs::

    ..  group-tab:: Composer-based installation

        ..  code-block:: bash

            vendor/bin/typo3 warming:showuseragent

    ..  group-tab:: Legacy installation

        ..  code-block:: bash

            typo3/sysext/core/bin/typo3 warming:showuseragent
