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
use Framework\Libraries\View\HtmlView;

final class AgreementLifecycleController
{
    public function admin_agreement_new(): void
    {
        $data = [
            'is_admin' => true,
        ];

        echo new HtmlView('main/page-content/authenticated/admin/agreement_new_or_modify', true, $data);
    }

    /**
     * Used both for new agreements and when modifying existing agreements.
     *
     * IMPORTANT!
     * The validation is done by CMD-Auth. This is required when changing the encoding in order for the content
     *  to survive the transfer to/from the database.
     *
     * As a reminder, CMD-Auth will always validate input, but it is better to filter calls and stop
     *  invalid requests before ever reaching CMD-Auth level in order to optimize network usage.
     */
    public function remote_request_admin_agreement_edit(string $agreement_id = null): void
    {
        $is_modify_action = isset($agreement_id);

        // At this point the data is base64 encoded so form validation is done by CMD-Auth alone

        $redirect_on_error_url = UrlUtils::baseUrl($is_modify_action ?
            ('/admin/agreement/modify/' . $agreement_id) : '/admin/agreement/new');

        // CRITICAL!
        // In order to make sure that the content of the agreement survives the transport,
        // a conversion to base64 is done at the frontend and here.
        // At the same time CMD-Auth's setting validation.others.character_encoding_per_request
        // must be set to *true* in order to convert base64 values to the encoding set using
        // *character_encoding_downstream* (utf16le) before storing to database.
        // If another approach is required you should adjust frontend logic, CMD-Auth's settings and this class.
        $caRequest = new CARequest();
        $caRequest
            ->setQuery('character_encoding_upstream=base64&character_encoding_downstream=utf16le')
            ->setBody($_POST);

        if ($is_modify_action) {
            $edit_agreement_response =
                $caRequest->exec('PATCH', '/admin/agreement/' . $agreement_id);
        } else {
            $edit_agreement_response =
                $caRequest->exec('POST', '/admin/agreements');
        }

        if ($edit_agreement_response->hasError()) {
            syslog(LOG_INFO, $edit_agreement_response->getBody());
            CookieMessengerWriter::setMessage(
                $edit_agreement_response->getStatusCode(),
                true,
                $edit_agreement_response->getBody(),
                $_POST
            );
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        // SUCCESS!
        $edit_agreement_response_array = \json_decode($edit_agreement_response->getBody(), true, 2);
        if (!isset($edit_agreement_response_array, $edit_agreement_response_array['agreement_id']) ||
            \strlen((string)$edit_agreement_response_array['agreement_id']) < 8) {
            // invalid response
            CookieMessengerWriter::setMessage(
                500,
                true,
                'Agreement added, but a valid id could not be retrieved. Further checks required.'
            );
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        // all operations successful
        $response_message = $is_modify_action ? 'Agreement modified' : 'Agreement added';

        CookieMessengerWriter::setMessage(
            null,
            false,
            $response_message);
        HttpResponse::redirectTo(UrlUtils::baseUrl('/admin/agreements'));
    }


    public function admin_agreement_modify(string $agreement_id): void
    {
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->exec('GET', '/admin/agreement/' . $agreement_id);
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectToErrorPage();
            return;
        }

        $agreement_details_body = $caResponse->getBody();
        $agreement_details_array = \json_decode($agreement_details_body, true, 2);

        if (!isset($agreement_details_array, $agreement_details_array['agreement_id'], $agreement_details_array['agreement_title'])) {
            // invalid response body
            CookieMessengerWriter::setMessage(500, true, 'Could not get valid agreement details');
            HttpResponse::redirectTo(UrlUtils::baseUrl('/agreement/' . $agreement_id));
            return;
        }

        if ($agreement_details_array['agreement_id'] !== $agreement_id) {
            CookieMessengerWriter::setMessage(500, true, 'Invalid agreement details');
            HttpResponse::redirectTo(UrlUtils::baseUrl('/agreement/' . $agreement_id));
            return;
        }

        $data = [
            'custom_page_name' => 'Edit agreement - ' . \ucfirst((string)$agreement_details_array['agreement_title']),
            'agreement_details' => $agreement_details_array,
            'is_admin' => true,
        ];

        echo new HtmlView('main/page-content/authenticated/admin/agreement_new_or_modify', true, $data);
    }

    public function remote_request_admin_agreement_modify(string $agreement_id): void
    {
        $this->remote_request_admin_agreement_edit($agreement_id);
    }

    public function remote_request_admin_agreement_delete(string $agreement_id): void
    {
        $redirect_on_error_url = UrlUtils::baseUrl('/admin/agreements/' . $agreement_id);


        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->exec('DELETE', '/admin/agreement/' . $agreement_id);
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        CookieMessengerWriter::setMessage(null, false, 'Agreement deleted successfully.');
        HttpResponse::redirectTo(UrlUtils::baseUrl('/admin/agreements'));
    }
}
