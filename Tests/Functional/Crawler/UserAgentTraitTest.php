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

namespace EliasHaeussler\Typo3Warming\Tests\Functional\Crawler;

use EliasHaeussler\Typo3Warming\Configuration\Configuration;
use EliasHaeussler\Typo3Warming\Crawler\UserAgentTrait;
use EliasHaeussler\Typo3Warming\Tests\Functional\AccessibleMethodTrait;
use GuzzleHttp\Psr7\Request;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * UserAgentTraitTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class UserAgentTraitTest extends FunctionalTestCase
{
    use AccessibleMethodTrait;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/warming',
    ];

    /**
     * @var object|UserAgentTrait
     */
    protected $subject;

    /**
     * @var \ReflectionMethod
     */
    protected $reflectionMethod;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->subject, $this->reflectionMethod] = $this->getAccessibleMethodOfTrait(
            UserAgentTrait::class,
            'applyUserAgentHeader'
        );
    }

    /**
     * @test
     */
    public function applyUserAgentHeaderAddsUserAgentHeaderToRequest(): void
    {
        $configuration = GeneralUtility::makeInstance(Configuration::class);
        $request = new Request('GET', 'https://www.example.com');

        /** @var Request $actual */
        $actual = $this->reflectionMethod->invoke($this->subject, $request);

        self::assertTrue($actual->hasHeader('User-Agent'));
        self::assertSame([$configuration->getUserAgent()], $actual->getHeader('User-Agent'));
    }
}
