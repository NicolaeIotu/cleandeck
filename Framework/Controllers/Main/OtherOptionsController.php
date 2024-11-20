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

final class OtherOptionsController
{
    public function index(): void
    {
        echo new HtmlView('authenticated/user/account_other_options');
    }

    public function remote_request_account_hibernate(): void
    {
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->exec('PATCH', '/account/mark-asleep');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectTo(UrlUtils::baseUrl('/other-options'));
        } else {
            CookieMessengerWriter::setMessage(
                null,
                false,
                'Account hibernated successfully. ' .
                'You may login at any time in order to restore your account to normal status.'
            );
            HttpResponse::redirectTo(UrlUtils::baseUrl());
        }
    }

    public function account_delete_confirm(): void
    {
        echo new HtmlView('authenticated/user/delete_account_confirmation');
    }

    public function remote_request_account_delete_final(): void
    {
        // form validation
        $validator = new Validator([
            'email' => ['email'],
            'password' => ['password'],
        ]);
        if ($validator->redirectOnError(UrlUtils::baseUrl('/delete-account'))) {
            return;
        }

        try {
            $password_hash = PasswordUtils::hash($_POST['password']);
        } catch (\Exception $exception) {
            CookieMessengerWriter::setMessage(null, true, $exception->getMessage());
            HttpResponse::redirectTo(UrlUtils::baseUrl('/delete-account'));
            return;
        }

        $ca_body = $_POST;
        $ca_body['password'] = $password_hash;

        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($ca_body)
            ->exec('DELETE', '/account');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectTo(UrlUtils::baseUrl('/delete-account'));
        } else {
            CookieMessengerWriter::setMessage(null, false, 'Account deleted successfully.');
            HttpResponse::redirectTo(UrlUtils::baseUrl());
        }
    }
}
