<?php

/*
 * CleanDeck for CMD-Auth (https://link133.com) and other similar applications
 *
 * Copyright (c) 2023-2024 Iotu Nicolae, nicolae.g.iotu@link133.com
 * Licensed under the terms of the MIT License (MIT)
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

/**
 * Ini requirements:
 *   - 'variables_order' => 'EGPCS'
 */

use Framework\Libraries\Routes\Router;
use Framework\Libraries\Utils\DotEnv;

// Not checking PHP version. Use the version indicated by composer.json.

// Adjust current working directory if required
if (getcwd() !== __DIR__) {
    chdir(__DIR__);
}


// Load the Constants
require_once __DIR__ . '/../../Framework/Config/constants.php';
// Composer autoload
require_once CLEANDECK_VENDOR_PATH . '/autoload.php';
// Handle .env.ini
DotEnv::setEnvironment(CLEANDECK_ROOT_PATH . '/.env.ini');
// Add common global functions
require_once CLEANDECK_FRAMEWORK_PATH . '/Libraries/common.php';
// Constants which depend on user settings
require_once CLEANDECK_FRAMEWORK_PATH . '/Config/dynamic-constants.php';


// Launch the application
new Router();
