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

class SupportCasesLifecycleController
{
    public function cases_new(): void
    {
        $data = [];

        $validator = new Validator([
            'case_topic' => ['if_exist', 'min_length' => 2],
        ]);

        // use these to populate fields with predefined values
        // silently discard any errors
        if (!$validator->hasErrors()) {
            if (isset($_GET['case_topic'])) {
                $data['case_topic'] = $_GET['case_topic'];
            }
        }


        $data['custom_page_name'] = 'New Support Case';

        echo new HtmlView('main/page-content/authenticated/user/support_cases_new', true, $data);
    }


    public function remote_request_cases_new(): void
    {
        $redirect_on_error_url = UrlUtils::baseUrl('/support-cases/new');

        $validator = new Validator([
            'case_title' => ['min_length' => 2],
            'case_topic' => ['min_length' => 2],
            'case_references' => ['if_exist', 'min_length' => 10, 'max_length' => 200],
            'message_content' => ['min_length' => 40, 'max_length' => 5000],
        ]);
        if ($validator->redirectOnError($redirect_on_error_url, $_POST)) {
            return;
        }


        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($_POST)
            ->exec('POST', '/support-cases');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody(),
                $_POST
            );
            HttpResponse::redirectTo($redirect_on_error_url);
        } else {
            CookieMessengerWriter::setMessage(null, false, 'Support case created');
            HttpResponse::redirectTo(UrlUtils::baseUrl('/support-cases'));
        }
    }


    public function remote_request_case_close(): void
    {
        // form validation
        $validator = new Validator([
            'case_id' => ['regex_match' => '/^[a-f0-9]{10,}$/'],
        ]);
        if ($validator->redirectOnError(UrlUtils::baseUrl('/support-cases/search'))) {
            return;
        }

        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($_POST)
            ->exec('PATCH', '/admin/support-case/close');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectToErrorPage();
        } else {
            CookieMessengerWriter::setMessage(null, false, 'Support case closed.');
            if (isset($_POST['case_id'])) {
                $redirect_on_success_url = UrlUtils::baseUrl('/support-cases/case/details/' . $_POST['case_id'] .
                    '?page_number=1&page_entries=10');
            } else {
                $redirect_on_success_url = UrlUtils::baseUrl('/support-cases');
            }

            HttpResponse::redirectTo($redirect_on_success_url);
        }
    }
}
