..  include:: /Includes.rst.txt

..  _warmup-permission-guard:

=======================
`WarmupPermissionGuard`
=======================

The :php:`WarmupPermissionsGuard` can be used to determine
:ref:`permissions <permissions>` of a given backend user to warm up caches
of specific sites or pages.

..  note::
    Resolved permissions for a given combination of page/site, language id and
    backend user are cached during runtime.

..  php:namespace:: EliasHaeussler\Typo3Warming\Security

..  php:class:: WarmupPermissionGuard

    Guard to determine cache warmup permissions.

    ..  php:method:: canWarmupCacheOfPage($pageId, $context)

        Check if caches of the given page can be warmed up while respecting given
        permission context.

        :param int $pageId: ID of the page to be checked.
        :param \EliasHaeussler\Typo3Warming\Security\Context\PermissionContext $context: Permission context which holds language ID and backend user.
        :returntype: :php:`bool`

    ..  php:method:: canWarmupCacheOfSite($site, $context)

        Check if caches of the given site can be warmed up while respecting given
        permission context.

        :param \TYPO3\CMS\Core\Site\Entity\Site $site: The site to be checked.
        :param \EliasHaeussler\Typo3Warming\Security\Context\PermissionContext $context: Permission context which holds language ID and backend user.
        :returntype: :php:`bool`

..  seealso::

    View the sources on GitHub:

    -   `WarmupPermissionGuard <https://github.com/eliashaeussler/typo3-warming/blob/main/Classes/Security/WarmupPermissionGuard.php>`__
    -   `PermissionContext <https://github.com/eliashaeussler/typo3-warming/blob/main/Classes/Security/Context/PermissionContext.php>`__
