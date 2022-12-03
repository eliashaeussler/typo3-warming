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
    :type: integer or string (site identifier)
    :Default: none
    :Multiple allowed: true

    Use this option to provide a list of sites to be warmed up. You
    can either pass the appropriate site identifiers or the site's
    root page IDs.

    Example:

    ..  tabs::

        ..  group-tab:: Composer-based installation

            ..  code-block:: bash

                vendor/bin/typo3 warming:cachewarmup -s my-cool-site
                vendor/bin/typo3 warming:cachewarmup -s 1

        ..  group-tab:: Legacy installation

            ..  code-block:: bash

                typo3/sysext/core/bin/typo3 warming:cachewarmup -s my-cool-site
                typo3/sysext/core/bin/typo3 warming:cachewarmup -s 1

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

..  confval:: --limit

    :Required: false
    :type: integer
    :Default: :typoscript:`limit` value from :ref:`extension configuration <extension-configuration>`
    :Multiple allowed: false

    Maximum number of pages to be crawled. Set to :php:`0` to disable
    the limit. If this option is omitted, the :typoscript:`limit` value
    from :ref:`extension configuration <extension-configuration>` is
    used.

..  confval:: -x|--strict

    :Required: false
    :type: boolean
    :Default: false
    :Multiple allowed: false

    Exit with a non-zero status code in case cache warmup fails or
    errors occur during cache warmup.

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
