<div align="center">

![Extension icon](Resources/Public/Icons/Extension.svg)

# TYPO3 extension `warming`

[![Coverage](https://img.shields.io/coverallsCoverage/github/eliashaeussler/typo3-warming?logo=coveralls)](https://coveralls.io/github/eliashaeussler/typo3-warming)
[![Maintainability](https://img.shields.io/codeclimate/maintainability/eliashaeussler/typo3-warming?logo=codeclimate)](https://codeclimate.com/github/eliashaeussler/typo3-warming/maintainability)
[![CGL](https://img.shields.io/github/actions/workflow/status/eliashaeussler/typo3-warming/cgl.yaml?label=cgl&logo=github)](https://github.com/eliashaeussler/typo3-warming/actions/workflows/cgl.yaml)
[![Tests](https://img.shields.io/github/actions/workflow/status/eliashaeussler/typo3-warming/tests.yaml?label=tests&logo=github)](https://github.com/eliashaeussler/typo3-warming/actions/workflows/tests.yaml)
[![Supported TYPO3 versions](https://typo3-badges.dev/badge/warming/typo3/shields.svg)](https://extensions.typo3.org/extension/warming)
[![Slack](https://img.shields.io/badge/slack-%23ext--warming-4a154b?logo=slack)](https://typo3.slack.com/archives/C0400CSGWAY)

</div>

An extension for TYPO3 CMS that warms up Frontend caches based on an XML sitemap.
Cache warmup can be triggered via TYPO3 backend or using a console command.
It supports multiple languages and custom crawler implementations.

## 🚀 Features

* Warmup of Frontend caches from pages or XML sitemap
* Integration in TYPO3 backend toolbar and page tree
* Support of various sitemap providers (e.g. `robots.txt` or custom location)
* Multi-language support
* Support for custom crawlers
* Console command
* Compatible with TYPO3 12.4 LTS and 13.2

## 🔥 Installation

### Composer

[![Packagist](https://img.shields.io/packagist/v/eliashaeussler/typo3-warming?label=version&logo=packagist)](https://packagist.org/packages/eliashaeussler/typo3-warming)
[![Packagist Downloads](https://img.shields.io/packagist/dt/eliashaeussler/typo3-warming?color=brightgreen)](https://packagist.org/packages/eliashaeussler/typo3-warming)

```bash
composer require eliashaeussler/typo3-warming
```

### TER

[![TER version](https://typo3-badges.dev/badge/warming/version/shields.svg)](https://extensions.typo3.org/extension/warming)
[![TER downloads](https://typo3-badges.dev/badge/warming/downloads/shields.svg)](https://extensions.typo3.org/extension/warming)

Download the zip file from
[TYPO3 extension repository (TER)](https://extensions.typo3.org/extension/warming).

## 📙 Documentation

Please have a look at the
[official extension documentation](https://docs.typo3.org/p/eliashaeussler/typo3-warming/main/en-us/).

## 💎 Credits

The extension icon ("rocket") as well as the icons for cache warmup actions are
modified versions of the original
[`actions-rocket`](https://typo3.github.io/TYPO3.Icons/icons/actions/actions-rocket.html)
icon from TYPO3 core which is originally licensed under
[MIT License](https://github.com/TYPO3/TYPO3.Icons/blob/main/LICENSE).

## ⭐ License

This project is licensed under [GNU General Public License 2.0 (or later)](LICENSE.md).
