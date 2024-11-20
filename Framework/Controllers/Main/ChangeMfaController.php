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

final class ChangeMfaController
{
    public function index(): void
    {
        $data['custom_page_name'] = 'Change MFA';

        // retrieve the previous MFA option
        $caRequest = new CARequest();
        $caResponse = $caRequest
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

        // get mfa option
        $user_details_array = \json_decode($caResponse->getBody(), true, 2);
        if (!isset($user_details_array, $user_details_array['mfa_option'])) {
            CookieMessengerWriter::setMessage(500, true, 'Could not retrieve MFA option.');
            HttpResponse::redirectToErrorPage();
            return;
        }

        $data['mfa_option'] = $user_details_array['mfa_option'];

        echo new HtmlView('authenticated/user/account_change_mfa', $data);
    }


    public function remote_request(): void
    {
        $validator = new Validator([
            'mfa_option' => ['in_list' => ['email', 'disabled']],
        ]);
        if ($validator->redirectOnError(UrlUtils::baseUrl('/change-mfa'))) {
            return;
        }

        // start procedure
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($_POST)
            ->exec('PATCH', '/mfa-option');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectToErrorPage();
        } else {
            // success!
            $post_mfa_option = $_POST['mfa_option'] ?? '';

            $response_body_array = \json_decode($caResponse->getBody(), true, 2);
            if (isset($response_body_array, $response_body_array['accepted_mfa_option'])) {
                // double check accepted value
                if ($response_body_array['accepted_mfa_option'] !== $post_mfa_option) {
                    $error_msg = 'Change request processed successfully, but the MFA method accepted was: ' .
                        $response_body_array['accepted_mfa_option'];
                }
            } else {
                $error_msg = 'Change request processed successfully, but the response is not as expected.';
            }

            if (isset($error_msg)) {
                CookieMessengerWriter::setMessage(null, true, $error_msg);
            } else {
                CookieMessengerWriter::setMessage(null, false, 'New MFA: ' . $post_mfa_option);
            }

            HttpResponse::redirectTo(UrlUtils::baseUrl('/change-mfa'));
        }
    }
}
