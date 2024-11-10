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
use Framework\Libraries\Cookie\CookieMessengerWriter;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\UrlUtils;
use Framework\Libraries\Validator\Validator;
use Framework\Libraries\View\HtmlView;

final class AgreementsController
{
    public function view_agreement(string $agreement_id, bool $is_admin = false): void
    {
        $redirect_on_error_url = UrlUtils::baseUrl('/user');

        if (!$is_admin && !CleanDeckStatics::isEmployee()) {
            CookieMessengerWriter::setMessage(403);
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->exec('GET',
                $is_admin ? '/admin/agreement/' . $agreement_id : '/agreement/' . $agreement_id);
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        $agreement_response_body_array = \json_decode($caResponse->getBody(), true, 2);
        if (!isset($agreement_response_body_array)) {
            CookieMessengerWriter::setMessage(403, true, 'Invalid agreement details');
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        $data = [
            'custom_page_name' => $agreement_response_body_array['agreement_title'],
            'is_admin' => $is_admin,
        ];

        echo new HtmlView('main/page-content/authenticated/employee/agreement',
            true, \array_merge($data, $agreement_response_body_array));
    }

    public function admin_view_agreement(string $agreement_id): void
    {
        $this->view_agreement($agreement_id, true);
    }

    public function list_agreements(bool $is_admin = false): void
    {
        $redirect_on_error_url = UrlUtils::baseUrl('/user');

        if (!$is_admin && !CleanDeckStatics::isEmployee()) {
            CookieMessengerWriter::setMessage(403);
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        $agreements_category_desc = 'All Applicable';

        $caRequest = new CARequest();
        if ($is_admin) {
            $validator = new Validator([
                'agreement_title' => ['if_exist', 'min_length' => 2, 'max_length' => 100,
                    'label' => 'Agreement title'],
                'page_number' => ['if_exist', 'is_natural',
                    'greater_than' => 0, 'less_than' => 10000,
                    'label' => 'Page number'],
                'page_entries' => ['if_exist', 'is_natural',
                    'greater_than_equal_to' => 5, 'less_than_equal_to' => 150,
                    'label' => 'Page entries'],
            ]);
            if (!$validator->hasErrors()) {
                $query_array = [
                    'page_number' => $_GET['page_number'] ?? 1,
                    'page_entries' => $_GET['page_entries'] ?? 10,
                ];
                if (isset($_GET['agreement_title'])) {
                    $query_array['agreement_title'] = $_GET['agreement_title'];
                }
                $caRequest->setQuery($query_array);
            }
        } else {
            $validator = new Validator([
                'agreements_category' => ['if_exist', 'in_list' => ['all', 'accepted', 'pending'],
                    'label' => 'Agreements category'],
            ]);
            if (!$validator->hasErrors() && isset($_GET['agreements_category'])) {
                if ($_GET['agreements_category'] === 'accepted') {
                    $agreements_category_desc = 'Accepted';
                } elseif ($_GET['agreements_category'] === 'pending') {
                    $agreements_category_desc = 'Pending Applicable';
                }
                $caRequest->setQuery([
                    'agreements_category' => $_GET['agreements_category'],
                ]);
            }
        }
        $caResponse = $caRequest
            ->exec('GET', $is_admin ? '/admin/agreements' : '/user/agreements');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        $agreements_response_body_array = \json_decode($caResponse->getBody(),
            true, $is_admin ? 4 : 3);
        if (!isset($agreements_response_body_array)) {
            CookieMessengerWriter::setMessage(403, true, 'Invalid agreement details');
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        $data = [
            'custom_page_name' => $is_admin ? 'Agreements Administration' :
                $agreements_category_desc . ' Agreements',
            'agreements' => $agreements_response_body_array,
            'is_admin' => $is_admin,
        ];

        echo new HtmlView('main/page-content/authenticated/employee/agreements', true, $data);
    }

    public function admin_list_agreements(): void
    {
        $this->list_agreements(true);
    }

    public function remote_request(string $agreement_id): void
    {
        $redirect_on_error_url = UrlUtils::baseUrl('/user');

        if (!CleanDeckStatics::isEmployee()) {
            CookieMessengerWriter::setMessage(403);
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody([
                'accept' => $_POST['accept'] ?? 'false',
            ])
            ->exec('PATCH', '/user/agreement/' . $agreement_id);
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        CookieMessengerWriter::setMessage(
            null,
            false,
            'Agreement recorded successfully'
        );
        HttpResponse::redirectTo(UrlUtils::baseUrl('/agreements/employee'));
    }
}
