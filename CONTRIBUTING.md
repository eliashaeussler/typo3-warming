# Contributing

Thanks for considering contributing to this project! Each contribution is
highly appreciated. In order to maintain a high code quality, please follow
all steps below.

This project uses [DDEV][1] for local development. Make sure to set it up as
described below. For continuous integration, we use GitHub Actions.

## Preparation

```bash
# Clone repository
git clone https://github.com/eliashaeussler/typo3-warming.git
cd typo3-warming

# Start DDEV project
ddev start

# Install Composer dependencies
ddev composer install

# Install Node dependencies
ddev frontend install
```

You can access the DDEV site at <https://typo3-ext-warming.ddev.site/>.

## Run linters

### TYPO3

```bash
# All linters
ddev composer lint

# Specific linters
ddev composer lint:composer
ddev composer lint:editorconfig
ddev composer lint:php
```

### Frontend

```bash
# All linters
ddev frontend lint
ddev frontend lint:fix

# Specific linters
ddev frontend lint:scss
ddev frontend lint:scss:fix
ddev frontend lint:ts
ddev frontend lint:ts:fix
```

## Run static code analysis

```bash
# All static code analyzers
ddev composer sca

# Specific static code analyzers
ddev composer sca:php
```

## Run tests

```bash
# All tests
ddev composer test

# Specific tests
ddev composer test:functional
ddev composer test:unit

# All tests with code coverage
ddev composer test:ci

# Specific tests with code coverage
ddev composer test:ci:functional
ddev composer test:ci:unit

# Merge code coverage of all test suites
ddev composer test:ci:merge
```

### Test reports

Code coverage reports are written to `.Build/log/coverage`. You can open the
last merged HTML report like follows:

```bash
open .Build/log/coverage/html/_merged/index.html
```

:bulb: Make sure to merge coverage reports as written above.

## Submit a pull request

Once you have finished your work, please **submit a pull request** and describe
what you've done. Ideally, your PR references an issue describing the problem
you're trying to solve.

All described code quality tools are automatically executed on each pull request
for all currently supported PHP versions and TYPO3 versions. Take a look at the
appropriate [workflows][2] to get a detailed overview.

[1]: https://ddev.readthedocs.io/en/stable/
[2]: .github/workflows
