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
use Framework\Libraries\CA\CAResponse;
use Framework\Libraries\CSRF\CSRF;
use Framework\Libraries\Http\HttpRequest;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Validator\Validator;

final class SupportCasesRankController
{
    public function ajax_request_case_rank_client(): void
    {
        if (HttpRequest::isAJAX()) {
            HttpResponse::setHeaders([
                'content-type' => 'application/json; charset=UTF-8',
            ]);
        } else {
            HttpResponse::send(400, 'Expecting an AJAX request');
            return;
        }

        // form validation
        $validator = new Validator([
            'case_id' => ['hex', 'min_length' => 10, 'max_length' => 256],
            'support_rank_owner_concise' => ['if_exist', 'permit_empty',
                'in_list' => ['1', '2', '3', '4', '5'], 'label' => 'rank owner concise'],
            'support_rank_owner_polite' => ['if_exist', 'permit_empty',
                'in_list' => ['1', '2', '3', '4', '5'], 'label' => 'rank owner polite'],
        ]);

        if ($validator->hasErrors()) {
            $form_validation_errors = $validator->getErrors();
            $full_error_msg = '';
            foreach ($form_validation_errors as $form_validation_error) {
                $full_error_msg .= $form_validation_error . ' ';
            }

            HttpResponse::send(403, \json_encode($full_error_msg));
            return;
        }

        // end form validation

        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($_POST)
            ->exec('PUT', '/support-case/rank-client');
        $this->sendAjaxResponse($caResponse);
    }


    public function ajax_request_case_rank_support(): void
    {
        if (HttpRequest::isAJAX()) {
            HttpResponse::setHeaders([
                'content-type' => 'application/json; charset=UTF-8',
            ]);
        } else {
            HttpResponse::send(400, 'Expecting an AJAX request');
            return;
        }

        // form validation
        $validator = new Validator([
            'case_id' => ['hex', 'min_length' => 10, 'max_length' => 256],
            'owner_rank_support_pleasant' => ['if_exist', 'permit_empty',
                'in_list' => ['1', '2', '3', '4', '5'], 'label' => 'rank support pleasant'],
            'owner_rank_support_pro' => ['if_exist', 'permit_empty',
                'in_list' => ['1', '2', '3', '4', '5'], 'label' => 'rank support professional'],
        ]);

        if ($validator->hasErrors()) {
            $form_validation_errors = $validator->getErrors();
            $full_error_msg = '';
            foreach ($form_validation_errors as $form_validation_error) {
                $full_error_msg .= $form_validation_error . ' ';
            }

            HttpResponse::send(403, \json_encode($full_error_msg));
            return;
        }

        // end form validation

        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($_POST)
            ->exec('PUT', '/support-case/rank-support');

        $this->sendAjaxResponse($caResponse);
    }

    private function sendAjaxResponse(CAResponse $caResponse): void
    {
        $response_status_code = $caResponse->getStatusCode();

        if ($response_status_code === 204) {
            $response_status_code = 200;
        }

        // build the response
        $csrf_hash = CSRF::init();
        $response_body = [
            'csrf_hash' => $csrf_hash,
        ];
        if ($response_status_code >= 300) {
            $response_body['error_message'] = $caResponse->getBody();
        }

        $response_body_json = \json_encode($response_body);
        if ($response_body_json === false) {
            HttpResponse::send($response_status_code, '{"csrf_hash":"' . $csrf_hash . '"}');
        } else {
            echo $response_body_json;
        }
    }
}
