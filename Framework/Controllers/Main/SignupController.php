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
use Framework\Libraries\Email\EmailTemplates;
use Framework\Libraries\Email\SignupEmail;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\PasswordUtils;
use Framework\Libraries\Utils\UrlUtils;
use Framework\Libraries\Validator\Validator;
use Framework\Libraries\View\HtmlView;

final class SignupController
{
    public function index(): void
    {
        $data = [
            'seo_description' => 'Signup',
        ];

        echo new HtmlView('main/page-content/signup', true, $data);
    }


    public function remote_request(): void
    {
        $redirect_on_error_url = UrlUtils::baseUrl('/signup');

        // form validation
        $validator = new Validator([
            'email' => ['email'],
            'password' => ['password'],
            'password_confirmation' => ['password', 'matches' => 'password'],
            'username' => ['permit_empty', 'regex_match' => '/^[a-zA-Z0-9][\-a-zA-Z0-9._+]{1,63}$/'],
            'firstname' => ['permit_empty', 'max_length' => 100],
            'lastname' => ['permit_empty', 'max_length' => 100],
        ]);
        if ($validator->redirectOnError($redirect_on_error_url, $_POST)) {
            return;
        }

        try {
            $password_hash = PasswordUtils::hash($_POST['password']);
            $password_confirmation_hash = PasswordUtils::hash($_POST['password_confirmation']);
        } catch (\Exception $exception) {
            CookieMessengerWriter::setMessage(null, true, $exception->getMessage());
            HttpResponse::redirectTo(UrlUtils::baseUrl('/signup'));
            return;
        }

        $ca_body = $_POST;
        $ca_body['password'] = $password_hash;
        $ca_body['password_confirmation'] = $password_confirmation_hash;

        // start signup procedure
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($ca_body)
            ->exec('PUT', '/signup');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody(),
                $_POST
            );
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        // get email and activation_hash
        // get activation_hash
        $response_body = $caResponse->getBody();
        $response_body_array = \json_decode($response_body, true, 2);

        if (!isset($response_body_array, $response_body_array['activation_hash'])) {
            // invalid response body ... redirect to / which should check further
            CookieMessengerWriter::setMessage(
                null,
                true,
                'Invalid email and/or activation hash',
                $_POST
            );
            HttpResponse::redirectTo(UrlUtils::baseUrl());
        } else {
            // SUCCESS
            $activation_link = UrlUtils::baseUrl('/activate-user') . '?' . \http_build_query([
                    'email' => $_POST['email'],
                    'activation_hash' => $response_body_array['activation_hash'],
                ]);
            $email_content = EmailTemplates::buildEmail(
                EmailTemplates::ACTIVATE_ACCOUNT,
                $activation_link,
                $activation_link,
                $activation_link
            );

            // send AWS SES email
            try {
                $info_message = SignupEmail::send(
                    $_POST['email'],
                    'Activate Your Account',
                    $email_content
                );
                $sendEmailResult = true;
            } catch (\Exception $exception) {
                $info_message = $exception->getMessage();
                $sendEmailResult = false;
            }

            // only during development
            if (\env('cleandeck.ENVIRONMENT') === 'development') {
                $activate_user_query = \http_build_query([
                    'email' => $_POST['email'],
                    'activation_hash' => $response_body_array['activation_hash'],
                ]);
                $info_message .= '<hr>The following activation url appears as an <strong>aid</strong> when ' .
                    "<em>cleandeck.ENVIRONMENT</em> variable is set to 'development' (file .env.ini): " . PHP_EOL .
                    UrlUtils::anchor('/activate-user?' . $activate_user_query, 'Activate User', [
                        'target' => '_blank',
                    ]);
            }

            CookieMessengerWriter::setMessage(
                null,
                $sendEmailResult === false,
                nl2br($info_message),
                $sendEmailResult === false ? $_POST : null
            );

            if ($sendEmailResult) {
                HttpResponse::redirectTo(UrlUtils::baseUrl('/login'));
            } else {
                HttpResponse::redirectTo(UrlUtils::baseUrl('/contact'));
            }
        }
    }
}
