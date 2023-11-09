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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3Warming\Log\Writer;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming\Domain;
use EliasHaeussler\Typo3Warming\Enums;
use GuzzleHttp\Exception;
use Psr\Log;
use TYPO3\CMS\Core;

/**
 * DatabaseWriter
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class DatabaseWriter extends Core\Log\Writer\AbstractWriter
{
    private readonly Core\Database\Connection $connection;

    /**
     * @param array<string, mixed> $options
     * @throws Core\Log\Exception\InvalidLogWriterConfigurationException
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->connection = Core\Utility\GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)
            ->getConnectionForTable(Domain\Model\Log::TABLE_NAME);
    }

    public function writeLog(Core\Log\LogRecord $record): self
    {
        $url = $record->getData()['url'] ?? '';
        /** @var Log\LogLevel::* $level */
        $level = $record->getLevel();

        [$sitemap, $site, $siteLanguage] = $this->resolveSiteRelatedProperties($url);

        $this->connection->insert(
            Domain\Model\Log::TABLE_NAME,
            [
                'request_id' => $record->getRequestId(),
                'date' => number_format($record->getCreated(), thousands_separator: ''),
                'url' => (string)$url,
                'message' => $this->formatMessage($record),
                'state' => Enums\WarmupState::fromLogLevel($level)->value,
                'sitemap' => $sitemap,
                'site' => $site,
                'site_language' => $siteLanguage,
            ],
        );

        return $this;
    }

    private function formatMessage(Core\Log\LogRecord $record): string
    {
        $exception = $record->getData()['exception'] ?? null;

        if (!($exception instanceof Exception\RequestException) || $exception->getResponse() === null) {
            return $this->interpolate($record->getMessage(), $record->getData());
        }

        return (string)$exception->getResponse()->getBody();
    }

    /**
     * @return array{string|null, int|null, int|null}
     */
    private function resolveSiteRelatedProperties(mixed $url): array
    {
        if (!($url instanceof CacheWarmup\Sitemap\Url)) {
            return [null, null, null];
        }

        $sitemap = $url->getRootOrigin();

        if ($sitemap === null) {
            return [null, null, null];
        }

        $sitemapUri = (string)$sitemap->getUri();

        if (!($sitemap instanceof Domain\Model\SiteAwareSitemap)) {
            return [$sitemapUri, null, null];
        }

        return [
            $sitemapUri,
            $sitemap->getSite()->getRootPageId(),
            $sitemap->getSiteLanguage()->getLanguageId(),
        ];
    }
}
