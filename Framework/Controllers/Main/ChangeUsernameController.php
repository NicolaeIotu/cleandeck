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
use Framework\Libraries\View\HtmlView;

final class ChangeUsernameController
{
    public function index(): void
    {
        echo new HtmlView('main/page-content/authenticated/user/account_change_username');
    }


    public function remote_request(): void
    {
        // form validation
        $validator = new Validator([
            'new_username' => ['min_length' => 2, 'max_length' => 64],
        ]);
        if ($validator->redirectOnError(UrlUtils::baseUrl('/change-username'))) {
            return;
        }


        // start procedure
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($_POST)
            ->exec('PATCH', '/username');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectToErrorPage();
            return;
        }

        // success!
        // get activation_hash_new_primary_email
        $response_body = $caResponse->getBody();
        $response_body_array = \json_decode($response_body, true, 2);

        if (!isset($response_body_array, $response_body_array['new_username'])) {
            // invalid response body ... redirect to / which should check further
            HttpResponse::redirectTo(UrlUtils::baseUrl('/login'));
        } else {
            CookieMessengerWriter::setMessage(
                null,
                false,
                'New Username: ' . $response_body_array['new_username']
            );
            HttpResponse::redirectTo(UrlUtils::baseUrl('/user'));
        }
    }
}
