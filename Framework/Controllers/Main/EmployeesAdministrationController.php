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

final class EmployeesAdministrationController
{
    public function employees_list(): void
    {
        $redirect_on_error_url = UrlUtils::baseUrl('/administration');

        // form validation
        $validator = new Validator([
            'email' => ['if_exist', 'permit_empty', 'max_length' => 200],
            'page_number' => ['if_exist', 'is_natural',
                'greater_than' => 0, 'less_than' => 10000,
                'label' => 'Page number'],
            'page_entries' => ['if_exist', 'is_natural',
                'greater_than_equal_to' => 5, 'less_than_equal_to' => 150,
                'label' => 'Page entries'],
        ]);
        if ($validator->redirectOnError($redirect_on_error_url)) {
            return;
        }

        $caRequest = new CARequest();
        $query_arr = \array_filter($_GET, static function ($key): bool {
            return \in_array($key, ['email', 'page_number', 'page_entries']);
        }, ARRAY_FILTER_USE_KEY);
        if ($query_arr !== []) {
            $caRequest->setQuery($query_arr);
        }
        $caResponse = $caRequest
            ->exec('GET', '/admin/employees');
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectToErrorPage();
            return;
        }

        $employees_list_body = $caResponse->getBody();
        $employees_list_array = \json_decode($employees_list_body, true, 4);

        if (!isset($employees_list_array, $employees_list_array['stats'], $employees_list_array['result'])) {
            CookieMessengerWriter::setMessage(500, true, 'Invalid listing of employees');
            HttpResponse::redirectToErrorPage();
            return;
        }

        $data = [
            'employees' => $employees_list_array,
            'custom_page_name' => 'Employees',
        ];

        echo new HtmlView('main/page-content/authenticated/admin/employees_list', true, $data);
    }


    public function employee_modify(string $user_id): void
    {
        $redirect_on_error_url = UrlUtils::baseUrl('/admin/employees');

        $caRequest = new CARequest();
        $caResponse =
            $caRequest->exec('GET', '/admin/employee/' . $user_id);

        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        $employee_details_array = \json_decode($caResponse->getBody(), true, 2);
        // At least the email must be supplied. The other employee details may miss because initially
        //  users are not employees.
        if (!isset($employee_details_array, $employee_details_array['email'])) {
            CookieMessengerWriter::setMessage(500, true, 'Invalid employee details.');
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        $data = [
            'employee_details' => $employee_details_array,
            'custom_page_name' => 'Employee Details - ' . $employee_details_array['email'],
        ];

        echo new HtmlView('main/page-content/authenticated/admin/employee_modify', true, $data);
    }

    public function remote_request_employee_modify(): void
    {
        $redirect_url = UrlUtils::baseUrl('/admin/employee/' . $_POST['email']);

        $validator = new Validator([
            // mandatory
            'email' => ['min_length' => 8, 'max_length' => 200],
            // optional
            'employee_type' => ['if_exist', 'permit_empty', 'alpha_numeric_basic_punct', 'max_length' => 254],
            'employment_start_date' =>
                ['if_exist', 'permit_empty', 'max_length' => 10, 'regex_match' => '/^\d{4}-\d{2}-\d{2}$/'],
            'employment_end_date' =>
                ['if_exist', 'permit_empty', 'max_length' => 10, 'regex_match' => '/^\d{4}-\d{2}-\d{2}$/'],
            'employment_official_classification' =>
                ['if_exist', 'permit_empty', 'alpha_numeric_space_punct', 'max_length' => 254],
            'employment_official_title' => ['if_exist', 'permit_empty', 'alpha_numeric_punct', 'max_length' => 1000],
            'other_employment_details' => ['if_exist', 'permit_empty', 'alpha_numeric_punct', 'max_length' => 3000],
        ]);
        if ($validator->redirectOnError($redirect_url)) {
            return;
        }

        $caRequest = new CARequest();
        $caRequest->setBody($_POST);

        $caResponse =
            $caRequest->exec('PATCH', '/admin/employee');

        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectTo($redirect_url);
            return;
        }

        CookieMessengerWriter::setMessage(null, false, 'Employee details updated successfully.');
        HttpResponse::redirectTo(UrlUtils::baseUrl('/admin/employee/' . $_POST['email']));
    }
}
