<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2025 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3Warming\Command;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3SitemapLocator;
use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Domain;
use EliasHaeussler\Typo3Warming\Http;
use Psr\EventDispatcher;
use Symfony\Component\Console;
use TYPO3\CMS\Core;

/**
 * WarmupCommand
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Console\Attribute\AsCommand(
    name: 'warming:cachewarmup',
    description: 'Warm up Frontend caches of single pages and/or whole sites using their XML sitemaps.',
)]
final class WarmupCommand extends Console\Command\Command
{
    private const ALL_LANGUAGES = -1;
    private const ALL_SITES = 'all';

    public function __construct(
        private readonly Configuration\Configuration $configuration,
        private readonly CacheWarmup\Crawler\Strategy\CrawlingStrategyFactory $crawlingStrategyFactory,
        private readonly Typo3SitemapLocator\Sitemap\SitemapLocator $sitemapLocator,
        private readonly Domain\Repository\SiteRepository $siteRepository,
        private readonly Domain\Repository\SiteLanguageRepository $siteLanguageRepository,
        private readonly EventDispatcher\EventDispatcherInterface $eventDispatcher,
        private readonly Core\Package\PackageManager $packageManager,
        private readonly Http\Message\PageUriBuilder $pageUriBuilder,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $v = fn(mixed $value) => $value;
        $decoratedCrawlingStrategies = \implode(PHP_EOL, array_map(
            static fn(string $strategy) => '  │  * <info>' . $strategy . '</info>',
            $this->crawlingStrategyFactory->getAll(),
        ));

        $crawlingStrategy = $this->configuration->getStrategy();
        if ($crawlingStrategy !== null) {
            $crawlingStrategy = $crawlingStrategy::getName();
        }

        $this->setDescription('Warm up Frontend caches of single pages and/or whole sites using their XML sitemaps.');
        $this->setHelp(
            <<<HELP
This command can be used in many ways to warm up frontend caches.
Some possible combinations and options are shown below.

<info>Sites and pages</info>
<info>===============</info>

To warm up caches, either <info>pages</info> or <info>sites</info> can be specified.
Both types can also be combined or extended by the specification of one or more <info>languages</info>.
If you omit the language option, the caches of all languages of the requested pages and sites
will be warmed up.

You can also use the special keyword <info>all</info> for <info>sites</info>.
This will cause all available sites to be warmed up.

Examples:

* <comment>warming:cachewarmup -p 1,2,3</comment>
  ├─ Pages: <info>1, 2 and 3</info>
  └─ Languages: <info>all</info>

* <comment>warming:cachewarmup -s 1</comment>
* <comment>warming:cachewarmup -s main</comment>
  ├─ Sites: <info>Root page ID 1</info> or <info>identifier "main"</info>
  └─ Languages: <info>all</info>

* <comment>warming:cachewarmup -p 1 -s 1</comment>
* <comment>warming:cachewarmup -p 1 -s main</comment>
  ├─ Pages: <info>1</info>
  ├─ Sites: <info>Root page ID 1</info> or <info>identifier "main"</info>
  └─ Languages: <info>all</info>

* <comment>warming:cachewarmup -s 1 -l 0,1</comment>
  ├─ Sites: <info>Root page ID 1</info> or <info>identifier "main"</info>
  └─ Languages: <info>0 and 1</info>

* <comment>warming:cachewarmup -s all</comment>
  ├─ Sites: <info>all</info>
  └─ Languages: <info>all</info>

<info>Additional options</info>
<info>==================</info>

* <comment>Configuration file</comment>
  ├─ A preconfigured set of configuration options can be written to a configuration file.
  │  This file can be passed using the <info>--config</info> option.
  │  It may also contain extension paths, e.g. <info>EXT:sitepackage/Configuration/cache-warmup.json</info>.
  │  The following file formats are currently supported:
  │  * <info>json</info>
  │  * <info>php</info>
  │  * <info>yaml</info>
  │  * <info>yml</info>
  └─ Example: <comment>warming:cachewarmup --config path/to/cache-warmup.json</comment>

* <comment>Strict mode</comment>
  ├─ You can pass the <info>--strict</info> (or <info>-x</info>) option to terminate execution with an error code
  │  if individual caches warm up incorrectly.
  │  This is especially useful for automated execution of cache warmups.
  ├─ Default: <info>false</info>
  └─ Example: <comment>warming:cachewarmup -s 1 --strict</comment>

* <comment>Crawl limit</comment>
  ├─ The maximum number of pages to be warmed up can be defined via the extension configuration <info>limit</info>.
  │  It can be overridden by using the <info>--limit</info> option.
  │  The value <info>0</info> deactivates the crawl limit.
  ├─ Default: <info>{$v($this->configuration->getLimit())}</info>
  ├─ Example: <comment>warming:cachewarmup -s 1 --limit 100</comment> (limits crawling to 100 pages)
  └─ Example: <comment>warming:cachewarmup -s 1 --limit 0</comment> (no limit)

* <comment>Crawling strategy</comment>
  ├─ A crawling strategy defines how URLs will be crawled, e.g. by sorting them by a specific property.
  │  It can be defined via the extension configuration <info>strategy</info> or by using the <info>--strategy</info> option.
  │  The following strategies are currently available:
{$decoratedCrawlingStrategies}
  ├─ Default: <info>{$v($crawlingStrategy ?? 'none')}</info>
  └─ Example: <comment>warming:cachewarmup --strategy {$v(CacheWarmup\Crawler\Strategy\SortByPriorityStrategy::getName())}</comment>

* <comment>Format output</comment>
  ├─ By default, all user-oriented output is printed as plain text to the console.
  │  However, you can use other formatters by using the <info>--format</info> (or <info>-f</info>) option.
  ├─ Default: <info>{$v(CacheWarmup\Formatter\TextFormatter::getType())}</info>
  ├─ Example: <comment>warming:cachewarmup --format {$v(CacheWarmup\Formatter\TextFormatter::getType())}</comment> (normal output as plaintext)
  └─ Example: <comment>warming:cachewarmup --format {$v(CacheWarmup\Formatter\JsonFormatter::getType())}</comment> (displays output as JSON)

<info>Crawling configuration</info>
<info>======================</info>

* <comment>Alternative crawler</comment>
  ├─ Use the extension configuration <info>verboseCrawler</info> to use an alternative crawler for
  │  command-line requests. For warmup requests triggered via the TYPO3 backend, you can use the
  │  extension configuration <info>crawler</info>.
  ├─ Currently used default crawler: <info>{$v($this->configuration->getCrawler()::class)}</info>
  └─ Currently used verbose crawler: <info>{$v($this->configuration->getVerboseCrawler()::class)}</info>

* <comment>Custom User-Agent header</comment>
  ├─ When the default crawler is used, each warmup request is executed with a special User-Agent header.
  │  This header is generated from the encryption key of the TYPO3 installation.
  │  It can be used, for example, to exclude warmup requests from your search statistics.
  └─ Current User-Agent: <info>{$v($this->configuration->getUserAgent())}</info>
HELP
        );

        $this->addOption(
            'pages',
            'p',
            Console\Input\InputOption::VALUE_REQUIRED | Console\Input\InputOption::VALUE_IS_ARRAY,
            'Pages whose Frontend caches are to be warmed up.',
        );
        $this->addOption(
            'sites',
            's',
            Console\Input\InputOption::VALUE_REQUIRED | Console\Input\InputOption::VALUE_IS_ARRAY,
            'Site identifiers or root page IDs of sites whose caches are to be warmed up.',
        );
        $this->addOption(
            'languages',
            'l',
            Console\Input\InputOption::VALUE_REQUIRED | Console\Input\InputOption::VALUE_IS_ARRAY,
            'Optional identifiers of languages for which caches are to be warmed up.',
        );
        $this->addOption(
            'config',
            'c',
            Console\Input\InputOption::VALUE_REQUIRED,
            'Path to optional configuration file',
        );
        $this->addOption(
            'limit',
            null,
            Console\Input\InputOption::VALUE_REQUIRED,
            'Maximum number of pages to be crawled. Set to <info>0</info> to disable the limit.',
            $this->configuration->getLimit(),
        );
        $this->addOption(
            'strategy',
            null,
            Console\Input\InputOption::VALUE_REQUIRED,
            'Optional strategy to prepare URLs before crawling them.',
            $crawlingStrategy,
        );
        $this->addOption(
            'format',
            'f',
            Console\Input\InputOption::VALUE_REQUIRED,
            'Formatter used to print the cache warmup result',
            CacheWarmup\Formatter\TextFormatter::getType(),
        );
        $this->addOption(
            'strict',
            'x',
            Console\Input\InputOption::VALUE_NONE,
            'Fail if an error occurred during cache warmup.',
        );
    }

    protected function initialize(Console\Input\InputInterface $input, Console\Output\OutputInterface $output): void
    {
        Core\Core\Bootstrap::initializeBackendAuthentication();
    }

    /**
     * @throws CacheWarmup\Exception\Exception
     * @throws Console\Exception\ExceptionInterface
     * @throws Core\Package\Exception\UnknownPackageException
     * @throws Core\Package\Exception\UnknownPackagePathException
     * @throws Typo3SitemapLocator\Exception\BaseUrlIsNotSupported
     * @throws Typo3SitemapLocator\Exception\SitemapIsMissing
     * @throws \JsonException
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output): int
    {
        // Initialize sub command
        $subCommand = new CacheWarmup\Command\CacheWarmupCommand($this->eventDispatcher, $this->crawlingStrategyFactory);
        $subCommand->setApplication($this->getApplication() ?? new Console\Application());

        // Initialize sub command input
        $subCommandInput = new Console\Input\ArrayInput(
            $this->prepareCommandParameters($input),
            $subCommand->getDefinition(),
        );
        $subCommandInput->setInteractive(false);

        // Run cache warmup in sub command from eliashaeussler/cache-warmup
        $statusCode = $subCommand->run($subCommandInput, $output);

        // Fail if strict mode is enabled and at least one crawl was erroneous
        if ($input->getOption('strict') && $statusCode > 0) {
            return $statusCode;
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     * @throws CacheWarmup\Exception\Exception
     * @throws Core\Package\Exception\UnknownPackageException
     * @throws Core\Package\Exception\UnknownPackagePathException
     * @throws Typo3SitemapLocator\Exception\BaseUrlIsNotSupported
     * @throws Typo3SitemapLocator\Exception\SitemapIsMissing
     * @throws \JsonException
     */
    private function prepareCommandParameters(Console\Input\InputInterface $input): array
    {
        // Resolve input options
        $languages = $this->resolveLanguages($input->getOption('languages'));
        $urls = array_unique($this->resolvePages($input->getOption('pages'), $languages));
        $sitemaps = array_unique($this->resolveSites($input->getOption('sites'), $languages));
        $config = $input->getOption('config');
        $limit = max(0, (int)$input->getOption('limit'));
        $strategy = $input->getOption('strategy');
        $format = $input->getOption('format');

        // Fetch input options from extension configuration
        $excludePatterns = $this->configuration->getExcludePatterns();
        $crawler = $this->configuration->getVerboseCrawler();
        $crawlerOptions = $this->configuration->getVerboseCrawlerOptions();
        $parserOptions = $this->configuration->getParserOptions();

        // Initialize sub-command parameters
        $subCommandParameters = [
            'sitemaps' => $sitemaps,
            '--urls' => $urls,
            '--limit' => $limit,
            '--crawler' => $crawler::class,
            '--format' => $format,
        ];

        // Add crawler options to sub-command parameters
        if ($crawlerOptions !== []) {
            $subCommandParameters['--crawler-options'] = json_encode($crawlerOptions, JSON_THROW_ON_ERROR);
        }

        // Add parser options to sub-command parameters
        if ($parserOptions !== []) {
            $subCommandParameters['--parser-options'] = json_encode($parserOptions, JSON_THROW_ON_ERROR);
        }

        // Add exclude patterns
        if ($excludePatterns !== []) {
            $subCommandParameters['--exclude'] = $excludePatterns;
        }

        // Add crawling strategy
        if ($strategy !== null) {
            $subCommandParameters['--strategy'] = $strategy;
        }

        // Add config file
        if ($config !== null) {
            if (Core\Utility\PathUtility::isExtensionPath($config)) {
                $config = $this->packageManager->resolvePackagePath($config);
            }

            $subCommandParameters['--config'] = $config;
        }

        return $subCommandParameters;
    }

    /**
     * @param array<string> $pages
     * @param list<int<-1, max>> $languages
     * @return list<string>
     */
    private function resolvePages(array $pages, array $languages): array
    {
        $resolvedUrls = [];

        foreach ($pages as $pageList) {
            $normalizedPages = Core\Utility\GeneralUtility::intExplode(',', $pageList, true);

            /** @var positive-int $page */
            foreach ($normalizedPages as $page) {
                $languageIds = $languages;

                if (\in_array(self::ALL_LANGUAGES, $languageIds, true)) {
                    $site = $this->siteRepository->findOneByPageId($page);
                    $languageIds = array_keys($site?->getLanguages() ?? []);
                }

                foreach ($languageIds as $languageId) {
                    $uri = $this->pageUriBuilder->build($page, $languageId);

                    if ($uri !== null) {
                        $resolvedUrls[] = (string)$uri;
                    }
                }
            }
        }

        return $resolvedUrls;
    }

    /**
     * @param array<string> $sites
     * @param list<int<-1, max>> $languages
     * @return list<Domain\Model\SiteAwareSitemap>
     * @throws CacheWarmup\Exception\LocalFilePathIsMissingInUrl
     * @throws CacheWarmup\Exception\UrlIsEmpty
     * @throws CacheWarmup\Exception\UrlIsInvalid
     * @throws Typo3SitemapLocator\Exception\BaseUrlIsNotSupported
     * @throws Typo3SitemapLocator\Exception\SitemapIsMissing
     */
    private function resolveSites(array $sites, array $languages): array
    {
        $requestedSites = [];
        $resolvedSitemaps = [];

        foreach ($sites as $siteList) {
            $requestedSites += Core\Utility\GeneralUtility::trimExplode(',', $siteList, true);

            if (\in_array(self::ALL_SITES, $requestedSites, true)) {
                $requestedSites = $this->siteRepository->findAll();

                break;
            }
        }

        foreach ($requestedSites as $site) {
            if (Core\Utility\MathUtility::canBeInterpretedAsInteger($site)) {
                /** @var positive-int $rootPageId */
                $rootPageId = (int)$site;
                $site = $this->siteRepository->findOneByRootPageId($rootPageId);
            } elseif (is_string($site)) {
                $site = $this->siteRepository->findOneByIdentifier($site);
            }

            // Skip inaccessible sites
            if ($site === null) {
                continue;
            }

            $languageIds = $languages;

            if ([self::ALL_LANGUAGES] === $languageIds) {
                $languageIds = array_keys($site->getLanguages());
            }

            foreach ($languageIds as $languageId) {
                $siteLanguage = $this->siteLanguageRepository->findOneByLanguageId($site, $languageId);

                // Skip inaccessible site languages
                if ($siteLanguage === null) {
                    continue;
                }

                $sitemaps = $this->sitemapLocator->locateBySite($site, $siteLanguage);

                foreach ($sitemaps as $sitemap) {
                    $resolvedSitemaps[] = Domain\Model\SiteAwareSitemap::fromLocatedSitemap($sitemap);
                }
            }
        }

        return $resolvedSitemaps;
    }

    /**
     * @param array<string> $languages
     * @return list<int<-1, max>>
     */
    private function resolveLanguages(array $languages): array
    {
        $resolvedLanguages = [];

        if ($languages === []) {
            // Run cache warmup for all languages by default
            return [self::ALL_LANGUAGES];
        }

        foreach ($languages as $languageList) {
            $normalizedLanguages = Core\Utility\GeneralUtility::intExplode(',', $languageList, true);

            if (\in_array(self::ALL_LANGUAGES, $normalizedLanguages, true)) {
                return [self::ALL_LANGUAGES];
            }

            foreach ($normalizedLanguages as $languageId) {
                if ($languageId >= 0) {
                    $resolvedLanguages[] = $languageId;
                }
            }
        }

        return $resolvedLanguages;
    }
}
