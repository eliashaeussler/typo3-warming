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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\EventListener;

use EliasHaeussler\Typo3Warming as Src;
use EliasHaeussler\Typo3Warming\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * SiteConfigurationListenerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\EventListener\SiteConfigurationListener::class)]
final class SiteConfigurationListenerTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'warming',
    ];

    protected bool $initializeDatabase = false;

    private Core\Cache\Frontend\PhpFrontend $cache;
    private Tests\Unit\Fixtures\DummySiteFinder $siteFinder;
    private Src\EventListener\SiteConfigurationListener $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->get('cache.warming');
        $this->siteFinder = new Tests\Unit\Fixtures\DummySiteFinder();
        $this->subject = new Src\EventListener\SiteConfigurationListener(
            $this->get(Src\Cache\SitemapsCache::class),
            $this->siteFinder,
        );

        $this->cache->set(
            'foo',
            sprintf(
                'return %s;',
                var_export(
                    [
                        0 => [
                            'https://www.example.com/baz',
                            'https://www.example.com/bar',
                        ],
                    ],
                    true,
                ),
            ),
        );
    }

    #[Framework\Attributes\Test]
    public function invokeDoesNothingIfGivenSiteDoesNotExist(): void
    {
        $event = new Core\Configuration\Event\SiteConfigurationBeforeWriteEvent('foo', []);

        ($this->subject)($event);

        self::assertTrue($this->cache->has('foo'));
    }

    #[Framework\Attributes\Test]
    public function invokeRemovesSitemapsCache(): void
    {
        $event = new Core\Configuration\Event\SiteConfigurationBeforeWriteEvent('foo', []);

        $this->siteFinder->expectedSite = new Core\Site\Entity\Site('foo', 1, []);

        ($this->subject)($event);

        self::assertFalse($this->cache->has('foo'));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->cache->flush();
    }
}
