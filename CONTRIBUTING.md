# Contributing

Thanks for considering contributing to this project! Each contribution is
highly appreciated. In order to maintain a high code quality, please follow
all steps below.

## Preparation

```bash
# Clone repository
git clone https://github.com/eliashaeussler/typo3-warming.git
cd typo3-warming

# Install dependencies
composer install
```

## Run linters

```bash
# All linters
composer lint

# Specific linters
composer lint:composer
composer lint:editorconfig
composer lint:php
```

## Run static code analysis

```bash
# All static code analyzers
composer sca

# Specific static code analyzers
composer sca:php
```

## Run tests

```bash
# All tests
composer test

# All tests with code coverage
composer test:ci
```

### Test reports

Code coverage reports are written to `.Build/log/coverage`. You can open the
last HTML report like follows:

```bash
open .Build/log/coverage/html/index.html
```

## Submit a pull request

Once you have finished your work, please **submit a pull request** and describe
what you've done. Ideally, your PR references an issue describing the problem
you're trying to solve.

All described code quality tools are automatically executed on each pull request
for all currently supported PHP versions and TYPO3 versions. Take a look at
the appropriate [workflows](.github/workflows) to get a detailed overview.
