<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3Warming\Command;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming\Configuration;
use EliasHaeussler\Typo3Warming\Crawler;
use EliasHaeussler\Typo3Warming\Exception;
use EliasHaeussler\Typo3Warming\Http;
use EliasHaeussler\Typo3Warming\Sitemap;
use EliasHaeussler\Typo3Warming\Utility;
use JsonException;
use Symfony\Component\Console;
use TYPO3\CMS\Core;

/**
 * WarmupCommand
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class WarmupCommand extends Console\Command\Command
{
    private const ALL_LANGUAGES = -1;

    public function __construct(
        private readonly Http\Client\ClientFactory $clientFactory,
        private readonly Configuration\Configuration $configuration,
        private readonly Crawler\Strategy\CrawlingStrategyFactory $crawlingStrategyFactory,
        private readonly Sitemap\SitemapLocator $sitemapLocator,
        private readonly Core\Site\SiteFinder $siteFinder,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Warm up Frontend caches of single pages and/or whole sites using their XML sitemaps.');
        $this->setHelp(implode(PHP_EOL, [
            'This command can be used in many ways to warm up frontend caches.',
            'Some possible combinations and options are shown below.',
            '',
            '<info>Sites and pages</info>',
            '<info>===============</info>',
            '',
            'To warm up caches, either <info>pages</info> or <info>sites</info> can be specified.',
            'Both types can also be combined or extended by the specification of one or more <info>languages</info>.',
            'If you omit the language option, the caches of all languages of the requested pages and sites',
            'will be warmed up.',
            '',
            'Examples:',
            '',
            '* <comment>warming:cachewarmup -p 1,2,3</comment>',
            '  ├─ Pages: <info>1, 2 and 3</info>',
            '  └─ Languages: <info>all</info>',
            '',
            '* <comment>warming:cachewarmup -s 1</comment>',
            '* <comment>warming:cachewarmup -s main</comment>',
            '  ├─ Sites: <info>Root page ID 1</info> or <info>identifier "main"</info>',
            '  └─ Languages: <info>all</info>',
            '',
            '* <comment>warming:cachewarmup -p 1 -s 1</comment>',
            '* <comment>warming:cachewarmup -p 1 -s main</comment>',
            '  ├─ Pages: <info>1</info>',
            '  ├─ Sites: <info>Root page ID 1</info> or <info>identifier "main"</info>',
            '  └─ Languages: <info>all</info>',
            '',
            '* <comment>warming:cachewarmup -s 1 -l 0,1</comment>',
            '  ├─ Sites: <info>Root page ID 1</info> or <info>identifier "main"</info>',
            '  └─ Languages: <info>0 and 1</info>',
            '',
            '<info>Additional options</info>',
            '<info>==================</info>',
            '',
            '* <comment>Strict mode</comment>',
            '  ├─ You can pass the <info>--strict</info> (or <info>-x</info>) option to terminate execution with an error code',
            '  │  if individual caches warm up incorrectly.',
            '  │  This is especially useful for automated execution of cache warmups.',
            '  ├─ Default: <info>false</info>',
            '  └─ Example: <comment>warming:cachewarmup -s 1 --strict</comment>',
            '',
            '* <comment>Crawl limit</comment>',
            '  ├─ The maximum number of pages to be warmed up can be defined via the extension configuration <info>limit</info>.',
            '  │  It can be overridden by using the <info>--limit</info> option.',
            '  │  The value <info>0</info> deactivates the crawl limit.',
            '  ├─ Default: <info>' . $this->configuration->getLimit() . '</info>',
            '  ├─ Example: <comment>warming:cachewarmup -s 1 --limit 100</comment> (limits crawling to 100 pages)',
            '  └─ Example: <comment>warming:cachewarmup -s 1 --limit 0</comment> (no limit)',
            '',
            '* <comment>Crawling strategy</comment>',
            '  ├─ A crawling strategy defines how URLs will be crawled, e.g. by sorting them by a specific property.',
            '  │  It can be defined via the extension configuration <info>strategy</info> or by using the <info>--strategy</info> option.',
            '  │  The following strategies are currently available:',
            ...array_map(
                static fn (string $strategy) => '  │  * <info>' . $strategy . '</info>',
                array_keys($this->crawlingStrategyFactory->getAll()),
            ),
            '  ├─ Default: <info>' . ($this->configuration->getStrategy() ?? 'none') . '</info>',
            '  └─ Example: <comment>warming:cachewarmup --strategy ' . CacheWarmup\Crawler\Strategy\SortByPriorityStrategy::getName() . '</comment>',
            '',
            '* <comment>Format output</comment>',
            '  ├─ By default, all user-oriented output is printed as plain text to the console.',
            '  │  However, you can use other formatters by using the <info>--format</info> (or <info>-f</info>) option.',
            '  ├─ Default: <info>' . CacheWarmup\Formatter\TextFormatter::getType() . '</info>',
            '  ├─ Example: <comment>warming:cachewarmup --format ' . CacheWarmup\Formatter\TextFormatter::getType() . '</comment> (normal output as plaintext)',
            '  └─ Example: <comment>warming:cachewarmup --format ' . CacheWarmup\Formatter\JsonFormatter::getType() . '</comment> (displays output as JSON)',
            '',
            '<info>Crawling configuration</info>',
            '<info>======================</info>',
            '',
            '* <comment>Alternative crawler</comment>',
            '  ├─ Use the extension configuration <info>verboseCrawler</info> to use an alternative crawler for',
            '  │  command-line requests. For warmup requests triggered via the TYPO3 backend, you can use the',
            '  │  extension configuration <info>crawler</info>.',
            '  ├─ Currently used default crawler: <info>' . $this->configuration->getCrawler() . '</info>',
            '  └─ Currently used verbose crawler: <info>' . $this->configuration->getVerboseCrawler() . '</info>',
            '',
            '* <comment>Custom User-Agent header</comment>',
            '  ├─ When the default crawler is used, each warmup request is executed with a special User-Agent header.',
            '  │  This header is generated from the encryption key of the TYPO3 installation.',
            '  │  It can be used, for example, to exclude warmup requests from your search statistics.',
            '  └─ Current User-Agent: <info>' . $this->configuration->getUserAgent() . '</info>',
        ]));

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
            $this->configuration->getStrategy(),
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

    /**
     * @throws CacheWarmup\Exception\InvalidUrlException
     * @throws Console\Exception\ExceptionInterface
     * @throws Core\Exception\SiteNotFoundException
     * @throws Exception\UnsupportedConfigurationException
     * @throws Exception\UnsupportedSiteException
     * @throws JsonException
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output): int
    {
        // Initialize sub command
        $subCommand = new CacheWarmup\Command\CacheWarmupCommand($this->clientFactory->get());
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
     * @throws CacheWarmup\Exception\InvalidUrlException
     * @throws Core\Exception\SiteNotFoundException
     * @throws Exception\UnsupportedConfigurationException
     * @throws Exception\UnsupportedSiteException
     * @throws JsonException
     */
    private function prepareCommandParameters(Console\Input\InputInterface $input): array
    {
        // Resolve input options
        $languages = $this->resolveLanguages($input->getOption('languages'));
        $urls = array_unique($this->resolvePages($input->getOption('pages'), $languages));
        $sitemaps = array_unique($this->resolveSites($input->getOption('sites'), $languages));
        $limit = max(0, (int)$input->getOption('limit'));
        $strategy = $input->getOption('strategy');
        $format = $input->getOption('format');
        $excludePatterns = $this->configuration->getExcludePatterns();

        // Fetch crawler and crawler options
        $crawler = $this->configuration->getVerboseCrawler();
        $crawlerOptions = $this->configuration->getVerboseCrawlerOptions();

        // Initialize sub-command parameters
        $subCommandParameters = [
            'sitemaps' => $sitemaps,
            '--urls' => $urls,
            '--limit' => $limit,
            '--crawler' => $crawler,
            '--format' => $format,
        ];

        // Add crawler options to sub-command parameters
        if ($crawlerOptions !== []) {
            $subCommandParameters['--crawler-options'] = json_encode($crawlerOptions, JSON_THROW_ON_ERROR);
        }

        // Add exclude patterns
        if ($excludePatterns !== []) {
            $subCommandParameters['--exclude'] = $excludePatterns;
        }

        // Add crawling strategy
        if ($strategy !== null) {
            $subCommandParameters['--strategy'] = $strategy;
        }

        return $subCommandParameters;
    }

    /**
     * @param array<string> $pages
     * @param list<int> $languages
     * @return list<string>
     * @throws Core\Exception\SiteNotFoundException
     */
    private function resolvePages(array $pages, array $languages): array
    {
        $resolvedUrls = [];

        foreach ($pages as $pageList) {
            $normalizedPages = Core\Utility\GeneralUtility::intExplode(',', $pageList, true);

            foreach ($normalizedPages as $page) {
                $languageIds = $languages;

                if ($languageIds === [self::ALL_LANGUAGES]) {
                    $site = $this->siteFinder->getSiteByPageId($page);
                    $languageIds = array_keys($site->getLanguages());
                }

                foreach ($languageIds as $languageId) {
                    $uri = Utility\HttpUtility::generateUri($page, $languageId);

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
     * @param list<int> $languages
     * @return list<string>
     * @throws CacheWarmup\Exception\InvalidUrlException
     * @throws Core\Exception\SiteNotFoundException
     * @throws Exception\UnsupportedConfigurationException
     * @throws Exception\UnsupportedSiteException
     */
    private function resolveSites(array $sites, array $languages): array
    {
        $resolvedSitemaps = [];

        foreach ($sites as $siteList) {
            foreach (Core\Utility\GeneralUtility::trimExplode(',', $siteList, true) as $site) {
                if (Core\Utility\MathUtility::canBeInterpretedAsInteger($site)) {
                    $site = $this->siteFinder->getSiteByRootPageId((int)$site);
                } else {
                    $site = $this->siteFinder->getSiteByIdentifier($site);
                }

                $languageIds = $languages;

                if ([self::ALL_LANGUAGES] === $languageIds) {
                    $languageIds = array_keys($site->getLanguages());
                }

                foreach ($languageIds as $languageId) {
                    $resolvedSitemaps[] = (string)$this->sitemapLocator->locateBySite(
                        $site,
                        $site->getLanguageById($languageId)
                    )->getUri();
                }
            }
        }

        return $resolvedSitemaps;
    }

    /**
     * @param array<string> $languages
     * @return list<int>
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

            foreach ($normalizedLanguages as $languageId) {
                $resolvedLanguages[] = $languageId;
            }
        }

        return $resolvedLanguages;
    }
}
