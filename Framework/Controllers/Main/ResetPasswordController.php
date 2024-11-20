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

final class ResetPasswordController
{
    // shows start reset-password page
    public function index(): void
    {
        echo new HtmlView('reset-password');
    }


    public function remote_request(): void
    {
        // form validation
        $validator = new Validator([
            'email' => ['email'],
        ]);
        if ($validator->redirectOnError(UrlUtils::baseUrl('/reset-password'))) {
            return;
        }

        // start login procedure
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($_POST)
            ->exec('DELETE', '/password');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(null, true, 'Generic Error');
            HttpResponse::redirectToErrorPage();
            return;
        }

        // user is authenticated with a valid session
        if ($caResponse->getStatusCode() === 204) {
            CookieMessengerWriter::setMessage(null, true, 'You are authenticated.');
            HttpResponse::redirectTo(UrlUtils::baseUrl('/change-password'));
            return;
        }

        $response_body = $caResponse->getBody();
        $response_body_array = \json_decode($response_body, true, 2);

        if (!isset($response_body_array,
            $response_body_array['primary_email'],
            $response_body_array['password_reset_hash'])) {
            // invalid response body ... redirect to / which should check further
            CookieMessengerWriter::setMessage(
                null,
                true,
                'Invalid response from the server. Please try again later.'
            );
            HttpResponse::redirectTo(UrlUtils::baseUrl());
            return;
        }

        $AWSSES_error = false;
        $AWSSES_error_message = 'There was an error when trying to send reset instructions to email: ' . PHP_EOL;

        $reset_link = UrlUtils::baseUrl('/change-password-on-reset') . '?' . \http_build_query([
                'primary_email' => $response_body_array['primary_email'],
                'password_reset_hash' => $response_body_array['password_reset_hash'],
            ]);

        // send AWS SES email the primary address $response_body_array['primary_email']
        try {
            $sendEmailResult = SendEmail::init(
                'Reset Password Instructions',
                $response_body_array['primary_email'],
                EmailTemplates::buildEmail(
                    EmailTemplates::RESET_PASSWORD,
                    $response_body_array['primary_email'],
                    $reset_link,
                    $reset_link,
                    $reset_link
                )
            );
        } catch (\Exception) {
            $sendEmailResult = false;
        }

        if ($sendEmailResult === false) {
            $AWSSES_error = true;
            $AWSSES_error_message .= ' - ' . $response_body_array['primary_email'];
        }

        for ($i = 1; $i < 6; ++$i) {
            if (isset($response_body_array['email_' . $i])) {
                // send AWS SES email to secondary emails:  $response_body_array['email_' . $i]
                try {
                    $sendEmailResult = SendEmail::init(
                        'Reset Password Instructions',
                        $response_body_array['email_' . $i],
                        EmailTemplates::buildEmail(
                            EmailTemplates::RESET_PASSWORD,
                            $response_body_array['primary_email'],
                            $reset_link,
                            $reset_link,
                            $reset_link
                        )
                    );
                } catch (\Exception) {
                    $sendEmailResult = false;
                }

                if ($sendEmailResult === false) {
                    $AWSSES_error = true;
                    $AWSSES_error_message .= ' - ' . $response_body_array['email_' . $i];
                }
            }
        }

        if (\env('cleandeck.ENVIRONMENT') === 'development') {
            $reset_password_query = \http_build_query([
                'primary_email' => $response_body_array['primary_email'],
                'password_reset_hash' => $response_body_array['password_reset_hash'],
            ]);
            $response_message = '<hr>Password reset url is displayed as <strong>aid</strong> only when ' .
                "<em>cleandeck.ENVIRONMENT</em> variable is set to 'development' (file .env.ini): " . PHP_EOL .
                UrlUtils::anchor('/change-password-on-reset?' . $reset_password_query, 'Change Password on Reset', [
                    'target' => '_blank',
                ]);
        } else {
            $response_message = '';
        }

        $final_response_message = "Please follow password reset instructions sent by email." . $response_message;
        if ($AWSSES_error) {
            $final_response_message .= PHP_EOL . $AWSSES_error_message . PHP_EOL .
                'Please try to repeat the operation or <a title="Contact Us" href="' . UrlUtils::baseUrl('/contact') . '">contact us</a>.';
        }

        CookieMessengerWriter::setMessage(
            null,
            $AWSSES_error,
            nl2br($final_response_message)
        );
        HttpResponse::redirectTo(UrlUtils::baseUrl());
    }
}
