..  include:: /Includes.rst.txt

..  _migration:

=========
Migration
=========

This page lists all notable changes and required migrations when
upgrading to a new major version of this extension.

..  seealso::

    Make sure to check out the `migration guide <https://cache-warmup.dev/migration.html>`__
    of the :composer:`eliashaeussler/cache-warmup` library as well.

..  _version-4.2.0:

Version 4.2.0
=============

Use of properties in :ref:`Configuration API <configuration-api>`
-----------------------------------------------------------------

-   Several class methods in :php:`\EliasHaeussler\Typo3Warming\Configuration\Configuration`
    are converted to class properties.
-   Make sure to access the appropriate properties directly instead of calling the
    class methods.
-   Calling the legacy class methods is deprecated and won't be possible anymore with version
    5.0 of the extension.

Deprecation of :php:`getUserAgent()` method in :ref:`Configuration API <configuration-api>`
-------------------------------------------------------------------------------------------

-   Access to the generated `User-Agent` header is moved from the :ref:`Configuration API <configuration-api>`
    to a dedicated :php:`\EliasHaeussler\Typo3Warming\Http\Message\Request\RequestOptions` class.
-   Migrate calls from :php:`\EliasHaeussler\Typo3Warming\Configuration\Configuration::getUserAgent`
    to :php:`\EliasHaeussler\Typo3Warming\Http\Message\Request\RequestOptions::getUserAgent`.
-   Calling the legacy method is deprecated and won't be possible anymore with version 5.0
    of the extension.

..  _version-4.0.0:

Version 4.0.0
=============

Upgrade of :composer:`eliashaeussler/cache-warmup` library
----------------------------------------------------------

-   Crawler option `client_config` is removed and must be migrated to
    extension configuration :ref:`clientOptions <confval-clientoptions>`.
-   Extension configuration `parserClientOptions` is renamed to
    :ref:`parserOptions <confval-parseroptions>`. Existing configuration must
    be migrated to extension configuration :ref:`clientOptions <confval-clientoptions>`.
-   Crawling response body is no longer attached to response objects. Enable
    crawler option `write_response_body` to restore previous behavior.
-   Read more in the library's `release notes <https://github.com/eliashaeussler/cache-warmup/releases/tag/4.0.0>`__.

..  _version-3.0.0:

Version 3.0.0
=============

Upgrade of :composer:`eliashaeussler/cache-warmup` library
----------------------------------------------------------

-   Custom crawlers must be rewritten to match the updated codebase.
-   Read more in the library's `release notes <https://github.com/eliashaeussler/cache-warmup/releases/tag/3.0.0>`__.

:php:`StreamResponseHandler` is now result-aware
------------------------------------------------

-   :php:`\EliasHaeussler\Typo3Warming\Http\Message\Handler\StreamResponseHandler`
    now depends on a given
    :php:`\EliasHaeussler\CacheWarmup\Result\CacheWarmupResult`.
-   The result object is generated and updated by the
    :php:`\EliasHaeussler\CacheWarmup\Http\Message\Handler\ResultCollectorHandler`.
-   Make sure to use both handlers together when using the stream response handler.
-   Pass the result object from result collection handler when instantiating the
    stream response handler.
-   See :php:`\EliasHaeussler\Typo3Warming\Crawler\ConcurrentUserAgentCrawler`
    for a dedicated example.

..  _version-2.0.0:

Version 2.0.0
=============

Integration of :t3ext:`sitemap_locator`
---------------------------------------

-   Sitemaps cache was extracted to :t3ext:`sitemap_locator`. Use
    :php:`\EliasHaeussler\Typo3SitemapLocator\Cache\SitemapsCache`
    instead of :php:`\EliasHaeussler\Typo3Warming\Cache\SitemapsCache`.
-   Sitemap providers were extracted to :t3ext:`sitemap_locator`. Use
    :php:`\EliasHaeussler\Typo3SitemapLocator\Sitemap\Provider\Provider`
    instead of :php:`\EliasHaeussler\Typo3Warming\Sitemap\Provider\Provider`
    for custom provider implementations.
-   Sitemap locator was extracted to :t3ext:`sitemap_locator`. Use
    :php:`\EliasHaeussler\Typo3SitemapLocator\Sitemap\SitemapLocator`
    instead of :php:`\EliasHaeussler\Typo3Warming\Sitemap\SitemapLocator`.

Relocated sitemap model
-----------------------

-   The default :php:`\EliasHaeussler\Typo3Warming\Sitemap\SiteAwareSitemap`
    model was moved to :php:`\EliasHaeussler\Typo3Warming\Domain\Model\SiteAwareSitemap`.
    Update references to this class in your code.

