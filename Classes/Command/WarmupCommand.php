<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\CacheWarmup\Command\CacheWarmupCommand;
use EliasHaeussler\Typo3Warming\Configuration\Configuration;
use EliasHaeussler\Typo3Warming\Exception\UnsupportedConfigurationException;
use EliasHaeussler\Typo3Warming\Exception\UnsupportedSiteException;
use EliasHaeussler\Typo3Warming\Service\CacheWarmupService;
use EliasHaeussler\Typo3Warming\Sitemap\SiteAwareSitemap;
use EliasHaeussler\Typo3Warming\Sitemap\SitemapLocator;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * WarmupCommand
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class WarmupCommand extends Command
{
    private const ALL_LANGUAGES = -1;

    /**
     * @var CacheWarmupService
     */
    protected $warmupService;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var SitemapLocator
     */
    protected $sitemapLocator;

    /**
     * @var SiteFinder
     */
    protected $siteFinder;

    public function __construct(
        CacheWarmupService $warmupService,
        Configuration $configuration,
        SitemapLocator $sitemapLocator,
        SiteFinder $siteFinder,
        string $name = null
    ) {
        $this->warmupService = $warmupService;
        $this->configuration = $configuration;
        $this->sitemapLocator = $sitemapLocator;
        $this->siteFinder = $siteFinder;

        parent::__construct($name);
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
            '  └─ Example: <comment>warming:cachewarmup -s 1 -x</comment>',
            '',
            '* <comment>Crawl limit</comment>',
            '  ├─ The maximum number of pages to be warmed up can be defined via the extension configuration <info>limit</info>.',
            '  │  It can be overridden by using the <info>--limit</info> option.',
            '  │  The value <info>0</info> deactivates the crawl limit.',
            '  ├─ Default: <info>' . $this->configuration->getLimit() . '</info>',
            '  ├─ Example: <comment>warming:cachewarmup -s 1 --limit 100</comment> (limits crawling to 100 pages)',
            '  └─ Example: <comment>warming:cachewarmup -s 1 --limit 0</comment> (no limit)',
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
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Pages whose Frontend caches are to be warmed up.'
        );
        $this->addOption(
            'sites',
            's',
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Site identifiers or root page IDs of sites whose caches are to be warmed up.'
        );
        $this->addOption(
            'languages',
            'l',
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Optional identifiers of languages for which caches are to be warmed up.'
        );
        $this->addOption(
            'limit',
            null,
            InputOption::VALUE_REQUIRED,
            'Maximum number of pages to be crawled. Set to <info>0</info> to disable the limit.',
            $this->configuration->getLimit()
        );
        $this->addOption(
            'strict',
            'x',
            InputOption::VALUE_NONE,
            'Fail if an error occurred during cache warmup.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $languages = iterator_to_array($this->resolveLanguages($input->getOption('languages')));
        $urls = array_unique(iterator_to_array($this->resolveUrls($input->getOption('pages'), $languages)));
        $sitemaps = array_unique(iterator_to_array($this->resolveSitemaps($input->getOption('sites'), $languages)), SORT_REGULAR);
        $limit = abs((int)$input->getOption('limit'));

        // Exit if neither pages nor sites are given
        if (\count($urls) + \count($sitemaps) === 0) {
            $io->error('You need to define at least one page or site.');
            return 1;
        }

        $io->writeln('Running <info>cache warmup</info> by <info>Elias Häußler</info> and contributors.');

        // Initialize crawler
        $crawler = $this->configuration->getVerboseCrawler();
        if (empty($crawler)) {
            $crawler = Configuration::DEFAULT_VERBOSE_CRAWLER;
        }

        // Initialize application
        $application = $this->getApplication();
        if ($application === null) {
            $application = new Application();
            $application->add($this);
        }

        // Run cache warmup in sub command from eliashaeussler/cache-warmup
        $application->add($subCommand = new CacheWarmupCommand());
        $subCommandParameters = [
            'sitemaps' => $sitemaps,
            '--urls' => $urls,
            '--limit' => $limit,
            '--crawler' => $crawler,
        ];
        $subCommandInput = new ArrayInput($subCommandParameters);
        $returnCode = $subCommand->run($subCommandInput, $output);

        // Fail if strict mode is enabled and at least one crawl was erroneous
        if ($input->getOption('strict') && $returnCode > 0) {
            return 2;
        }

        return 0;
    }

    /**
     * @param string[]|int[] $pages
     * @param int[] $languages
     * @return \Generator<UriInterface>
     * @throws SiteNotFoundException
     */
    protected function resolveUrls(array $pages, array $languages): \Generator
    {
        foreach ($pages as $pageList) {
            foreach (GeneralUtility::intExplode(',', (string)$pageList, true) as $page) {
                $languageIds = $languages;
                if ([self::ALL_LANGUAGES] === $languageIds) {
                    $site = $this->siteFinder->getSiteByPageId($page);
                    $languageIds = array_keys($site->getLanguages());
                }
                foreach ($languageIds as $languageId) {
                    yield $this->warmupService->generateUri($page, $languageId);
                }
            }
        }
    }

    /**
     * @param string[]|int[] $sites
     * @param int[] $languages
     * @return \Generator<SiteAwareSitemap>
     * @throws SiteNotFoundException
     * @throws UnsupportedConfigurationException
     * @throws UnsupportedSiteException
     */
    protected function resolveSitemaps(array $sites, array $languages): \Generator
    {
        foreach ($sites as $siteList) {
            foreach (GeneralUtility::trimExplode(',', (string)$siteList, true) as $site) {
                if (MathUtility::canBeInterpretedAsInteger($site)) {
                    $site = $this->siteFinder->getSiteByRootPageId((int)$site);
                } else {
                    $site = $this->siteFinder->getSiteByIdentifier($site);
                }
                $languageIds = $languages;
                if ([self::ALL_LANGUAGES] === $languageIds) {
                    $languageIds = array_keys($site->getLanguages());
                }
                foreach ($languageIds as $languageId) {
                    yield $this->sitemapLocator->locateBySite($site, $site->getLanguageById($languageId));
                }
            }
        }
    }

    /**
     * @param string[]|int[] $languages
     * @return \Generator<int>
     */
    protected function resolveLanguages(array $languages): \Generator
    {
        if ($languages === []) {
            // Run cache warmup for all languages by default
            yield self::ALL_LANGUAGES;
            return;
        }

        foreach ($languages as $languageList) {
            foreach (GeneralUtility::intExplode(',', (string)$languageList, true) as $languageId) {
                yield $languageId;
            }
        }
    }
}
