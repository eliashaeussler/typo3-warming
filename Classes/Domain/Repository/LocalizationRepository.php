<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2026 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\Domain\Repository;

use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;

/**
 * LocalizationRepository
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 *
 * @todo Remove once support for TYPO3 v13 is dropped
 */
final readonly class LocalizationRepository
{
    private Core\Information\Typo3Version $typo3Version;

    public function __construct(
        private Backend\Domain\Repository\Localization\LocalizationRepository $baseRepository,
        private Core\Domain\RecordFactory $recordFactory,
    ) {
        $this->typo3Version = new Core\Information\Typo3Version();
    }

    public function getPageTranslation(int $pageId, int $languageId): ?Core\Domain\RawRecord
    {
        if ($languageId <= 0) {
            return null;
        }

        if ($this->typo3Version->getMajorVersion() >= 14) {
            /* @phpstan-ignore method.notFound */
            return $this->baseRepository->getRecordTranslation('pages', $pageId, $languageId);
        }

        // @todo Remove everything below once support for TYPO3 v13 is dropped
        $pageTranslations = Backend\Utility\BackendUtility::getRecordLocalization('pages', $pageId, $languageId);

        if ($pageTranslations !== false && $pageTranslations !== []) {
            return $this->recordFactory->createRawRecord('pages', $pageTranslations[0]);
        }

        return null;
    }
}
