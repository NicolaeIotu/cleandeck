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

namespace Framework\Controllers\Main;

use Framework\Libraries\CSRF\CSRF;
use Framework\Libraries\Http\HttpRequest;
use Framework\Libraries\Http\HttpResponse;

final class CSRFController
{
    public function ajax_request_get_csrf(): void
    {
        if (HttpRequest::isAJAX()) {
            HttpResponse::setHeaders([
                'content-type' => 'application/json; charset=UTF-8',
            ]);
        } else {
            HttpResponse::send(400, 'Expecting an AJAX request');
            return;
        }

        $csrf_hash = CSRF::init();
        if ($csrf_hash === false) {
            HttpResponse::send(500, 'CSRF hash failure');
            return;
        }

        HttpResponse::noCache();

        HttpResponse::send(200, \base64_encode($csrf_hash));
    }
}
