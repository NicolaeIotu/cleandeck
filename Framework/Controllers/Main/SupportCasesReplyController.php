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

final class SupportCasesReplyController
{
    public function remote_request_case_reply(): void
    {
        if (isset($_POST['case_id'])) {
            $redirect_on_error_url = UrlUtils::baseUrl('/support-cases/case/details/' . $_POST['case_id'] .
                '?page_number=1&page_entries=10');
        } else {
            $redirect_on_error_url = UrlUtils::baseUrl('/support-cases');
        }

        $previous_form_data = [
            'message_content' => $_POST['message_content'] ?? '',
        ];

        // validate data
        $validator = new Validator([
            'case_id' => ['regex_match' => '/^[a-f0-9]{64}$/'],
            'message_content' => ['min_length' => 10, 'max_length' => 5000],
        ]);
        if ($validator->redirectOnError($redirect_on_error_url, $previous_form_data)) {
            return;
        }


        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($_POST)
            ->exec('POST', '/support-case');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody(),
                $previous_form_data
            );
        }

        HttpResponse::redirectTo($redirect_on_error_url);
    }
}
