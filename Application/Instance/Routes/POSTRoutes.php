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

namespace Application\Instance\Config\Routes;

// The place for your routes. This location is processed first, before loading Addon Routes and Main Routes.

// Use ```composer exec cleandeck-routes``` to get an updated list of active routes and adjust your routing
//  below as required.

// This is your control center for the routes of your application:
//  - add custom routes to your application
//  - override selected main routes and/or addon routes by using the same route uri and modifying the rest of the parameters
//  - exclude selected main routes and/or addon routes by using $routes->add404([..])

// For examples see Framework/Routes/Main/POSTRoutes.php.

use Framework\Libraries\Routes\RouteCollection;

if (\defined('CLEANDECK_LIST_ROUTES')) {
    // RouteCollection class used by utility 'cleandeck-routes' (composer exec cleandeck-routes).
    if (!isset($routes)) {
        $routes = new \Framework\Support\Utils\RouteCollection();
    }
} else {
    // RouteCollection class used for normal routing.
    $routes = new RouteCollection();
}

// For the best performance, frequently used routes should be positioned first below.

// Add your POST routes i.e. $routes->add(...)
