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

final class ActivateEmailController
{
    // 'click in an email' scenario, or in case the user is redirected here
    // previous form data is not recovered
    public function index(): void
    {
        // form validation
        $validator = new Validator([
            'email' => ['email', 'if_exist'],
            'activation_hash' => ['hex', 'if_exist', 'min_length' => 10, 'max_length' => 256],
        ]);
        if ($validator->redirectOnError(UrlUtils::baseUrl('/activate-email'))) {
            return;
        }

        // check GET vars email and activation_hash; if present then directly perform activation and return result
        // (this is 'click in an email' scenario)
        if (isset($_GET['email'], $_GET['activation_hash'])) {
            $caRequest = new CARequest();
            $activate_email_response = $caRequest
                ->setBody([
                    'email' => $_GET['email'],
                    'activation_hash' => $_GET['activation_hash'],
                ])
                ->exec('PATCH', '/email/activate');

            if ($activate_email_response->hasError()) {
                CookieMessengerWriter::setMessage(
                    $activate_email_response->getStatusCode(),
                    true,
                    $activate_email_response->getBody()
                );
                HttpResponse::redirectToErrorPage();
                return;
            }
            CookieMessengerWriter::setMessage(
                null,
                false,
                \sprintf('Email %s activated successfully!', $_GET['email'])
            );
            HttpResponse::redirectTo(UrlUtils::baseUrl('/user'));
            return;
        }

        // invalid or missing GET vars; proceed to manually activate email
        echo new HtmlView('authenticated/user/account_activate_email');
    }

    public function remote_request(): void
    {
        // form validation
        $validator = new Validator([
            'email' => ['email'],
            'activation_hash' => ['hex', 'min_length' => 10, 'max_length' => 256],
        ]);

        $previous_form_data = [
            'email' => $_POST['email'] ?? '',
            'activation_hash' => $_POST['activation_hash'] ?? '',
        ];

        if ($validator->redirectOnError(UrlUtils::baseUrl('/activate-email'), $previous_form_data)) {
            return;
        }


        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($_POST)
            ->exec('PATCH', '/email/activate');

        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody(),
                $previous_form_data
            );
        } else {
            $email = $_POST['email'] ?? '';
            CookieMessengerWriter::setMessage(
                null,
                false,
                \sprintf('Email %s activated successfully!', $email)
            );
        }

        HttpResponse::redirectTo(UrlUtils::baseUrl('/user'));
    }
}
