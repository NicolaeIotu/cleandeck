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

final class ChangePasswordController
{
    public function index(): void
    {
        echo new HtmlView('main/page-content/authenticated/user/account_change_password');
    }


    public function remote_request(): void
    {
        // form validation
        $validator = new Validator([
            'password_existing' => ['password', 'label' => 'Existing Password'],
            'new_password' => ['password'],
            'new_password_confirmation' => ['password', 'matches' => 'new_password'],
        ]);
        if ($validator->redirectOnError(UrlUtils::baseUrl('/change-password'))) {
            return;
        }

        // get the email
        $ca_request = new CARequest();
        $caResponse = $ca_request
            ->exec('GET', '/user/minimal-details');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectToErrorPage();
            return;
        }

        // get the email
        $response_body = $caResponse->getBody();
        $response_body_array = \json_decode($response_body, true, 2);

        if (!isset($response_body_array, $response_body_array['email'])) {
            // invalid response body
            CookieMessengerWriter::setMessage(
                500,
                true,
                'Could not complete preparations for changing the password'
            );
            HttpResponse::redirectToErrorPage();
            return;
        }

        $final_request_body_array = $_POST;
        $final_request_body_array['primary_email'] = $response_body_array['email'];

        // hash passwords
        try {
            $password_existing_hash = PasswordUtils::hash($_POST['password_existing']);
            $new_password_hash = PasswordUtils::hash($_POST['new_password']);
            $new_password_confirmation_hash = PasswordUtils::hash($_POST['new_password_confirmation']);
        } catch (\Exception $exception) {
            CookieMessengerWriter::setMessage(null, true, $exception->getMessage());
            HttpResponse::redirectTo(UrlUtils::baseUrl('/login'));
            return;
        }

        $final_request_body_array['password_existing'] = $password_existing_hash;
        $final_request_body_array['new_password'] = $new_password_hash;
        $final_request_body_array['new_password_confirmation'] = $new_password_confirmation_hash;


        // start change password procedure
        $ca_request = new CARequest();
        $change_password_response = $ca_request
            ->setBody($final_request_body_array)
            ->exec('PUT', '/password');
        if ($change_password_response->hasError()) {
            CookieMessengerWriter::setMessage(
                $change_password_response->getStatusCode(),
                true,
                $change_password_response->getBody()
            );
            HttpResponse::redirectTo(UrlUtils::baseUrl('/change-password'));
        } else {
            CookieMessengerWriter::setMessage(
                null,
                false,
                'Password changed. Next login must be done using the updated password.'
            );
            HttpResponse::redirectTo(UrlUtils::baseUrl('/user'));
        }
    }
}
