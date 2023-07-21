..  include:: /Includes.rst.txt

..  _caching:

=======
Caching
=======

Once a sitemap is located by a :ref:`sitemap provider <sitemap-providers>`,
the path to the XML sitemap is cached. This speeds up following
warmup requests. Caching happens with a custom `warming` cache
which defaults to a filesystem cache located at :file:`var/cache/code/warming/sitemaps.php`.

..  php:namespace:: EliasHaeussler\Typo3Warming\Cache

..  php:class:: SitemapsCache

    Read and write sitemap cache entries from custom `warming` cache.

    ..  php:method:: get($site, $siteLanguage = null)

        Get the located sitemaps of a given site.

        :param TYPO3\\CMS\\Core\\Site\\Entity\\Site $site: The sitemap's site object.
        :param TYPO3\\CMS\\Core\\Site\\Entity\\SiteLanguage $siteLanguage: An optional site language.
        :returns: Located sitemaps of a given site.

    ..  php:method:: set($sitemaps)

        Add the located sitemaps to the `warming` cache.

        :param array $sitemaps: The located sitemaps to be cached.

..  seealso::

    View the sources on GitHub:

    -   `SitemapsCache <https://github.com/eliashaeussler/typo3-warming/blob/main/Classes/Cache/SitemapsCache.php>`__
