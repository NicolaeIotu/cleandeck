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

final class SupportCasesAdminsController
{
    public function cases_overview(): void
    {
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->exec('GET', '/admin/support-cases/overview');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectToErrorPage();
            return;
        }

        $cases_overview_response_array = \json_decode($caResponse->getBody(), true, 3);
        if (!isset($cases_overview_response_array)) {
            CookieMessengerWriter::setMessage(
                null,
                true,
                'Error while retrieving support cases overview. Please try again.'
            );
            HttpResponse::redirectToErrorPage();
            return;
        }

        $data = [
            'cases_overview' => $cases_overview_response_array,
        ];

        echo new HtmlView('authenticated/admin/support_cases_overview', $data);
    }


    public function cases_search(): void
    {
        $data["custom_page_name"] = 'Search Support Cases';

        echo new HtmlView('authenticated/admin/support_cases_search', $data);
    }

    public function remote_request_cases_search(): void
    {
        $query = UrlUtils::get_query();
        $redirect_on_error_url = UrlUtils::baseUrl('/support-cases/search') .
            ($query === '' ? '' : '?' . $query);

        $validator = new Validator([
            'user_id' => ['if_exist', 'permit_empty', 'regex_match' => '/^[a-f0-9]{10,}$/'],
            'content' => ['if_exist', 'max_length' => 300],
            'topic' => ['if_exist', 'max_length' => 100],
            'status' => ['if_exist', 'permit_empty', 'regex_match' => '/^(closed|opened|\-\-)$/'],
            'attended' => ['if_exist', 'permit_empty', 'regex_match' => '/^(yes|no|\-\-)$/'],
            'ranked' => ['if_exist', 'permit_empty', 'regex_match' => '/^(yes|no|missing_support|missing_owner|\-\-)$/'],
            'page_number' => ['if_exist', 'is_natural',
                'greater_than' => 0, 'less_than' => 10000],
            'page_entries' => ['if_exist', 'is_natural',
                'greater_than_equal_to' => 5, 'less_than_equal_to' => 150],
        ]);
        if ($validator->redirectOnError($redirect_on_error_url, $_GET)) {
            return;
        }

        $page_number = $_GET['page_number'] ?? 1;
        $page_entries = $_GET['page_entries'] ?? 10;


        // filter useful values
        $search_array = [];
        // important
        $allowed_search_keys = ["user_id", "content", "topic", "status", "attended", "ranked"];
        foreach ($_GET as $key => $value) {
            if (!\in_array($key, $allowed_search_keys)) {
                continue;
            }
            if ($value === "--") {
                continue;
            }
            if (\strlen((string)$value) <= 0) {
                continue;
            }
            $search_array[$key] = $value;
        }

        // end filter useful values


        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setQuery([...$search_array, 'page_number' => $page_number, 'page_entries' => $page_entries])
            ->exec('GET', '/admin/support-cases');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody(),
                $_POST
            );
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }


        $support_cases_array = \json_decode($caResponse->getBody(), true, 4);
        if (!isset($support_cases_array,
                $support_cases_array['stats'],
                $support_cases_array['stats']['total_cases'],
                $support_cases_array['result']) ||
            !\is_int($support_cases_array['stats']['total_cases'])) {
            CookieMessengerWriter::setMessage(
                null,
                true,
                'Error while retrieving support cases. Please try again.',
                $_POST
            );
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        $data = [
            'support_cases' => $support_cases_array,
        ];

        $data["custom_page_name"] = 'Support Cases Search Results';
        // store the query in order to describe on the next presentation page what was searched
        $data["search_details"] = $search_array;

        echo new HtmlView('authenticated/admin/support_cases_search_results', $data);
    }
}