..  _version-1.0.0:

Version 1.0.0
=============

Default crawlers
----------------

-   Default crawlers are now :php:`final`. Custom crawlers can no
    longer extend default crawlers. Implement the
    :php:interface:`\EliasHaeussler\CacheWarmup\Crawler\Crawler`
    or :php:interface:`\EliasHaeussler\CacheWarmup\Crawler\VerboseCrawler`
    interface instead.
-   :php:`CrawlerFactory` from :composer:`eliashaeussler/cache-warmup` library
    is now used to instantiate crawlers. Dependency injection is no
    longer possible.
-   :php:`\EliasHaeussler\Typo3Warming\Crawler\ConfigurableClientTrait`
    was removed. Use
    :php:`\EliasHaeussler\Typo3Warming\Http\Client\ClientFactory::get`
    instead.
-   :php:`\EliasHaeussler\Typo3Warming\Crawler\RequestAwareInterface`
    and :php:`\EliasHaeussler\Typo3Warming\Crawler\RequestAwareTrait`
    were removed. Use
    :php:interface:`\EliasHaeussler\Typo3Warming\Crawler\StreamableCrawler`
    in combination with
    :php:`\EliasHaeussler\Typo3Warming\Http\Message\Handler\StreamResponseHandler`
    instead.
-   :php:`\EliasHaeussler\Typo3Warming\Crawler\UserAgentTrait`
    was removed. Provide an own implementation that calls
    :php:meth:`\EliasHaeussler\Typo3Warming\Configuration\Configuration::getUserAgent`
    instead.

Warmup request handling
-----------------------

-   :php:`\EliasHaeussler\Typo3Warming\ValueObject\Request\WarmupRequest`
    is now :php:`final`.
-   :php:`\EliasHaeussler\Typo3Warming\ValueObject\Request\WarmupRequest::$updateCallback`
    was removed. Streamed warmup requests must now be handled by using
    :php:`\EliasHaeussler\Typo3Warming\Http\Message\Handler\StreamResponseHandler`
    in a custom crawler instead.
-   Crawling result handling within
    :php:`\EliasHaeussler\Typo3Warming\ValueObject\Request\WarmupRequest`
    was removed. Use the returned
    :php:`\EliasHaeussler\Typo3Warming\Result\CacheWarmupResult`
    from :php:meth:`\EliasHaeussler\Typo3Warming\Service\CacheWarmupService::warmup`
    instead.
-   :php:`\EliasHaeussler\Typo3Warming\Service\CacheWarmupService::warmupPages`
    and :php:`\EliasHaeussler\Typo3Warming\Service\CacheWarmupService::warmupSites`
    were combined to a new method
    :php:meth:`\EliasHaeussler\Typo3Warming\Service\CacheWarmupService::warmup`.
    Use this method with dedicated instances of
    :php:`\EliasHaeussler\Typo3Warming\ValueObject\Request\SiteWarmupRequest` and
    :php:`\EliasHaeussler\Typo3Warming\ValueObject\Request\PageWarmupRequest`.

Sitemap providers
-----------------

-   :php:`\EliasHaeussler\Typo3Warming\Sitemap\Provider\ProviderInterface`
    was renamed to
    :php:`\EliasHaeussler\Typo3Warming\Sitemap\Provider\Provider`.
-   :php:`\EliasHaeussler\Typo3Warming\Sitemap\Provider\AbstractProvider`
    was removed. Custom sitemap providers must now implement
    :php:`\EliasHaeussler\Typo3Warming\Sitemap\Provider\Provider`
    directly. The previously available trait method is now available within
    :php:`\EliasHaeussler\Typo3Warming\Utility\HttpUtility::getSiteUrlWithPath`.
-   :php:`\EliasHaeussler\Typo3Warming\Sitemap\Provider\Provider::get`
    now returns an array of :php:`\EliasHaeussler\Typo3Warming\Sitemap\SiteAwareSitemap`
    instances.
-   A new sitemap provider
    :php:`\EliasHaeussler\Typo3Warming\Sitemap\Provider\PageTypeProvider`
    was added. It is configured with highest priority. Read more at
    :ref:`sitemap-providers`.

Language handling
-----------------

-   :php:`\EliasHaeussler\Typo3Warming\Sitemap\SiteAwareSitemap`
    now requires a site language to be set.
-   Page uri generation now respects configured language
    overlays and is moved to
    :php:`\EliasHaeussler\Typo3Warming\Utility\HttpUtility::generateUri`.

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
