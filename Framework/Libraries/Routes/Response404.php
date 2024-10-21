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

use Framework\Controllers\Main\ErrorController;
use Framework\Libraries\Http\HttpRequest;
use Framework\Libraries\Http\HttpResponse;
use Framework\Middleware\Main\AAAInit;
use Framework\Middleware\Main\ApplicationStatusJWT;
use Framework\Middleware\Main\CSP;
use Framework\Middleware\Main\HttpCaching;
use Framework\Middleware\Main\UserDetails;

class Response404
{
    public function __construct()
    {
        if (HttpRequest::isAJAX()) {
            HttpResponse::setHeaders([
                'content-type' => 'application/json; charset=UTF-8',
            ]);
            HttpResponse::send(404);
            return;
        }

        \http_response_code(404);
        new RouteResponse(
            ErrorController::class,
            'error404',
            null,
            [
                AAAInit::class,
                ApplicationStatusJWT::class,
                HttpCaching::class,
                UserDetails::class,
                CSP::class,
            ]
        );
        exit(1);
    }
}
