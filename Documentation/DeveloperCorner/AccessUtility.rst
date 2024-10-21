..  include:: /Includes.rst.txt

..  _access-utility:

===============
`AccessUtility`
===============

The :php:`AccessUtility` can be used to determine
:ref:`permissions <permissions>` of the current user to warm up
caches of specific sites or pages.

..  php:namespace:: EliasHaeussler\Typo3Warming\Utility

..  php:class:: AccessUtility

    Utility class to determine cache warmup permissions.

    ..  php:method:: canWarmupCacheOfPage($pageId, $languageId = null)

        Check if the current user can warm up caches of the given page.

        :param int $pageId: ID of the page to be checked.
        :param int $languageId: Optional language ID to be included in the check.
        :returntype: :php:`bool`

    ..  php:method:: canWarmupCacheOfSite($site, $languageId = null)

        Check if the current user can warm up caches of the given site.

        :param \TYPO3\CMS\Core\Site\Entity\Site $site: The site to be checked.
        :param int $languageId: Optional language ID to be included in the check.
        :returntype: :php:`bool`

..  seealso::

    View the sources on GitHub:

    -   `AccessUtility <https://github.com/eliashaeussler/typo3-warming/blob/main/Classes/Utility/AccessUtility.php>`__
