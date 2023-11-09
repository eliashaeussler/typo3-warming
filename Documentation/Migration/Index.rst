..  include:: /Includes.rst.txt

..  _migration:

=========
Migration
=========

This page lists all notable changes and required migrations when
upgrading to a new major version of this extension.

..  _version-2.0.0:

Version 2.0.0
=============

Integration of EXT:sitemap_locator
----------------------------------

-   Sitemaps cache was extracted to EXT:sitemap_locator. Use
    :php:class:`EliasHaeussler\\Typo3SitemapLocator\\Cache\\SitemapsCache`
    instead of :php:class:`EliasHaeussler\\Typo3Warming\\Cache\\SitemapsCache`.
-   Sitemap providers were extracted to EXT:sitemap_locator. Use
    :php:interface:`EliasHaeussler\\Typo3SitemapLocator\\Sitemap\\Provider\\Provider`
    instead of :php:interface:`EliasHaeussler\\Typo3Warming\\Sitemap\\Provider\\Provider`
    for custom provider implementations.
-   Sitemap locator was extracted to EXT:sitemap_locator. Use
    :php:class:`EliasHaeussler\\Typo3SitemapLocator\\Sitemap\\SitemapLocator`
    instead of :php:class:`EliasHaeussler\\Typo3Warming\\Sitemap\\SitemapLocator`.

Relocated sitemap model
-----------------------

-   The default :php:class:`EliasHaeussler\\Typo3Warming\\Sitemap\\SiteAwareSitemap`
    model was moved to :php:class:`EliasHaeussler\\Typo3Warming\\Domain\\Model\\SiteAwareSitemap`.
    Update references to this class in your code.

..  _version-1.0.0:

Version 1.0.0
=============

Default crawlers
----------------

-   Default crawlers are now :php:`final`. Custom crawlers can no
    longer extend default crawlers. Implement
    :php:interface:`EliasHaeussler\\CacheWarmup\\Crawler\\CrawlerInterface`
    or :php:interface:`EliasHaeussler\\CacheWarmup\\Crawler\\VerboseCrawlerInterface`
    instead.
-   :php:`CrawlerFactory` from `eliashaeussler/cache-warmup` library
    is now used to instantiate crawlers. Dependency injection is no
    longer possible.
-   :php:trait:`EliasHaeussler\\Typo3Warming\\Crawler\\ConfigurableClientTrait`
    was removed. Use
    :php:meth:`EliasHaeussler\\Typo3Warming\\Http\\Client\\ClientFactory::get`
    instead.
-   :php:interface:`EliasHaeussler\\Typo3Warming\\Crawler\\RequestAwareInterface`
    and :php:trait:`EliasHaeussler\\Typo3Warming\\Crawler\\RequestAwareTrait`
    were removed. Use
    :php:interface:`EliasHaeussler\\Typo3Warming\\Crawler\\StreamableCrawler`
    in combination with
    :php:class:`EliasHaeussler\\Typo3Warming\\Http\\Message\\Handler\\StreamResponseHandler`
    instead.
-   :php:trait:`EliasHaeussler\\Typo3Warming\\Crawler\\UserAgentTrait`
    was removed. Provide an own implementation that calls
    :php:meth:`EliasHaeussler\\Typo3Warming\\Configuration\\Configuration::getUserAgent`
    instead.

Warmup request handling
-----------------------

-   :php:class:`EliasHaeussler\\Typo3Warming\\ValueObject\\Request\\WarmupRequest`
    is now :php:`final`.
-   :php:attr:`EliasHaeussler\\Typo3Warming\\ValueObject\\Request\\WarmupRequest::$updateCallback`
    was removed. Streamed warmup requests must now be handled by using
    :php:class:`EliasHaeussler\\Typo3Warming\\Http\\Message\\Handler\\StreamResponseHandler`
    in a custom crawler instead.
