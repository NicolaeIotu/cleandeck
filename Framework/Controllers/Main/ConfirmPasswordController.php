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
use Framework\Libraries\Utils\PasswordUtils;
use Framework\Libraries\Utils\UrlUtils;
use Framework\Libraries\Validator\Validator;
use Framework\Libraries\View\HtmlView;

final class ConfirmPasswordController
{
    public function index(): void
    {
        echo new HtmlView('main/page-content/authenticated/user/account_confirm_password');
    }


    public function remote_request(): void
    {
        $redirect_on_error_url = UrlUtils::baseUrl('/confirm-password');

        // form validation
        $validator = new Validator([
            'password' => ['password'],
        ]);
        if ($validator->redirectOnError($redirect_on_error_url)) {
            return;
        }

        try {
            $password_hash = PasswordUtils::hash($_POST['password']);
        } catch (\Exception $exception) {
            CookieMessengerWriter::setMessage(null, true, $exception->getMessage());
            HttpResponse::redirectTo(UrlUtils::baseUrl('/confirm-password'));
            return;
        }

        $ca_body = $_POST;
        $ca_body['password'] = $password_hash;

        // start procedure
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($ca_body)
            ->exec('PUT', '/password/confirm');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectTo($redirect_on_error_url);
        } else {
            // success!
            CookieMessengerWriter::setMessage(null, false, 'Password confirmed successfully!');
            HttpResponse::redirectTo(UrlUtils::baseUrl('/user'));
        }
    }
}
