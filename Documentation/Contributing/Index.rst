.. include:: /Includes.rst.txt

.. _contributing:

============
Contributing
============

Thanks for considering contributing to this extension! Since it is
an open source product, its successful further development depends
largely on improving and optimizing it together.

The development of this extension follows the official
`TYPO3 coding standards <https://github.com/TYPO3/coding-standards>`__.
To ensure the stability and cleanliness of the code, various code
quality tools are used and most components are covered with test
cases.

.. _create-an-issue-first:

Create an issue first
=====================

Before you start working on the extension, please create an issue on
GitHub: https://github.com/eliashaeussler/typo3-warming/issues

Also, please check if there is already an issue on the topic you want
to address.

.. _contribution-workflow:

Contribution workflow
=====================

.. note::

    This extension follows `Semantic Versioning <https://semver.org/>`__.

.. _preparation:

Preparation
-----------

Clone the repository first:

.. code-block:: bash

    git clone https://github.com/eliashaeussler/typo3-warming.git
    cd typo3-warming

Now install all Composer dependencies:

.. code-block:: bash

    composer install

Next, install all Node dependencies:

.. code-block:: bash

    yarn --cwd Resources/Private/Frontend

.. _check-code-quality:

Check code quality
------------------

.. image:: https://github.com/eliashaeussler/typo3-warming/actions/workflows/cgl.yaml/badge.svg
    :target: https://github.com/eliashaeussler/typo3-warming/actions/workflows/cgl.yaml

.. _cgl-typo3:

TYPO3
~~~~~

.. code-block:: bash

    # Run all linters
    composer lint

    # Run Composer linter only
    composer lint:composer

    # Run PHP linter only
    composer lint:php

    # Run TypoScript linter only
    composer lint:typoscript

    # Run PHP static code analysis
    composer sca

.. _cgl-frontend:

Frontend
~~~~~~~~

.. code-block:: bash

    # Run all linters
    yarn --cwd Resources/Private/Frontend lint
    yarn --cwd Resources/Private/Frontend lint:fix

    # Run SCSS linter only
    yarn --cwd Resources/Private/Frontend lint:scss
    yarn --cwd Resources/Private/Frontend lint:scss:fix

    # Run TypeScript linter only
    yarn --cwd Resources/Private/Frontend lint:ts
    yarn --cwd Resources/Private/Frontend lint:ts:fix

.. _run-tests:

Run tests
---------

.. image:: https://github.com/eliashaeussler/typo3-warming/actions/workflows/tests.yaml/badge.svg
    :target: https://github.com/eliashaeussler/typo3-warming/actions/workflows/tests.yaml

.. image:: https://codecov.io/gh/eliashaeussler/typo3-warming/branch/main/graph/badge.svg?token=7M3UXACCKA
    :target: https://codecov.io/gh/eliashaeussler/typo3-warming

.. rst-class:: mt-3

.. code-block:: bash

    # Run tests
    composer test

    # Run tests with code coverage
    composer test:ci

The code coverage reports will be stored in :file:`.Build/log/coverage`.

.. _build-documentation:

Build documentation
-------------------

.. code-block:: bash

    # Rebuild and open documentation
    composer docs

    # Build documentation (from cache)
    composer docs:build

    # Open rendered documentation
    composer docs:open

The built docs will be stored in :file:`.Build/docs`.

.. _pull-request:

Pull Request
------------

When you have finished developing your contribution, simply submit a
pull request on GitHub: https://github.com/eliashaeussler/typo3-warming/pulls
