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
[![Supported TYPO3 versions](https://shields.io/endpoint?url=https://typo3-badges.dev/badge/warming/typo3/shields)](https://extensions.typo3.org/extension/warming)
[![Extension stability](https://shields.io/endpoint?url=https://typo3-badges.dev/badge/warming/stability/shields)](https://extensions.typo3.org/extension/warming)
[![Slack](https://img.shields.io/badge/slack-%23ext--warming-4a154b?logo=slack)](https://typo3.slack.com/archives/C0400CSGWAY)

**📙&nbsp;[Documentation](https://docs.typo3.org/p/eliashaeussler/typo3-warming/main/en-us/)** |
📦&nbsp;[Packagist](https://packagist.org/packages/eliashaeussler/typo3-warming) |
🐥&nbsp;[TYPO3 extension repository](https://extensions.typo3.org/extension/warming) |
💾&nbsp;[Repository](https://github.com/eliashaeussler/typo3-warming) |
🐛&nbsp;[Issue tracker](https://github.com/eliashaeussler/typo3-warming/issues)

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
* Compatible with TYPO3 10.4 LTS and 11.5 LTS

## 🔥 Installation

Via Composer:

```bash
composer require eliashaeussler/typo3-warming
```

Or download the zip file from
[TYPO3 extension repository (TER)](https://extensions.typo3.org/extension/warming).

## 🚧 Migration

### 0.5.x → 1.0.0

@todo: Make beautiful!

- CacheWarmupService: CrawlerFactory from library used -> DI for crawlers no longer possible
- CacheWarmupService: warmupPages/warmupSites > warmup
- Crawlers are now final
- ConfigurableClientTrait: removed
- RequestAwareTrait: removed
- UserAgentTrait: removed
- Sitemap/Provider/ProviderInterface: renamed to Provider
- AbstractProvider: removed
- WarmupRequest: final
- WarmupRequest: updateCallback removed, use StreamResponseHandler instead
- WarmupRequest: results removed, use CacheWarmupResult from CacheWarmupService instead
- RequestAwareInterface: removed, use StreamableCrawler instead
- Configuration: getAll() removed
- CacheManager: renamed to SitemapsCache
- CacheManager: get() without parameters removed
- CacheManager: get() now returns Sitemap instead of sitemap url
- WarmupCommand: new option `--format`
- New extension configurations `strategy` and `exclude`
- WarmupRequest: new namespace "ValueObject"
- TranslatableTrait: moved to new Localization class
- BackendUserAuthenticationTrait: moved to new BackendUtility class
- ViewTrait: replaced by `TemplateRenderer`
- Cache path changed
- CacheWarmupController: migrate to single-action controllers

## 💎 Credits

The extension icon ("rocket") as well as the icons for cache warmup actions are
modified versions of the original
[`actions-rocket`](https://typo3.github.io/TYPO3.Icons/icons/actions/actions-rocket.html)
icon from TYPO3 core which is originally licensed under
[MIT License](https://github.com/TYPO3/TYPO3.Icons/blob/main/LICENSE).

## ⭐ License

This project is licensed under [GNU General Public License 2.0 (or later)](LICENSE.md).
