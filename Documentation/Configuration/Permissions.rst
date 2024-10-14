..  include:: /Includes.rst.txt

..  _permissions:

===========
Permissions
===========

..  note::
    Cache warmup can also be enabled or disabled globally. Check
    out the extension configuration for :ref:`page tree <extconf-enablePageTree>`
    and :ref:`backend toolbar <extconf-enableToolbar>`.

Administrators are able to run cache warmup for all available
sites and pages. All other users are by default not allowed to
run those tasks. Thus, they cannot see both the cache warmup
toolbar item and context menu items.

However, you can use User TSconfig to allow cache warmup for
specific users/usergroups and sites/pages. Add the following
configuration to the :typoscript:`options.cacheWarmup` User TSconfig:

..  confval:: allowedSites
    :Path: :typoscript:`options.cacheWarmup.allowedSites`
    :type: string (comma-separated list)

    Provide a comma-separated list of site identifiers. Those
    sites can then be warmed by the backend user.

    Example:

    ..  code-block:: typoscript

        options.cacheWarmup.allowedSites = my-dummy-site,another-dummy-site

..  confval:: allowedPages
    :Path: :typoscript:`options.cacheWarmup.allowedPages`
    :type: string (comma-separated list)

    Provide a comma-separated list of pages. Those pages can
    then be warmed by the backend user. Pages can be suffixed
    by a `+` sign to recursively include all subpages.

    Example:

    ..  code-block:: typoscript

        options.cacheWarmup.allowedPages = 1,2,3+

..  seealso::

    You might also want to check out :ref:`access-utility` for the
    actual implementation of the permission check.
