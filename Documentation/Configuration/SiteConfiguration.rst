..  include:: /Includes.rst.txt

..  _site-configuration:

==================
Site configuration
==================

..  important::

    Caches can be warmed up only if the entry point of the site
    contains the **full domain name**.

Cache warmup is based on the configured sites in the TYPO3 installation. Therefore,
in order to control the cache warmup behavior, the site configuration can be used.

Take a look at the :ref:`site configuration of EXT:sitemap_locator <sitemap-locator:site-configuration>`
which provides the XML sitemap location feature.

In addition, the following site configuration options are available:

..  confval:: warming_exclude (site)
    :Path: warming_exclude
    :type: boolean
    :Default: 0

    ..  versionadded:: 4.0.0

        `Feature: #793 - Allow to exclude sites and languages from warming <https://github.com/eliashaeussler/typo3-warming/pull/793>`__

    Enable or disable cache warmup for this site. If this is set to `1`,
    the **whole site will be excluded** from cache warmup. By default,
    all sites are included.

    ..  image:: ../Images/site-configuration-exclude.png
        :alt: Configuration of exclude checkbox within the Sites module

..  confval:: warming_exclude (site_language)
    :Path: languages > (site language) > warming_exclude
    :type: boolean
    :Default: 0

    ..  versionadded:: 4.0.0

        `Feature: #793 - Allow to exclude sites and languages from warming <https://github.com/eliashaeussler/typo3-warming/pull/793>`__

    Enable or disable cache warmup for this site language. If set to `1`,
    only pages of **this specific site language** will be excluded from
    cache warmup.

..  seealso::

    Take a look at :ref:`sitemap-providers` to learn how the extension
    internally evaluates the site configuration values to determine the
    path to the XML sitemap of a site.
