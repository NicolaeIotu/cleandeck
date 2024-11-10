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

/////////////////
// DIRECTORIES //
/////////////////

defined('CLEANDECK_ROOT_PATH') || define('CLEANDECK_ROOT_PATH', realpath(__DIR__ . '/../..'));

defined('CLEANDECK_VENDOR_PATH') || define('CLEANDECK_VENDOR_PATH', CLEANDECK_ROOT_PATH . '/vendor');

defined('CLEANDECK_FRAMEWORK_PATH') || define('CLEANDECK_FRAMEWORK_PATH', CLEANDECK_ROOT_PATH . '/Framework');

defined('CLEANDECK_APP_PATH') || define('CLEANDECK_APP_PATH', CLEANDECK_ROOT_PATH . '/Application');

defined('CLEANDECK_PUBLIC_PATH') || define('CLEANDECK_PUBLIC_PATH', CLEANDECK_APP_PATH . '/public');

defined('CLEANDECK_WRITE_PATH') || define('CLEANDECK_WRITE_PATH', CLEANDECK_APP_PATH . '/writable');

defined('CLEANDECK_DYNAMIC_PATH') || define('CLEANDECK_DYNAMIC_PATH', CLEANDECK_APP_PATH . '/public/misc');

defined('CLEANDECK_USER_VIEWS_PATH') || define('CLEANDECK_USER_VIEWS_PATH', CLEANDECK_APP_PATH . '/Instance/Views');
defined('CLEANDECK_FRAMEWORK_VIEWS_PATH') || define('CLEANDECK_FRAMEWORK_VIEWS_PATH', CLEANDECK_FRAMEWORK_PATH . '/Views');

defined('CLEANDECK_MAIN_ROUTES_PATH') || define('CLEANDECK_MAIN_ROUTES_PATH', CLEANDECK_FRAMEWORK_PATH . '/Routes/Main');
defined('CLEANDECK_ADDON_ROUTES_PATH') || define('CLEANDECK_ADDON_ROUTES_PATH', CLEANDECK_FRAMEWORK_PATH . '/Routes/Addon');
defined('CLEANDECK_USER_ROUTES_PATH') || define('CLEANDECK_USER_ROUTES_PATH', CLEANDECK_APP_PATH . '/Instance/Routes');

///////////
// FILES //
///////////
