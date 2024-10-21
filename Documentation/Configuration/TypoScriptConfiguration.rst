..  include:: /Includes.rst.txt

..  _typoscript-configuration:

========================
TypoScript configuration
========================

The following global TypoScript configuration is available:

..  _typoscript-constants:

Constants
=========

..  _typoscript-constants-view.templateRootPath:

..  confval:: view.templateRootPath
    :type: string
    :Path: :typoscript:`module.tx_warming`

    Additional path to template root used in Backend context. Within this
    path, Fluid templates can be overwritten.

    ..  seealso::

        Read more in the :ref:`official documentation <t3tsref:tlo-module-properties-templateRootPaths>`.

..  _typoscript-constants-view.partialRootPath:

..  confval:: view.partialRootPath
    :type: string
    :Path: :typoscript:`module.tx_warming`

    Additional path to template partials used in Backend context. Within
    this path, Fluid partials can be overwritten.

    ..  seealso::

        Read more in the :ref:`official documentation <t3tsref:tlo-module-properties-partialRootPaths>`.
