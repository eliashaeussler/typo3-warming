[![Pipeline](https://gitlab.elias-haeussler.de/typo3/warming/badges/master/pipeline.svg)](https://gitlab.elias-haeussler.de/typo3/warming/-/pipelines)
[![Coverage](https://gitlab.elias-haeussler.de/typo3/warming/badges/master/coverage.svg)](https://gitlab.elias-haeussler.de/typo3/warming/-/pipelines)
[![License](https://badgen.net/packagist/license/eliashaeussler/typo3-warming)](LICENSE.md)

# Warming

> Extension for TYPO3 CMS that warms up Frontend caches based on a XML sitemap.

![Extension icon](Resources/Public/Icons/Extension.svg)

## Installation

```bash
composer require eliashaeussler/typo3-warming
```

## Usage

Caches can be warmed up in two different modes – either on a **per-page** basis or
using the **XML sitemap of a site**. Currently, only one XML sitemap per page can
be used for cache warmup.

### Toolbar item

**Note: The toolbar item is only visible for admins and permitted users.**

As soon as the extension is installed, a new toolbar item in your TYPO3 backend
should appear. You can click on the toolbar item to get a list of all sites. If a
site does not provide a XML sitemap, it cannot be used to warm up caches.

![Toolbar item dropdown menu](Resources/Public/Images/Documentation/toolbar-item.png)

### Context menu

**Note: the context menu items are only visible for admins and permitted users.**

Next to the item in the toolbar, one can also trigger cache warmup using the context
menu of pages inside the page tree.

![Context menu](Resources/Public/Images/Documentation/context-menu.png)

The option "Warmup cache for this page" is available for all pages whereas the option
"Warmup all caches" is only available for site root pages.

### Console command

The extension provides a Console command which allows triggering cache warmup
from the console or using a Scheduler task.

```bash
typo3cms warming:cachewarmup [-p|--pages <pages>] [-s|--sites <sites>] [-x|--strict]
```

* `-p|--pages`: Define single pages to be crawled for cache warmup
* `-s|--sites`: Define site identifiers or site root pages for cache warmup
* `-x|--strict`: Set this option to exit with error in case any page could not
  be crawled during cache warmup

## Configuration

### Permissions

All administrators are able to run cache warmup for sites and pages. All other users
are not allowed to run those tasks. However, you can use User TSconfig to allow
warmup for specific users/usergroups and sites/pages.

```typo3_typoscript
# Comma-separated list of pages to be allowed for warming up caches
options.cacheWarmup.allowedPages = 1,2,3

# Comma-separated list of site identifiers to be allowed for warming up caches
options.cacheWarmup.allowedSites = my-dummy-site,another-dummy-site
```

### Path to XML sitemap

The path to a XML sitemap is determined in three steps:

1. Site configuration: Within the Sites module, one can explicitly define the path
   to the XML sitemap of a site.
2. `robots.txt`: If no path is defined in the site configuration, a possible
   `robots.txt` file is parsed for a valid `Sitemap` configuration. **Note: Only
   the first occurrence will be respected.**
3. Default path: If none of the above methods are successful, the default path
   `sitemap.xml` is used.

![Sitemap XML path in site configuration](Resources/Public/Images/Documentation/site-configuration.png)

In order to be able to locate the sitemap path, all mentioned methods are bundled
in so-called `providers`. You are free to implement custom providers or remove ones
using a custom `Services.yaml` file. See the section
[Sitemap providers](#sitemap-providers) for more information about this topic.

### Extension configuration

The extension configuration currently provides two configuration options:

* `limit`: Allows to limit the amount of crawled pages in one iteration. Can be
  set to `0` to crawl all pages in XML sitemap.
* `crawler`: Custom crawler to be used for crawling the requested pages. Note
  that custom crawlers must implement
  [`EliasHaeussler\CacheWarmup\Crawler\CrawlerInterface`][1].

[1]: https://gitlab.elias-haeussler.de/eliashaeussler/cache-warmup/-/blob/master/src/Crawler/CrawlerInterface.php

### Crawler

There exist two dedicated crawlers: one to run the cache warmup from the Backend
([`ConcurrentUserAgentCrawler`](Classes/Crawler/ConcurrentUserAgentCrawler.php))
and another one to use when running from the command line
([`OutputtingUserAgentCrawler`](Classes/Crawler/OutputtingUserAgentCrawler.php)).

Both crawlers define their own `User-Agent` header, which generates a hash from the
encryption key of the TYPO3 installation. This `User-Agent` header can be copied in
the dropdown of the toolbar item in the Backend to exclude such requests from the
statistics of analysis tools, for example.

Alternatively, the command `warming:showuseragent` can be used to read the
`User-Agent` header.

## Sitemap providers

The path to XML sitemaps is located using various path providers. All providers
implement the [`ProviderInterface`](Classes/Sitemap/Provider/ProviderInterface.php).

The [`SitemapLocator`](Classes/Sitemap/SitemapLocator.php) is fed by a list of
providers. That list is configured in the service container using the `Services.yaml`
file.

All providers will be processed in natural order, meaning the provider with the
lowest array index will be processed first. If any provider returns a valid
[`Sitemap`](https://gitlab.elias-haeussler.de/eliashaeussler/cache-warmup/-/blob/master/src/Sitemap.php)
object, the remaining providers won't be processed.

You are free to modify or extend the list of path providers. Keep in mind that the
[`DefaultProvider`](Classes/Sitemap/Provider/DefaultProvider.php) should always
be used as last provider since it always returns a `Sitemap` object.

```yaml
# Configuration/Services.yaml

services:
  # ...
  EliasHaeussler\Typo3Warming\Sitemap\SitemapLocator:
    public: true
    arguments:
      $providers:
        - '@My\Vendor\Sitemap\Provider\MyCustomProvider'
        - '@EliasHaeussler\Typo3Warming\Sitemap\Provider\DefaultProvider'
```

## License

This project is licensed under [GPL 2.0 (or later)](LICENSE.md).
