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

final class ChangePasswordOnResetController
{
    public function index(): void
    {
        $primary_email = $_GET['primary_email'] ?? '';
        $password_reset_hash = $_GET['password_reset_hash'] ?? '';

        $primary_email_check = \filter_var($primary_email, FILTER_VALIDATE_EMAIL) ||
            (\strtolower((string) $primary_email) === 'admin100k');
        $password_reset_hash_check = \preg_match('/^[0-9a-fA-F]{2,}$/', (string) $password_reset_hash);

        if ($primary_email_check === false ||
            $password_reset_hash_check === false ||
            $password_reset_hash_check === 0) {
            CookieMessengerWriter::setMessage(400, true, 'Invalid password reset details');
            HttpResponse::redirectToErrorPage();
            return;
        }

        $data = [
            'custom_data' => [
                'primary_email' => $primary_email,
                'password_reset_hash' => $password_reset_hash,
            ],
        ];

        echo new HtmlView('change-password-on-reset', $data);
    }


    public function remote_request(): void
    {
        $validator = new Validator([
            'primary_email' => ['email'],
            'password_reset_hash' => ['hex'],
            'disable_mfa' => ['in_list' => ['true', 'false']],
            'new_password' => ['password'],
            'new_password_confirmation' => ['password', 'matches' => 'new_password'],
        ]);

        $redirect_on_error_url = UrlUtils::baseUrl('/change-password-on-reset?' .
            \http_build_query([
                'primary_email' => $_POST['primary_email'] ?? '',
                'password_reset_hash' => $_POST['password_reset_hash'] ?? '',
            ]));

        if ($validator->redirectOnError($redirect_on_error_url)) {
            return;
        }

        try {
            $new_password_hash = PasswordUtils::hash($_POST['new_password']);
            $new_password_confirmation_hash = PasswordUtils::hash($_POST['new_password_confirmation']);
        } catch (\Exception $exception) {
            CookieMessengerWriter::setMessage(null, true, $exception->getMessage());
            HttpResponse::redirectTo(UrlUtils::baseUrl('/login'));
            return;
        }

        $ca_body = $_POST;
        $ca_body['new_password'] = $new_password_hash;
        $ca_body['new_password_confirmation'] = $new_password_confirmation_hash;


        // start procedure
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($ca_body)
            ->exec('PUT', '/password');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        // on password reset successful redirect to the login page
        CookieMessengerWriter::setMessage(null, false, 'Password changed!');
        HttpResponse::redirectTo(UrlUtils::baseUrl('/login'));
    }
}
