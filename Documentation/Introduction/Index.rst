..  include:: /Includes.rst.txt

..  _introduction:

============
Introduction
============

..  _what-it-does:

What does it do?
================

The extension provides a service to warm up Frontend caches based on XML
sitemaps. Cache warmup can be triggered in various ways:

-   From the :ref:`TYPO3 backend <backend-toolbar>`
-   Using a :ref:`console command <console-commands>`
-   Directly with the :ref:`PHP API <using-the-api>`

It supports multiple languages and custom crawler implementations.
Under the hood, the extension makes use of the
`eliashaeussler/cache-warmup <https://github.com/eliashaeussler/cache-warmup>`__
library which provides the core cache warmup implementation. In addition,
`EXT:sitemap_locator <https://extensions.typo3.org/extension/sitemap_locator>`__
is used to locate XML sitemaps.

..  _features:

Features
========

-   Frontend cache warmup of pages located in XML sitemaps
-   Integration in :ref:`TYPO3 backend toolbar <backend-toolbar>` and
    :ref:`page tree <page-tree>`
-   Support of various :ref:`sitemap providers <sitemap-providers>`
    (e.g. `robots.txt` or custom location)
-   Multi-language support of configured sites
-   Support for :ref:`custom crawlers <crawlers>` and
    :ref:`crawling strategies <crawling-strategies>`
-   :ref:`Console commands <console-commands>`
-   Compatible with TYPO3 12.4 LTS and 13.2 (see :ref:`version matrix <version-matrix>`)

..  _support:

Support
=======

There are several ways to get support for this extension:

* Slack: https://typo3.slack.com/archives/C0400CSGWAY
* GitHub: https://github.com/eliashaeussler/typo3-warming/issues

..  _license:

License
=======

This extension is licensed under
`GNU General Public License 2.0 (or later) <https://www.gnu.org/licenses/old-licenses/gpl-2.0.html>`_.
