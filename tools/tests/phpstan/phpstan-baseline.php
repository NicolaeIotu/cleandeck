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

$ignoreErrors = [];

defined('CLEANDECK_TESTS_PATH') || define('CLEANDECK_TESTS_PATH', dirname(__DIR__, 3) . '/tests');

/*
 * Add ignored errors below.
 */
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with null will always evaluate to false\\.$#',
    'count' => 1,
    'path' => CLEANDECK_TESTS_PATH . '/Framework/Libraries/Cookie/CookieMessengerReaderTests.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
