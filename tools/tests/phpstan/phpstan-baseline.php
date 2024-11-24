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

defined('CLEANDECK_FRAMEWORK_PATH') || define('CLEANDECK_FRAMEWORK_PATH', dirname(__DIR__, 3) . '/Framework');

/*
 * Add ignored errors below.
 */
//$ignoreErrors[] = [
//    'message' => '#^Variable \\$fileOpsQueue in isset\\(\\) is never defined\\.$#',
//    'count' => 1,
//    'path' => CLEANDECK_FRAMEWORK_PATH . '/Controllers/Main/ArticleShowController.php',
//];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
