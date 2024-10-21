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

use Framework\Libraries\CA\CARequest;
use Framework\Libraries\Cookie\CookieMessengerWriter;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\UrlUtils;
use Framework\Libraries\Validator\Validator;

final class ActivateUserController
{
    public function remote_request(): void
    {
        $validator = new Validator([
            'email' => ['email'],
            'activation_hash' => ['hex', 'min_length' => 10, 'max_length' => 256],
        ]);
        if ($validator->redirectOnError(UrlUtils::baseUrl('/error'))) {
            return;
        }

        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($_GET)
            ->exec('PATCH', '/user/activate');

        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectToErrorPage();
        } else {
            CookieMessengerWriter::setMessage(null, false, 'Account activated successfully!');
            HttpResponse::redirectTo(UrlUtils::baseUrl('/login'));
        }
    }
}
