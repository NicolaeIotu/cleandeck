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

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

use Framework\Libraries\Utils\DotEnv;

// Load the Constants
require_once __DIR__ . '/../../../Framework/Config/constants.php';
// Composer autoload
require_once CLEANDECK_VENDOR_PATH . '/autoload.php';
// Set environment
DotEnv::setEnvironment(CLEANDECK_ROOT_PATH . '/env.ini');
// Add common global functions
require_once CLEANDECK_FRAMEWORK_PATH . '/Libraries/common.php';
// Constants which depend on user settings
require_once CLEANDECK_FRAMEWORK_PATH . '/Config/dynamic-constants.php';

// additional constants for tests
defined('CLEANDECK_TESTS_PATH') || define('CLEANDECK_TESTS_PATH', CLEANDECK_ROOT_PATH . '/tests');