-   Crawling result handling within
    :php:class:`EliasHaeussler\\Typo3Warming\\ValueObject\\Request\\WarmupRequest`
    was removed. Use the returned
    :php:class:`EliasHaeussler\\Typo3Warming\\Result\\CacheWarmupResult`
    from :php:meth:`EliasHaeussler\\Typo3Warming\\Service\\CacheWarmupService::warmup`
    instead.
-   :php:meth:`EliasHaeussler\\Typo3Warming\\Service\\CacheWarmupService::warmupPages`
    and :php:meth:`EliasHaeussler\\Typo3Warming\\Service\\CacheWarmupService::warmupSites`
    were combined to a new method
    :php:meth:`EliasHaeussler\\Typo3Warming\\Service\\CacheWarmupService::warmup`.
    Use this method with dedicated instances of
    :php:class:`EliasHaeussler\\Typo3Warming\\ValueObject\\Request\\SiteWarmupRequest` and
    :php:class:`EliasHaeussler\\Typo3Warming\\ValueObject\\Request\\PageWarmupRequest`.

Sitemap providers
-----------------

-   :php:interface:`EliasHaeussler\\Typo3Warming\\Sitemap\\Provider\\ProviderInterface`
    was renamed to
    :php:interface:`EliasHaeussler\\Typo3Warming\\Sitemap\\Provider\\Provider`.
-   :php:trait:`EliasHaeussler\\Typo3Warming\\Sitemap\\Provider\\AbstractProvider`
    was removed. Custom sitemap providers must now implement
    :php:interface:`EliasHaeussler\\Typo3Warming\\Sitemap\\Provider\\Provider`
    directly. The previously available trait method is now available within
    :php:meth:`EliasHaeussler\\Typo3Warming\\Utility\\HttpUtility::getSiteUrlWithPath`.
-   :php:meth:`EliasHaeussler\\Typo3Warming\\Sitemap\\Provider\\Provider::get`
    now returns an array of :php:class:`EliasHaeussler\\Typo3Warming\\Sitemap\\SiteAwareSitemap`
    instances.
-   A new sitemap provider
    :php:class:`EliasHaeussler\\Typo3Warming\\Sitemap\\Provider\\PageTypeProvider`
    was added. It is configured with highest priority. Read more at
    :ref:`sitemap-providers`.

Language handling
-----------------

-   :php:class:`EliasHaeussler\\Typo3Warming\\Sitemap\\SiteAwareSitemap`
    now requires a site language to be set.
-   Page uri generation now respects configured language
    overlays and is moved to
    :php:meth:`EliasHaeussler\\Typo3Warming\\Utility\\HttpUtility::generateUri`.

Extension configuration
-----------------------

-   Extension configuration `exclude` was added. Read more at
    :ref:`exclude <extconf-exclude>`.
-   Extension configuration `strategy` was added. Read more at
    :ref:`strategy <extconf-strategy>`.

Command options
---------------

-   New command option `--format` was added. Read more at
    :ref:`warming-cachewarmup`.
-   New command option `--strategy` was added. Read more at
    :ref:`warming-cachewarmup`.

Template paths
--------------

-   Template paths were rewritten:

    +   :file:`CacheWarmupToolbarItem.html` was rewritten
        to :file:`Toolbar/CacheWarmupToolbarItem.html`
    +   :file:`CacheWarmupToolbarItemActions.html` was rewritten
        to :file:`Modal/SitesModal.html`

-   Partial paths were rewritten:

    +   :file:`ToolbarItem.html` was inlined to template
        :file:`Toolbar/CacheWarmupToolbarItem.html`
    +   :file:`ToolbarItemAction.html` was split into
        :file:`Modal/Sites/SiteGroup.html` and :file:`Modal/Sites/SiteGroupItem.html`
    +   :file:`ToolbarItemMissing.html` was rewritten to
        :file:`Modal/Alert/NoSites.html`
    +   :file:`ToolbarItemPlaceholder.html` was removed
    +   :file:`ToolbarItemUserAgent.html` was removed
