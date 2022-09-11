<?php

declare(strict_types=1);

$header = <<<EOM
This file is part of the TYPO3 CMS extension "warming".

Copyright (C) %d Elias Häußler <elias@haeussler.dev>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <https://www.gnu.org/licenses/>.
EOM;

$config = \TYPO3\CodingStandards\CsFixerConfig::create()
    ->setHeader(sprintf($header, date('Y')), true)
    ->addRules([
        'native_function_invocation' => true,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
    ]);

$finder = $config->getFinder()
    ->in(__DIR__)
    ->ignoreVCSIgnored(true);

return $config;
