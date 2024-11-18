<?php declare(strict_types=1);

/*
 * CleanDeck for CMD-Auth (https://link133.com) and other similar applications
 *
 * Copyright (c) 2023-2024 Iotu Nicolae, nicolae.g.iotu@link133.com
 * Licensed under the terms of the MIT License (MIT)
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

defined('CLEANDECK_APP_PATH') || define('CLEANDECK_APP_PATH', dirname(__DIR__, 3) . '/Application');

// Ignored errors, excluded paths and other PHPStan parameters can be declared in up to 3 files:
//  - phpstan-parameters-1.php
//  - phpstan-parameters-2.php
//  - phpstan-parameters-3.php

$i = 0;
while (++$i <= 3 && file_exists(__DIR__ . '/phpstan-parameters-' . $i . '.php')) {
    // IMPORTANT! Don't use here include_once or require_once.
    include __DIR__ . '/phpstan-parameters-' . $i . '.php';
}

return [
    'parameters' => [
        'ignoreErrors' => $ignoreErrors ?? null,
        'excludePaths' => $excludePaths ?? null,
    ]
];
