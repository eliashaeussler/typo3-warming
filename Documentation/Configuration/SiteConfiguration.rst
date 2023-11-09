..  include:: /Includes.rst.txt

..  _site-configuration:

==================
Site configuration
==================

..  important::

    Caches can be warmed up only if the entry point of the site
    contains the full domain name.

Cache warmup is based on the configured sites in the TYPO3 installation. Therefore,
in order to control the cache warmup behavior, the site configuration can be used.

Take a look at the :ref:`site configuration of EXT:sitemap_locator <sitemap-locator:site-configuration>`
which provides the XML sitemap location feature.

..  seealso::

    Take a look at :ref:`sitemap-providers` to learn how the extension
    internally evaluates the site configuration values to determine the
    path to the XML sitemap of a site.
