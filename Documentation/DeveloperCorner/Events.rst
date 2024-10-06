..  include:: /Includes.rst.txt

..  _events:

======
Events
======

..  versionadded:: 1.0.0

    `Feature #338 – Introduce events <https://github.com/eliashaeussler/typo3-warming/issues/338>`__

During cache warmup, some :ref:`PSR-14 events <t3coreapi:EventDispatcher>`
are dispatched. Events can be used to step in the cache warmup
lifecycle and affect its crawling behavior in various ways.

..  note::
    The `eliashaeussler/cache-warmup` library also dispatches some events.
    Read more in the `official documentation <https://cache-warmup.dev/api/events.html>`__.

The following events are currently dispatched:

..  _before-cache-warmup-event:

BeforeCacheWarmupEvent
======================

This event is dispatched right before cache warmup is triggered
via :php:meth:`EliasHaeussler\\CacheWarmup\\CacheWarmer::run`. It
allows to add additional sitemaps and URLs to the cache warmer or
modify the crawling behavior in other ways.

..  _after-cache-warmup-event:

AfterCacheWarmupEvent
=====================

Once cache warmup is finished, this event is dispatched. It
provides the cache warmup result together with the original
instance of :php:class:`EliasHaeussler\\CacheWarmup\\CacheWarmer`
and the used crawler.

..  seealso::

    View the sources on GitHub:

    -   `AfterCacheWarmupEvent <https://github.com/eliashaeussler/typo3-warming/blob/main/Classes/Event/AfterCacheWarmupEvent.php>`__
    -   `BeforeCacheWarmupEvent <https://github.com/eliashaeussler/typo3-warming/blob/main/Classes/Event/BeforeCacheWarmupEvent.php>`__
