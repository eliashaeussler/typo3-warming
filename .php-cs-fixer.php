<?php

declare(strict_types=1);

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
$finder = $config->getFinder()
    ->in(__DIR__)
    ->exclude('node_modules')
    ->ignoreVCSIgnored(true);

return $config;
