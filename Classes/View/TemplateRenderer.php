<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2025 Elias Häußler <elias@haeussler.dev>
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

use TYPO3\CMS\Core;
use TYPO3\CMS\Fluid;

/**
 * TemplateRenderer
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class TemplateRenderer
{
    private Fluid\Core\Rendering\RenderingContext $renderingContext;

    public function __construct(
        private Fluid\Core\Rendering\RenderingContextFactory $renderingContextFactory,
    ) {
        $this->renderingContext = $this->createRenderingContext();
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function render(string $templatePath, array $variables = []): string
    {
        if ((new Core\Information\Typo3Version())->getMajorVersion() >= 13) {
            $templatePaths = $this->renderingContext->getTemplatePaths();
            $data = new Core\View\ViewFactoryData(
                templateRootPaths: $templatePaths->getTemplateRootPaths(),
                partialRootPaths: $templatePaths->getPartialRootPaths(),
                layoutRootPaths: $templatePaths->getLayoutRootPaths(),
            );
            $viewFactory = Core\Utility\GeneralUtility::makeInstance(Core\View\ViewFactoryInterface::class);
            $view = $viewFactory->create($data);
        } else {
            // @todo Remove once support for TYPO3 v12 is dropped
            /* @phpstan-ignore classConstant.deprecatedClass */
            $view = Core\Utility\GeneralUtility::makeInstance(Fluid\View\StandaloneView::class, $this->renderingContext);
        }

        $view->assignMultiple($variables);

        return $view->render($templatePath);
    }

    private function createRenderingContext(): Fluid\Core\Rendering\RenderingContext
    {
        $rootPath = dirname(__DIR__, 2) . '/Resources/Private';

        return $this->renderingContextFactory->create([
            'templateRootPaths' => [$rootPath . '/Templates'],
            'partialRootPaths' => [$rootPath . '/Partials'],
            'layoutRootPaths' => [$rootPath . '/Layouts'],
        ]);
    }
}
