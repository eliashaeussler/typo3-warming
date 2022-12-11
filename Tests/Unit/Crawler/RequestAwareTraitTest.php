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

namespace EliasHaeussler\Typo3Warming\Tests\Unit\Crawler;

use EliasHaeussler\CacheWarmup\CrawlingState;
use EliasHaeussler\Typo3Warming\Request\WarmupRequest;
use EliasHaeussler\Typo3Warming\Tests\Unit\Fixtures\RequestAwareTraitTestClass;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * RequestAwareTraitTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class RequestAwareTraitTest extends UnitTestCase
{
    protected RequestAwareTraitTestClass $subject;
    protected WarmupRequest $warmupRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new RequestAwareTraitTestClass();
        $this->warmupRequest = new WarmupRequest();
    }

    /**
     * @test
     */
    public function setRequestAppliesGivenWarmupRequest(): void
    {
        self::assertNull($this->subject->getRequest());

        $this->subject->setRequest($this->warmupRequest);

        self::assertSame($this->warmupRequest, $this->subject->getRequest());
    }

    /**
     * @test
     */
    public function updateRequestAddsCrawlingStateToAttachedRequest(): void
    {
        $crawlingState = CrawlingState::createSuccessful(new Uri('https://example.com'));

        $this->subject->runUpdateRequest($crawlingState);

        self::assertSame([], $this->warmupRequest->getCrawlingStates());

        $this->subject->setRequest($this->warmupRequest);
        $this->subject->runUpdateRequest($crawlingState);

        self::assertSame([$crawlingState], $this->warmupRequest->getCrawlingStates());
    }
}
