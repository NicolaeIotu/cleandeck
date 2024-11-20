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
use Framework\Libraries\Utils\HtmlUtils;
use Framework\Libraries\Utils\UrlUtils;
use Framework\Libraries\Validator\Validator;
use Framework\Libraries\View\HtmlView;

final class SupportCasesController
{
    public function index(): void
    {
        // form validation
        $validator = new Validator([
            'page_number' => ['if_exist', 'is_natural',
                'greater_than' => 0, 'less_than' => 10000],
            'page_entries' => ['if_exist', 'is_natural',
                'greater_than_equal_to' => 5, 'less_than_equal_to' => 150],
        ]);
        if ($validator->redirectOnError(UrlUtils::baseUrl('/support-cases'))) {
            return;
        }

        $page_number = $_GET['page_number'] ?? 1;
        $page_entries = $_GET['page_entries'] ?? 10;


        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setQuery(['page_number' => $page_number, 'page_entries' => $page_entries])
            ->exec('GET', '/support-cases');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectToErrorPage();
            return;
        }


        $support_cases_array = \json_decode($caResponse->getBody(), true, 4);
        if (!isset($support_cases_array,
            $support_cases_array['stats'],
            $support_cases_array['stats']['total_cases'],
            $support_cases_array['result']) ||
            !\is_int($support_cases_array['stats']['total_cases'])) {
            // invalid response body ... redirect to /error
            CookieMessengerWriter::setMessage(
                500,
                true,
                'Invalid response when retrieving support cases: ' . \json_last_error_msg()
            );
            HttpResponse::redirectToErrorPage();
            return;
        }

        $data = [
            'support_cases' => $support_cases_array,
        ];

        echo new HtmlView('authenticated/user/support_cases_list', $data);
    }

    public function case_details(string $case_id = null): void
    {
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setQuery([
                "case_id" => $case_id,
                "page_number" => $_GET['page_number'] ?? 1,
                "page_entries" => $_GET['page_entries'] ?? 10,
            ])
            ->exec('GET', '/support-case');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectTo(UrlUtils::baseUrl('/support-cases'));
            return;
        }

        $case_details_response_array = \json_decode($caResponse->getBody(), true, 4);
        // brief check
        if (!isset($case_details_response_array, $case_details_response_array['stats'])) {
            CookieMessengerWriter::setMessage(
                null,
                true,
                'Error while retrieving support case details: ' . \json_last_error_msg()
            );
            HttpResponse::redirectTo(UrlUtils::baseUrl('/support-cases'));
            return;
        }

        $case_details_response_array = HtmlUtils::strip_tags_turbo($case_details_response_array);
        $data = [
            'case_details' => $case_details_response_array,
            'custom_page_name' => "Support Case - " . \ucwords($case_details_response_array['stats']['case_title'] ?? ''),
        ];

        echo new HtmlView('authenticated/user/support_cases_case_details', $data);
    }
}
