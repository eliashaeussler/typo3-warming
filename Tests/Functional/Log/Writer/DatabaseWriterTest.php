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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Log\Writer;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use GuzzleHttp\Exception;
use PHPUnit\Framework;
use Psr\Log;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * DatabaseWriterTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Log\Writer\DatabaseWriter::class)]
final class DatabaseWriterTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\Functional\SiteTrait;

    protected array $testExtensionsToLoad = [
        'warming',
    ];

    private Src\Log\Writer\DatabaseWriter $subject;
    private Core\Database\Connection $connection;
    private Core\Site\Entity\Site $site;
    private CacheWarmup\Sitemap\Url $uri;
    private Core\Log\LogRecord $logRecord;

    protected function setUp(): void
    {
        parent::setUp();

        $response = new Core\Http\Response();
        $response->getBody()->write('Oops, something went wrong.');
        $response->getBody()->rewind();

        $this->subject = new Src\Log\Writer\DatabaseWriter();
        $this->connection = $this->getConnectionPool()->getConnectionForTable(Src\Domain\Model\Log::TABLE_NAME);
        $this->site = $this->createSite();
        $this->uri = new CacheWarmup\Sitemap\Url(
            (string)$this->site->getBase(),
            origin: new Src\Sitemap\SiteAwareSitemap(
                new Core\Http\Uri('https://typo3-testing.local/sitemap.xml'),
                $this->site,
                $this->site->getDefaultLanguage(),
            ),
        );
        $this->logRecord = new Core\Log\LogRecord(
            Src\Log\Writer\DatabaseWriter::class,
            Log\LogLevel::ERROR,
            'Error while crawling URL {url} (exception: {exception}).',
            [
                'url' => $this->uri,
                'exception' => new Exception\RequestException(
                    'Something went wrong.',
                    new Core\Http\Request($this->site->getBase()),
                    $response,
                ),
            ],
            '123',
        );

        $this->connection->truncate(Src\Domain\Model\Log::TABLE_NAME);
    }

    #[Framework\Attributes\Test]
    public function writeLogUsesInterpolatedLogMessageAsLogMessage(): void
    {
        $exception = new \Exception('That\'s an error.');

        $this->logRecord->setData([
            'url' => $this->site->getBase(),
            'exception' => $exception,
        ]);

        $this->subject->writeLog($this->logRecord);

        $this->assertLastLogEquals([
            'message' => sprintf('Error while crawling URL https://typo3-testing.local/ (exception: %s).', $exception),
        ]);
    }

    #[Framework\Attributes\Test]
    public function writeLogUsesResponseBodyAsLogMessage(): void
    {
        $this->subject->writeLog($this->logRecord);

        $this->assertLastLogEquals([
            'message' => 'Oops, something went wrong.',
        ]);
    }

    #[Framework\Attributes\Test]
    public function writeLogIgnoresSiteRelatedPropertiesIfNoSitemapIsConfigured(): void
    {
        $logRecord = new Core\Log\LogRecord(
            Src\Log\Writer\DatabaseWriter::class,
            Log\LogLevel::INFO,
            'URL {url} was successfully crawled (status code: {status_code}).',
            [
                'url' => new CacheWarmup\Sitemap\Url((string)$this->uri),
                'status_code' => 200,
            ],
            '123',
        );

        $this->subject->writeLog($logRecord);

        $this->assertLastLogEquals([
            'sitemap' => null,
            'site' => null,
            'site_language' => null,
        ]);
    }

    #[Framework\Attributes\Test]
    public function writeLogIgnoresSiteRelatedPropertiesIfNoSiteAwareSitemapIsConfigured(): void
    {
        $this->uri->setOrigin(
            new CacheWarmup\Sitemap\Sitemap(
                new Core\Http\Uri('https://typo3-testing.local/sitemap.xml'),
            ),
        );

        $this->subject->writeLog($this->logRecord);

        $this->assertLastLogEquals([
            'sitemap' => 'https://typo3-testing.local/sitemap.xml',
            'site' => null,
            'site_language' => null,
        ]);
    }

    #[Framework\Attributes\Test]
    public function writeLogInsertsAllRelevantPropertiesIntoDatabase(): void
    {
        $this->subject->writeLog($this->logRecord);

        $this->assertLastLogEquals([
            'request_id' => '123',
            'url' => 'https://typo3-testing.local/',
            'state' => Src\Enums\WarmupState::Failed->value,
            'sitemap' => 'https://typo3-testing.local/sitemap.xml',
            'site' => 1,
            'site_language' => 0,
        ]);
    }

    /**
     * @param array<string, mixed> $expected
     */
    private function assertLastLogEquals(array $expected): void
    {
        $columns = array_keys($expected);

        self::assertSame(
            $expected,
            $this->connection->select($columns, Src\Domain\Model\Log::TABLE_NAME)->fetchAssociative(),
        );
    }
}
