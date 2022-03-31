<?php

declare(strict_types=1);

$config = \TYPO3\CodingStandards\CsFixerConfig::create()
    ->addRules([
        'native_function_invocation' => true,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
    ]);

$finder = $config->getFinder()
    ->in(__DIR__)
    ->ignoreVCSIgnored(true);

return $config;
