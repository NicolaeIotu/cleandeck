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

final class AccountHistoryController
{
    public function index(): void
    {
        // form validation
        $validator = new Validator([
            'email' => ['email'],
        ]);
        if ($validator->redirectOnError(UrlUtils::baseUrl('/admin/account/history/search'), $_GET)) {
            return;
        }

        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->exec('GET', '/admin/account/history/' . $_GET['email']);
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectToErrorPage();
            return;
        }

        $response_body = $caResponse->getBody();
        $response_body_array = \json_decode($response_body, true, 4);
        if (!isset($response_body_array, $response_body_array['result'])) {
            // invalid response body
            CookieMessengerWriter::setMessage(
                500,
                true,
                'Invalid account history'
            );
            HttpResponse::redirectToErrorPage();
            return;
        }

        $data = [
            'custom_page_name' => 'Account History',
            'account_history' => $response_body_array['result'],
            'account_email' => $_GET['email'],
            'is_admin' => true,
        ];

        echo new HtmlView('authenticated/admin/account_history', $data);
    }

    public function account_history_search(): void
    {
        $data["custom_page_name"] = 'Search Account History';
        echo new HtmlView('authenticated/admin/account_history_search', $data);
    }
}
