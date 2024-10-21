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
use Framework\Libraries\Email\SendEmail;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\UrlUtils;
use Framework\Libraries\Validator\Validator;
use Framework\Libraries\View\HtmlView;

final class ChangePrimaryEmailController
{
    public function index(): void
    {
        echo new HtmlView('main/page-content/authenticated/user/account_change_primary_email');
    }


    public function remote_request(): void
    {
        // form validation
        $validator = new Validator([
            'new_primary_email' => ['email'],
        ]);
        if ($validator->redirectOnError(UrlUtils::baseUrl('/change-primary-email'))) {
            return;
        }

        // start procedure
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($_POST)
            ->exec('PATCH', '/primary-email');
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

        if (!isset($response_body_array, $response_body_array['activation_hash_new_primary_email'])) {
            CookieMessengerWriter::setMessage(
                null,
                true,
                'Cannot retrieve the activation hash. Please try again.'
            );
            HttpResponse::redirectTo(UrlUtils::baseUrl());
            return;
        }

        $activation_hash = $response_body_array['activation_hash_new_primary_email'];

        // send email using AWS SES
        $new_primary_email = $_POST['new_primary_email'];
        $activate_email_query = \http_build_query([
            'email' => $new_primary_email,
            'activation_hash' => $activation_hash,
        ]);
        $url_activate_new_primary_email = UrlUtils::baseUrl('/activate-email') . '?' . $activate_email_query;
        $email_content = EmailTemplates::buildEmail(
            EmailTemplates::ACTIVATE_NEW_PRIMARY_EMAIL,
            $url_activate_new_primary_email,
            $url_activate_new_primary_email,
            $url_activate_new_primary_email
        );

        ////////////////////////////////////////////////////////////////////////////////////////////////
        try {
            $sendEmailResult = SendEmail::init(
                'Activate New Primary Email',
                $new_primary_email,
                $email_content
            );
        } catch (\Exception) {
            $sendEmailResult = false;
        }

        if ($sendEmailResult) {
            $success_msg = 'Primary email change request successfully received. ' . PHP_EOL .
                'Check your existing email for instructions on how to activate a fresh primary email. ';

            if (\env('cleandeck.ENVIRONMENT') === 'development') {
                $success_msg .= PHP_EOL .
                    '<br>The following activation url appears as an <strong>aid</strong> when ' .
                    "<em>cleandeck.ENVIRONMENT</em> variable is set to 'development' (file .env.ini): " . PHP_EOL .
                    UrlUtils::anchor('/activate-email?' . $activate_email_query, 'Activate Email', [
                        'target' => '_blank',
                    ]);
            }

            CookieMessengerWriter::setMessage(null, false, nl2br($success_msg));
            HttpResponse::redirectTo(UrlUtils::baseUrl('/user'));
        } else {
            $info_message = 'There was an error when trying to send the activation email. ' . PHP_EOL .
                'Please retry this operation or contact us.' . PHP_EOL;

            if (\env('cleandeck.ENVIRONMENT') === 'development') {
                $info_message .= 'Activation Hash: ' . $activation_hash . PHP_EOL;
            }

            CookieMessengerWriter::setMessage(null, true, nl2br($info_message));
            HttpResponse::redirectTo(UrlUtils::baseUrl('/support-cases/new'));
        }
    }
}
