..  include:: /Includes.rst.txt

..  _sitemap-providers:

=================
Sitemap providers
=================

To localize the sitemap of a site, so-called "sitemap providers"
are available. These are executed one after the other (depending on
priority) to localize XML sitemaps according to certain criteria.

..  php:namespace:: EliasHaeussler\Typo3Warming\Sitemap\Provider

..  php:interface:: Provider

    Interface for sitemap providers used to locate the path to an
    XML sitemap of a given site.

    ..  php:method:: get($site, $siteLanguage = null)

        Locate the XML sitemap path of the given site.

        :param TYPO3\\CMS\\Core\\Site\\Entity\\Site $site: The site whose XML sitemap path should be located.
        :param TYPO3\\CMS\\Core\\Site\\Entity\\SiteLanguage $siteLanguage: An optional site language to include while locating the XML sitemap path.
        :returns: An instance of :php:class:`EliasHaeussler\\Typo3Warming\\Sitemap\\SiteAwareSitemap` or :php:`null`.

    ..  php:staticmethod:: getPriority()

        Get the provider's priority. The higher the returned value,
        the earlier the provider will be executed in the sitemap
        location process.

        :returntype: int

..  _default-providers:

Default providers
=================

By default, the path to an XML sitemap is determined in three steps:

..  rst-class:: bignums

1.  Page type

    When using the :ref:`XML sitemap <t3coreapi:xmlsitemap>` from TYPO3's
    SEO extension, the configured path to the XML sitemap can be determined
    by the appropriate page type.

2.  Site configuration

    Within the Sites module, one can explicitly define the path to
    the XML sitemap of a site.

    ..  image:: ../Images/site-configuration.png
        :alt: Configuration of XML sitemap path within the Sites module

3.  `robots.txt`

    If no path is defined in the site configuration, a possible
    `robots.txt` file is parsed for a valid `Sitemap` specification.
    Read more at `sitemaps.org <https://www.sitemaps.org/protocol.html#submit_robots>`__.

    ..  note::
        Only the first occurrence will be respected. In the following
        example, the resulting sitemap is
        `https://www.example.com/our-sitemap.xml`.

        ..  code-block:: none
            :emphasize-lines: 3

            User-agent: *
            Disallow: /tmp/
            Sitemap: https://www.example.com/our-sitemap.xml
            Sitemap: https://www.example.com/our-other-sitemap.xml

4.  Default path

    If none of the above methods are successful, the default path
    `sitemap.xml` is used.

..  _implement-a-custom-provider:

Implement a custom provider
===========================

To develop your own sitemap provider, it is only necessary to
implement the :php:interface:`EliasHaeussler\\Typo3Warming\\Sitemap\\Provider\\Provider`
interface. In addition, the :php:`getPriority()` method must be
used to define when the provider is executed.

The order of the providers provided by default is as follows:

+---------------------------------------------------------------------------------+---------------------+
| Sitemap provider                                                                | Priority            |
+=================================================================================+=====================+
| :php:class:`EliasHaeussler\\Typo3Warming\\Sitemap\\Provider\\PageTypeProvider`  | 300                 |
+---------------------------------------------------------------------------------+---------------------+
| :php:class:`EliasHaeussler\\Typo3Warming\\Sitemap\\Provider\\SiteProvider`      | 200                 |
+---------------------------------------------------------------------------------+---------------------+
| :php:class:`EliasHaeussler\\Typo3Warming\\Sitemap\\Provider\\RobotsTxtProvider` | 100                 |
+---------------------------------------------------------------------------------+---------------------+
| :php:class:`EliasHaeussler\\Typo3Warming\\Sitemap\\Provider\\DefaultProvider`   | :php:`PHP_INT_MIN`  |
+---------------------------------------------------------------------------------+---------------------+

Once your custom provider is ready, make sure to clear the DI
caches in order to rebuild the service container properly.

..  seealso::
    View the sources on GitHub:

    -   `Provider <https://github.com/eliashaeussler/typo3-warming/blob/main/Classes/Sitemap/Provider/Provider.php>`__
    -   `PageTypeProvider <https://github.com/eliashaeussler/typo3-warming/blob/main/Classes/Sitemap/Provider/PageTypeProvider.php>`__
    -   `SiteProvider <https://github.com/eliashaeussler/typo3-warming/blob/main/Classes/Sitemap/Provider/SiteProvider.php>`__
    -   `RobotsTxtProvider <https://github.com/eliashaeussler/typo3-warming/blob/main/Classes/Sitemap/Provider/RobotsTxtProvider.php>`__
    -   `DefaultProvider <https://github.com/eliashaeussler/typo3-warming/blob/main/Classes/Sitemap/Provider/DefaultProvider.php>`__
