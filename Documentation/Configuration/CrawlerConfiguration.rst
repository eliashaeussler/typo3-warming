..  include:: /Includes.rst.txt

..  _crawler-configuration:

=====================
Crawler configuration
=====================

The following configuration applies only to the :ref:`default crawlers <default-crawlers>`
shipped by this extension.

..  seealso::

    If you want to learn more about configurable crawlers, read more in the :ref:`developer corner <crawlers>`.

..  note::

    Both default crawlers utilize the same crawler options that are described in the
    `option reference <https://cache-warmup.dev/config-reference/crawler-options.html#option-reference>`__
    of the underlying `eliashaeussler/cache-warmup` library.

In addition, the following crawler options are available:

..  confval:: perform_subrequests
    :type: boolean
    :Default: 0

    ..  versionadded:: 3.3.0

        `Feature: #730 - Introduce sub request handler <https://github.com/eliashaeussler/typo3-warming/pull/730>`__

    Enable sub request handler for cache warmup requests. This will significantly increase
    performance of the whole cache warmup progress, since no HTTP requests will be performed
    anymore. Instead, the bootstrapped frontend application is re-used for each cache warmup
    request.

    ..  note::

        Only URLs matching the site's base URL are handled by the sub request handler. All
        other URLs (e.g. pages linking to external URLs) will be handled by Guzzle's default
        handler.
