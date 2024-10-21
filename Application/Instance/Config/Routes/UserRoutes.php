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

class UserRoutes
{
    /**
     * You can modify this function in order to optimize and/or speed up the way this application
     *  loads your own routes.
     * By default, this loader is looking for 2 route files for each type of request GET or POST.
     * @param bool $request_is_get
     * @return void
     */
    public static function load(bool $request_is_get): void
    {
        if ($request_is_get) {
            require_once CLEANDECK_USER_ROUTES_PATH . '/GETRoutes.php';
            if (\file_exists(CLEANDECK_USER_ROUTES_PATH . '/GETRoutes_1.php')) {
                include_once CLEANDECK_USER_ROUTES_PATH . '/GETRoutes_1.php';
            }
        } else {
            require_once CLEANDECK_USER_ROUTES_PATH . '/POSTRoutes.php';
            if (\file_exists(CLEANDECK_USER_ROUTES_PATH . '/POSTRoutes_1.php')) {
                include_once CLEANDECK_USER_ROUTES_PATH . '/POSTRoutes_1.php';
            }
        }
    }
}
