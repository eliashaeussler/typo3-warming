.. include:: /Includes.rst.txt

.. _caching:

=======
Caching
=======

Once a sitemap is located by a :ref:`sitemap provider <sitemap-providers>`,
the path to the XML sitemap is cached. This speeds up following
warmup requests. Caching happens with the `core` cache which defaults
to a filesystem cached located at :file:`var/cache/code/core/tx_warming.php`.

..  php:namespace:: EliasHaeussler\Typo3Warming\Cache

..  php:class:: CacheManager

    Manager to read and write the core cache `tx_warming`.

    ..  php:method:: get($site = null, $siteLanguage = null)

        Get all located sitemaps or the located sitemap of a given site
        and/or site language.

        :param TYPO3\\CMS\\Core\\Site\\Entity\\Site $site: The sitemap's site object or :php:`NULL` to lookup all sitemaps.
        :param TYPO3\\CMS\\Core\\Site\\Entity\\SiteLanguage $siteLanguage: An optional site language
        :returns: Either an array of all located sitemaps or the located sitemap of a given site.

    ..  php:method:: set($sitemap)

        Add the located sitemap to the `tx_warming` cache.

        :param EliasHaeussler\\Typo3Warming\\Sitemap\\SiteAwareSitemap $sitemap: The located sitemap to be cached.

.. seealso::

    View the sources on GitHub:

    - `CacheManager <https://github.com/eliashaeussler/typo3-warming/blob/main/Classes/Cache/CacheManager.php>`__
