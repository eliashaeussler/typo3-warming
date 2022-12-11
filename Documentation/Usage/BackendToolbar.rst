..  include:: /Includes.rst.txt

..  _backend-toolbar:

===============
Backend toolbar
===============

..  note::

    The toolbar item is only visible for admins and permitted users.
    Read how to give non-admin users access to the toolbar item at
    :ref:`permissions`.

As soon as the extension is installed, a new toolbar item in your TYPO3
backend should appear. You can click on the toolbar item to get a list
of all sites. If a site does not provide an XML sitemap, it cannot be
used to warm up caches.

..  image:: ../Images/toolbar-item.png
    :alt: Cache warmup toolbar item within the TYPO3 backend

..  tip::

    The toolbar item additionally outputs information about the
    `User-Agent` header used during the cache warmup. By clicking on
    :guilabel:`Copy to clipboard`, it can be copied to the clipboard,
    for example to exclude cache warmup requests from analyses in
    statistics tools. Take a look at the console command
    :ref:`warming-showuseragent` to learn more about the usage of the
    `User-Agent` header.
