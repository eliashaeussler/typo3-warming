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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Sitemap\Provider;

use EliasHaeussler\Typo3Warming\Sitemap\Provider\RobotsTxtProvider;
use EliasHaeussler\Typo3Warming\Sitemap\SiteAwareSitemap;
use EliasHaeussler\Typo3Warming\Tests\Unit\Fixtures\DummyRequestFactory;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * RobotsTxtProviderTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class RobotsTxtProviderTest extends UnitTestCase
{
    protected DummyRequestFactory $requestFactory;
    protected Site $site;
    protected RobotsTxtProvider $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestFactory = new DummyRequestFactory();
        $this->site = new Site('foo', 1, ['base' => 'https://www.example.com/']);
        $this->subject = new RobotsTxtProvider($this->requestFactory);
    }

    /**
     * @test
     */
    public function getReturnsNullIfNoRobotsTxtExists(): void
    {
        $this->requestFactory->responseStack[] = new \Exception();

        self::assertNull($this->subject->get($this->site));
    }

    /**
     * @test
     */
    public function getReturnsNullIfNoRobotsTxtDoesNotContainSitemapConfiguration(): void
    {
        $body = new Stream('php://temp', 'rw');
        $body->write('foo');
        $body->rewind();
        $response = new Response($body);

        $this->requestFactory->responseStack[] = $response;

        self::assertNull($this->subject->get($this->site));
    }

    /**
     * @test
     */
    public function getReturnsSitemapIfRobotsTxtContainsSitemapConfiguration(): void
    {
        $body = new Stream('php://temp', 'rw');
        $body->write('Sitemap: https://www.example.com/baz.xml');
        $body->rewind();
        $response = new Response($body);

        $this->requestFactory->responseStack[] = $response;

        $expected = new SiteAwareSitemap(new Uri('https://www.example.com/baz.xml'), $this->site);

        self::assertEquals($expected, $this->subject->get($this->site));
    }
}
