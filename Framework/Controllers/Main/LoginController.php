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
use Framework\Libraries\CleanDeckStatics;
use Framework\Libraries\Cookie\AppCookies;
use Framework\Libraries\Cookie\CookieMessengerWriter;
use Framework\Libraries\Cookie\JWTCookiesHandler;
use Framework\Libraries\Email\EmailTemplates;
use Framework\Libraries\Email\SendEmail;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\PasswordUtils;
use Framework\Libraries\Utils\UrlUtils;
use Framework\Libraries\Validator\Validator;
use Framework\Libraries\View\HtmlView;

final class LoginController
{
    public function index(): void
    {
        $data = [
            'seo_description' => 'Login',
        ];

        echo new HtmlView('login', $data);
    }

    // handles login requests
    public function remote_request(): void
    {
        // form validation
        $validator = new Validator([
            'email' => ['email'],
            'password' => ['password'],
        ]);
        if ($validator->redirectOnError(UrlUtils::baseUrl('/login'),
            ['email' => $_POST['email']])) {
            return;
        }

        try {
            $password_hash = PasswordUtils::hash($_POST['password']);
        } catch (\Exception $exception) {
            CookieMessengerWriter::setMessage(null, true,
                $exception->getMessage(), ['email' => $_POST['email']]);
            HttpResponse::redirectTo(UrlUtils::baseUrl('/login'));
            return;
        }

        $ca_body = $_POST;
        $ca_body['password'] = $password_hash;

        // start login procedure
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($ca_body)
            ->exec('PUT', '/login');
        if ($caResponse->hasError()) {
            syslog(LOG_ERR, $caResponse->getBody());
            CookieMessengerWriter::setMessage($caResponse->getStatusCode(), true,
                $caResponse->getBody(), ['email' => $_POST['email']]);
            HttpResponse::redirectTo(UrlUtils::baseUrl('/login'));
            return;
        }

        // check for MFA which will show within the body if enabled by user
        $login_response_array = \json_decode($caResponse->getBody(), true, 2);
        if (isset($login_response_array, $login_response_array['mfa_code'], $login_response_array['mfa_email'])) {
            // is MFA Step 1 type of login

            // send AWS SES email
            try {
                $sendEmailResult = SendEmail::init(
                    'Login Code',
                    $login_response_array['mfa_email'],
                    EmailTemplates::buildEmail(EmailTemplates::MFA_LOGIN, $login_response_array['mfa_code'])
                );
            } catch (\Exception) {
                $sendEmailResult = false;
            }

            if ($sendEmailResult) {
                // mfa code does not appear in production
                if (\env('cleandeck.ENVIRONMENT') === 'development') {
                    CookieMessengerWriter::setMessage(null, false,
                        'The following MFA Code appears as an <strong>aid</strong> when ' .
                        "<em>cleandeck.ENVIRONMENT</em> variable is set to 'development' (file .env.ini): " . PHP_EOL .
                        $login_response_array['mfa_code']);
                }

                HttpResponse::redirectTo(UrlUtils::baseUrl('/login-mfa-step-2'));
            } else {
                $info_message = 'There was an error when trying to send the MFA login code. ' . PHP_EOL .
                    'This event is critical. Please <a title="Contact Us" href="' .
                    UrlUtils::baseUrl('/contact') . '">contact us</a>.' . PHP_EOL;

                if (\env('cleandeck.ENVIRONMENT') !== 'production') {
                    $info_message .= 'MFA code: ' . $login_response_array['mfa_code'] . PHP_EOL;
                }

                CookieMessengerWriter::setMessage(null, true,
                    nl2br($info_message), ['email' => $_POST['email']]);
                HttpResponse::redirectTo(UrlUtils::baseUrl('/contact'));
            }
        } else {
            // standard simple login with no MFA (includes status code 204)
            HttpResponse::redirectTo(UrlUtils::baseUrl('/user'));
        }
    }

    // handle MFA step 2
    public function mfa_step_2(): void
    {
        if (!$this->checkMFAinProgress()) {
            if (CleanDeckStatics::isAuthenticated()) {
                HttpResponse::redirectTo(UrlUtils::baseUrl('/user'));
            } else {
                HttpResponse::redirectTo(UrlUtils::baseUrl('/login'));
            }

            return;
        }

        echo new HtmlView('authenticated/user/mfa_step_2');
    }


    // handle MFA step 2 - request
    public function mfa_step_2_remote_request(): void
    {
        if (!$this->checkMFAinProgress()) {
            if (CleanDeckStatics::isAuthenticated()) {
                HttpResponse::redirectTo(UrlUtils::baseUrl('/user'));
            } else {
                HttpResponse::redirectTo(UrlUtils::baseUrl('/login'));
            }

            return;
        }

        // form validation
        $validator = new Validator([
            'mfa_code' => ['regex_match' => '/^[a-zA-Z0-9]+$/'],
        ]);
        if ($validator->redirectOnError(UrlUtils::baseUrl('/login-mfa-step-2'))) {
            return;
        }

        // make the request
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($_POST)
            ->exec('PUT', '/login/mfa-step-2');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectTo(UrlUtils::baseUrl('/login'));
            return;
        }

        HttpResponse::redirectTo(UrlUtils::baseUrl('/user'));
    }

    // handle MFA cancel - request
    public function mfa_cancel_remote_request(): void
    {
        if (!$this->checkMFAinProgress()) {
            if (CleanDeckStatics::isAuthenticated()) {
                HttpResponse::redirectTo(UrlUtils::baseUrl('/user'));
            } else {
                HttpResponse::redirectTo(UrlUtils::baseUrl('/login'));
            }

            return;
        }


        // make the request
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->exec('DELETE', '/login/mfa-cancel');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectTo(UrlUtils::baseUrl('/login'));
            return;
        }

        HttpResponse::redirectTo(UrlUtils::baseUrl());
    }

    /**
     * Detect and mark MFA authentication in progress.
     */
    private function checkMFAinProgress(): bool
    {
        if (isset($_COOKIE[AppCookies::STATUS_COOKIE_NAME()])) {
            try {
                $app_status = JWTCookiesHandler::describeCookie(AppCookies::STATUS_COOKIE_NAME());
            } catch (\Exception $e) {
                \error_log('Failed to extract MFA status from JWT cookie: ' . $e->getMessage());
            }

            return isset($app_status, $app_status['mfa']) && $app_status['mfa'] === true;
        }
        return false;
    }
}
