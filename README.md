<div align="center">

![Extension icon](Resources/Public/Icons/Extension.svg)

# TYPO3 extension `warming`

[![Coverage](https://codecov.io/gh/eliashaeussler/typo3-warming/branch/main/graph/badge.svg?token=7M3UXACCKA)](https://codecov.io/gh/eliashaeussler/typo3-warming)
[![Maintainability](https://api.codeclimate.com/v1/badges/2f55fa181559fdda4cc1/maintainability)](https://codeclimate.com/github/eliashaeussler/typo3-warming/maintainability)
[![Tests](https://github.com/eliashaeussler/typo3-warming/actions/workflows/tests.yaml/badge.svg)](https://github.com/eliashaeussler/typo3-warming/actions/workflows/tests.yaml)
[![CGL](https://github.com/eliashaeussler/typo3-warming/actions/workflows/cgl.yaml/badge.svg)](https://github.com/eliashaeussler/typo3-warming/actions/workflows/cgl.yaml)
[![Release](https://github.com/eliashaeussler/typo3-warming/actions/workflows/release.yaml/badge.svg)](https://github.com/eliashaeussler/typo3-warming/actions/workflows/release.yaml)
[![License](http://poser.pugx.org/eliashaeussler/typo3-warming/license)](LICENSE.md)\
[![Version](https://shields.io/endpoint?url=https://typo3-badges.dev/badge/warming/version/shields)](https://extensions.typo3.org/extension/warming)
[![Downloads](https://shields.io/endpoint?url=https://typo3-badges.dev/badge/warming/downloads/shields)](https://extensions.typo3.org/extension/warming)
[![Extension stability](https://shields.io/endpoint?url=https://typo3-badges.dev/badge/warming/stability/shields)](https://extensions.typo3.org/extension/warming)
[![TYPO3 badge](https://shields.io/endpoint?url=https://typo3-badges.dev/badge/typo3/shields)](https://typo3.org/)
[![Slack](https://img.shields.io/badge/slack-%23ext--warming-4a154b?logo=slack)](https://typo3.slack.com/archives/C0400CSGWAY)

**:orange_book:&nbsp;[Documentation](https://docs.typo3.org/p/eliashaeussler/typo3-warming/main/en-us/)** |
:package:&nbsp;[Packagist](https://packagist.org/packages/eliashaeussler/typo3-warming) |
:hatched_chick:&nbsp;[TYPO3 extension repository](https://extensions.typo3.org/extension/warming) |
:floppy_disk:&nbsp;[Repository](https://github.com/eliashaeussler/typo3-warming) |
:bug:&nbsp;[Issue tracker](https://github.com/eliashaeussler/typo3-warming/issues)

</div>

An extension for TYPO3 CMS that warms up Frontend caches based on an XML sitemap.
Cache warmup can be triggered via TYPO3 backend or using a console command.
It supports multiple languages and custom crawler implementations.

## :rocket: Features

* Warmup of Frontend caches from pages or XML sitemap
* Integration in TYPO3 backend toolbar and page tree
* Support of various sitemap providers (e.g. `robots.txt` or custom location)
* Multi-language support
* Support for custom crawlers
* Console command
* Compatible with TYPO3 10.4 LTS and 11.5 LTS

## :fire: Installation

Via Composer:

```bash
composer require eliashaeussler/typo3-warming
```

Or download the zip file from
[TYPO3 extension repository (TER)](https://extensions.typo3.org/extension/warming).

## :star: License

This project is licensed under [GNU General Public License 2.0 (or later)](LICENSE.md).

[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Feliashaeussler%2Ftypo3-warming.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2Feliashaeussler%2Ftypo3-warming?ref=badge_large)
