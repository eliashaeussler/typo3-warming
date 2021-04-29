<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\Typo3Warming\Crawler\ConcurrentUserAgentCrawler;
use EliasHaeussler\Typo3Warming\Crawler\OutputtingUserAgentCrawler;
use EliasHaeussler\Typo3Warming\Service\CacheWarmupService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
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
    /**
     * @var CacheWarmupService
     */
    protected $warmupService;

    /**
     * @var SiteFinder
     */
    protected $siteFinder;

    public function __construct(CacheWarmupService $warmupService, SiteFinder $siteFinder, string $name = null)
    {
        parent::__construct($name);

        $this->warmupService = $warmupService;
        $this->siteFinder = $siteFinder;
    }

    protected function configure(): void
    {
        $this->setDescription('Warm up Frontend caches of single pages and/or whole sites using their XML sitemaps.');

        $this->addOption(
            'pages',
            'p',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Pages whose Frontend caches are to be warmed up.'
        );
        $this->addOption(
            'sites',
            's',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Site identifiers or root page IDs of sites whose caches are to be warmed up.'
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
        $pages = array_unique(iterator_to_array($this->resolvePages($input->getOption('pages'))));
        $sites = array_unique(iterator_to_array($this->resolveSites($input->getOption('sites'))), SORT_REGULAR);

        // Exit if neither pages nor sites are given
        if (count($pages) + count($sites) === 0) {
            $output->writeln('<error>You need to define at least one page or site.</error>');
            return 1;
        }

        $output->writeln('Running <info>cache warmup</info> by <info>Elias Häußler</info> and contributors.');

        // Initialize crawler
        if (($crawler = $this->warmupService->getCrawler()) instanceof ConcurrentUserAgentCrawler) {
            $crawler = new OutputtingUserAgentCrawler();
            $crawler->setOutput($output);
            $this->warmupService->setCrawler($crawler);
        }

        // Warmup pages and sites
        $countFailed = 0;
        $this->warmupService->warmupPages($pages);
        $countFailed += count($crawler->getFailedUrls());
        $this->warmupService->warmupSites($sites);
        $countFailed += count($crawler->getFailedUrls());

        // Fail if strict mode is enabled and at least one crawl was erroneous
        if ($input->getOption('strict') && $countFailed > 0) {
            return 2;
        }

        return 0;
    }

    /**
     * @param string[]|int[] $pages
     * @return \Generator<int>
     */
    protected function resolvePages(array $pages): \Generator
    {
        foreach ($pages as $pageList) {
            foreach (GeneralUtility::intExplode(',', $pageList, true) as $page) {
                yield $page;
            }
        }
    }

    /**
     * @param string[]|int[] $sites
     * @return \Generator<Site>
     * @throws SiteNotFoundException
     */
    protected function resolveSites(array $sites): \Generator
    {
        foreach ($sites as $siteList) {
            foreach (GeneralUtility::trimExplode(',', $siteList, true) as $site) {
                if (MathUtility::canBeInterpretedAsInteger($site)) {
                    yield $this->siteFinder->getSiteByRootPageId((int)$site);
                } else {
                    yield $this->siteFinder->getSiteByIdentifier($site);
                }
            }
        }
    }
}
