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

namespace EliasHaeussler\Typo3Warming\DataProcessing;

use EliasHaeussler\CacheWarmup;
use EliasHaeussler\Typo3Warming\Extension;
use EliasHaeussler\Typo3Warming\View;
use GuzzleHttp\RequestOptions;
use TYPO3\CMS\Backend;
use TYPO3\CMS\Core;
use TYPO3\CMS\Fluid;
use TYPO3\CMS\T3editor;

/**
 * ExtensionConfigurationProcessor
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 *
 * @phpstan-type ExtensionConfiguration array{fieldName: string, fieldValue: string|null}
 */
final class ExtensionConfigurationProcessor
{
    private const EXPECTED_INTERFACES = [
        'crawler' => CacheWarmup\Crawler\Crawler::class,
        'verboseCrawler' => CacheWarmup\Crawler\VerboseCrawler::class,
    ];
    private const TAG_LIST_VALIDATIONS = [
        'exclude' => 'tx_warming_validate_exclude_pattern',
    ];

    /**
     * @var array{type: 'object', properties: array<string, array{}>, additionalProperties: false}|null
     */
    private static ?array $guzzleRequestOptionsSchema = null;
    private static bool $codeEditorLoaded = false;

    private readonly CacheWarmup\Crawler\Strategy\CrawlingStrategyFactory $crawlingStrategyFactory;
    private readonly Core\Configuration\ExtensionConfiguration $extensionConfiguration;
    private readonly View\TemplateRenderer $templateRenderer;

    public function __construct()
    {
        // DI is not possible here because we're in context of the failsafe container
        $this->crawlingStrategyFactory = new CacheWarmup\Crawler\Strategy\CrawlingStrategyFactory();
        $this->extensionConfiguration = Core\Utility\GeneralUtility::makeInstance(Core\Configuration\ExtensionConfiguration::class);
        $this->templateRenderer = new View\TemplateRenderer(
            Core\Utility\GeneralUtility::makeInstance(Fluid\Core\Rendering\RenderingContextFactory::class)
        );
    }

    /**
     * @param ExtensionConfiguration $params
     */
    public function processJson(array $params): string
    {
        $fieldName = $params['fieldName'];
        $variables = [
            'fieldName' => $fieldName,
            'fieldValue' => $this->extensionConfiguration->get(Extension::KEY, $fieldName),
            'codeEditorLoaded' => self::$codeEditorLoaded,
        ];

        // Set flag to avoid loading code editor multiple times
        self::$codeEditorLoaded = true;

        if (class_exists(T3editor\T3editor::class)) {
            $this->resolveLegacyT3EditorVariables($fieldName, $variables);
        } elseif (class_exists(Backend\CodeEditor\CodeEditor::class)) {
            $this->resolveCodeEditorVariables($fieldName, $variables);
        }

        return $this->templateRenderer->render('ExtensionConfiguration/JsonValue', $variables);
    }

    /**
     * @param ExtensionConfiguration $params
     */
    public function processCrawlerFqcn(array $params): string
    {
        $fieldName = $params['fieldName'];
        $fieldValue = $this->extensionConfiguration->get(Extension::KEY, $fieldName);

        return $this->templateRenderer->render('ExtensionConfiguration/CrawlerFqcnValue', [
            'fieldName' => $fieldName,
            'fieldValue' => $fieldValue,
            'expectedInterface' => self::EXPECTED_INTERFACES[$fieldName] ?? null,
        ]);
    }

    /**
     * @param ExtensionConfiguration $params
     */
    public function processCrawlingStrategy(array $params): string
    {
        $fieldName = $params['fieldName'];
        $fieldValue = trim((string)$this->extensionConfiguration->get(Extension::KEY, $fieldName));
        $strategies = $this->crawlingStrategyFactory->getAll();

        // Make sure at least currently selected strategy is selectable in install tool
        // (in backend context, all available strategies are injected using JavaScript)
        if ($fieldValue !== '' && !in_array($fieldValue, $strategies, true)) {
            $strategies[] = $fieldValue;
        }

        return $this->templateRenderer->render('ExtensionConfiguration/CrawlingStrategyValue', [
            'fieldName' => $fieldName,
            'fieldValue' => $fieldValue,
            'strategies' => $strategies,
        ]);
    }

    /**
     * @param ExtensionConfiguration $params
     */
    public function processTagList(array $params): string
    {
        $fieldName = $params['fieldName'];
        $fieldValue = $this->extensionConfiguration->get(Extension::KEY, $fieldName);

        return $this->templateRenderer->render('ExtensionConfiguration/TagListValue', [
            'fieldName' => $fieldName,
            'fieldValue' => $fieldValue,
            'validation' => self::TAG_LIST_VALIDATIONS[$fieldName] ?? null,
        ]);
    }

