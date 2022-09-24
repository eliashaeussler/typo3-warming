.. include:: /Includes.rst.txt

.. _introduction:

============
Introduction
============

.. _what-it-does:

What does it do?
================

The extension provides a service to warm up Frontend caches based on an XML
sitemap. Cache warmup can be triggered in various ways:

- Via the :ref:`TYPO3 backend <backend-toolbar>`
- Using a :ref:`console command <console-commands>`
- Directly with the :ref:`PHP API <using-the-api>`

It supports multiple languages and custom crawler implementations.
Under the hood, the extension makes use of the
`eliashaeussler/cache-warmup <https://github.com/eliashaeussler/cache-warmup>`__
library which provides the core cache warmup implementation.

.. _features:

Features
========

-   Frontend cache warmup of pages located in XML sitemaps
-   Integration in :ref:`TYPO3 backend toolbar <backend-toolbar>` and
    :ref:`page tree <page-tree>`
-   Support of various :ref:`sitemap providers <sitemap-providers>`
    (e.g. `robots.txt` or custom location)
-   Multi-language support of configured sites
-   Support for :ref:`custom crawlers <crawlers>`
-   :ref:`Console commands <console-commands>`
-   Compatible with TYPO3 10.4 LTS and 11.5 LTS

.. _support:

Support
=======

There are several ways to get support for this extension:

* Slack: https://typo3.slack.com/archives/C0400CSGWAY
* GitHub: https://github.com/eliashaeussler/typo3-warming/issues

.. _license:

License
=======

This extension is licensed under
`GNU General Public License 2.0 (or later) <https://www.gnu.org/licenses/old-licenses/gpl-2.0.html>`_.
