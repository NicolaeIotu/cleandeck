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

namespace Framework\Libraries\Routes;

use Application\Instance\Config\Routes\UserRoutes;
use Framework\Libraries\Http\HttpRequest;
use Framework\Libraries\Http\HttpResponse;

final class Router
{
    /**
     * @throws \Exception
     */
    public function __construct()
    {
        // Speed optimizations:
        //   1. load GET definitions, OR load POST definitions
        //   2. when a route is found: serve content and interrupt further processing
        $request_method = HttpRequest::getMethod();
        $request_is_get = $request_method === 'GET';


        HttpResponse::setHeaders([
            'Server' => 'anonymous',
        ]);

        if ($request_is_get) {
            // GET
            // Check first user routes
            UserRoutes::load(true);
            // check addon routes if any
            $this->checkAddonRoutes(true);
            // lastly check main routes
            require_once CLEANDECK_MAIN_ROUTES_PATH . '/GETRoutes.php';
        } else {
            // POST
            // Check first user routes
            UserRoutes::load(false);
            // check addon routes if any
            $this->checkAddonRoutes(false);
            // lastly check main routes
            require_once CLEANDECK_MAIN_ROUTES_PATH . '/POSTRoutes.php';
        }
        // If the path is found then the script terminates before reaching this point.

        // No match for this request => error 404
        if (HttpRequest::isAJAX() ||
            !$request_is_get) {
            HttpResponse::send(404);
        } else {
            new Response404();
        }
        // The script must terminate here
        exit(1);
    }

    private function checkAddonRoutes(bool $request_is_get): void
    {
        $addons_string = \env('cleandeck.addons', '');
        if (\is_string($addons_string) && $addons_string !== '') {
            $addons = \explode(',', $addons_string);
            foreach ($addons as $addon) {
                require_once CLEANDECK_ADDON_ROUTES_PATH . '/' . \ucfirst($addon) .
                    ($request_is_get ? '/GETRoutes.php' : '/POSTRoutes.php');
            }
        }
    }
}