    /**
     * @param array<string, mixed> $variables
     *
     * @todo Remove once support for TYPO3 v12 is dropped.
     */
    private function resolveLegacyT3EditorVariables(string $fieldName, array &$variables): void
    {
        /* @phpstan-ignore class.notFound */
        $t3editor = Core\Utility\GeneralUtility::makeInstance(T3editor\T3editor::class);
        /* @phpstan-ignore class.notFound */
        $t3editor->registerConfiguration();

        /* @phpstan-ignore class.notFound */
        $addonRegistry = Core\Utility\GeneralUtility::makeInstance(T3editor\Registry\AddonRegistry::class);
        $addons = [];

        /* @phpstan-ignore class.notFound */
        $modeRegistry = Core\Utility\GeneralUtility::makeInstance(T3editor\Registry\ModeRegistry::class);
        /* @phpstan-ignore class.notFound */
        $mode = $modeRegistry->getByFileExtension('json')->getModule();

        /* @phpstan-ignore class.notFound */
        foreach ($addonRegistry->getAddons() as $addon) {
            $module = $addon->getModule();
            if ($module !== null) {
                $addons[] = $module;
            }
        }

        $jsonSchema = $this->buildJsonSchemaForField($fieldName);

        if ($jsonSchema !== null) {
            $addons[] = Core\Page\JavaScriptModuleInstruction::create(
                '@eliashaeussler/typo3-warming/backend/extension-configuration.js',
            )->invoke('jsonSchema', $jsonSchema);
        }

        $variables['codeEditor'] = [
            'mode' => Core\Utility\GeneralUtility::jsonEncodeForHtmlAttribute($mode, false),
            'addons' => Core\Utility\GeneralUtility::jsonEncodeForHtmlAttribute($addons, false),
            'jsModule' => '@typo3/t3editor/element/code-mirror-element.js',
        ];
    }

    /**
     * @param array<string, mixed> $variables
     */
    private function resolveCodeEditorVariables(string $fieldName, array &$variables): void
    {
        $codeEditor = Core\Utility\GeneralUtility::makeInstance(Backend\CodeEditor\CodeEditor::class);
        $codeEditor->registerConfiguration();

        $addonRegistry = Core\Utility\GeneralUtility::makeInstance(Backend\CodeEditor\Registry\AddonRegistry::class);
        $addons = [];

        $modeRegistry = Core\Utility\GeneralUtility::makeInstance(Backend\CodeEditor\Registry\ModeRegistry::class);
        $mode = $modeRegistry->getByFileExtension('json')->getModule();

        foreach ($addonRegistry->getAddons() as $addon) {
            $module = $addon->getModule();
            if ($module !== null) {
                $addons[] = $module;
            }
        }

        $jsonSchema = $this->buildJsonSchemaForField($fieldName);

        if ($jsonSchema !== null) {
            $addons[] = Core\Page\JavaScriptModuleInstruction::create(
                '@eliashaeussler/typo3-warming/backend/extension-configuration.js',
            )->invoke('jsonSchema', $jsonSchema);
        }

        $variables['codeEditor'] = [
            'mode' => Core\Utility\GeneralUtility::jsonEncodeForHtmlAttribute($mode, false),
            'addons' => Core\Utility\GeneralUtility::jsonEncodeForHtmlAttribute($addons, false),
            'jsModule' => '@typo3/backend/code-editor/element/code-mirror-element.js',
        ];
    }

    private function buildJsonSchemaForField(string $fieldName): ?string
    {
        $filename = sprintf('EXT:warming/Resources/Private/JsonSchema/%s.json', $fieldName);
        $filename = Core\Utility\GeneralUtility::getFileAbsFileName($filename);

        if ($filename === '' || !file_exists($filename)) {
            return null;
        }

        try {
            $json = json_decode((string)file_get_contents($filename), true, 10, JSON_THROW_ON_ERROR);
            $json['definitions']['requestOptions'] = $this->createGuzzleRequestOptionsSchema();

            return json_encode($json, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }
    }

    /**
     * @return array{type: 'object', properties: array<string, array{}>, additionalProperties: false}
     */
    private function createGuzzleRequestOptionsSchema(): array
    {
        if (self::$guzzleRequestOptionsSchema !== null) {
            return self::$guzzleRequestOptionsSchema;
        }

        $reflection = new \ReflectionClass(RequestOptions::class);
        $constantReflections = $reflection->getReflectionConstants();
        $schema = [
            'type' => 'object',
            'properties' => [],
            'additionalProperties' => false,
        ];

        foreach ($constantReflections as $constantReflection) {
            $schema['properties'][$constantReflection->getValue()] = [];
        }

        ksort($schema['properties']);

        return self::$guzzleRequestOptionsSchema = $schema;
    }
}
