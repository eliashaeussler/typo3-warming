<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3Warming\View;

use EliasHaeussler\Typo3Warming\Extension;
use TYPO3\CMS\Core;
use TYPO3\CMS\Fluid;

/**
 * TemplateRenderer
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class TemplateRenderer
{
    /**
     * @param array<string, mixed> $variables
     */
    public function render(string $templatePath, array $variables = []): string
    {
        $view = Core\Utility\GeneralUtility::makeInstance(Fluid\View\StandaloneView::class);
        $view->getRenderingContext()->getTemplatePaths()->fillDefaultsByPackageName(Extension::KEY);
        $view->assignMultiple($variables);

        return $view->render($templatePath);
    }
}
