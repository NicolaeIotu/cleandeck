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

final class VerifyAccountsController
{
    public function index(): void
    {
        $page_number = $_GET['page_number'] ?? 1;
        $page_entries = $_GET['page_entries'] ?? 5;
        $page_entries = (int)$page_entries;
        // maximum 10 entries because the maximum length of concatenated emails string in the next step
        // is 1290 characters.
        $page_entries = \max(5, \min(10, $page_entries));

        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setQuery([
                'user_details_verified_timestamp_max' => 0,
                // Administrator having account rank >= 10000 need no verification
                'account_rank_max' => 9999,
                'page_number' => $page_number,
                'page_entries' => $page_entries,
            ])
            ->exec('GET', '/admin/users');
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

        $data = [
            'custom_page_name' => 'Verify Accounts',
            'unverified_details' => $response_body_array,
            'is_admin' => true,
        ];

        echo new HtmlView('main/page-content/authenticated/admin/verify_accounts', true, $data);
    }


    public function remote_request(): void
    {
        $redirect_url = UrlUtils::baseUrl('/admin/accounts/mark-verified');

        // form validation
        $validator = new Validator([
            'page_number' => ['if_exist', 'is_natural',
                'greater_than' => 0, 'less_than' => 10000,
                'label' => 'Page number'],
            'page_entries' => ['if_exist', 'is_natural',
                'greater_than_equal_to' => 5, 'less_than_equal_to' => 150,
                'label' => 'Page entries'],
        ]);
        if ($validator->redirectOnError($redirect_url)) {
            return;
        }

        // Manually validate and filter emails
        $emails_to_mark_verified = [];
        if ($_POST === []) {
            CookieMessengerWriter::setMessage(
                null,
                true,
                'Invalid request'
            );
            HttpResponse::redirectTo($redirect_url);
            return;
        }


        foreach ($_POST as $base64_email => $option) {
            $email = \base64_decode($base64_email);
            if ($option === '1') {
                $emails_to_mark_verified[] = $email;
            }
        }

        $err_msg = '';
        $compound_success_msg = '';
        if ($emails_to_mark_verified === []) {
            CookieMessengerWriter::setMessage(
                null,
                true,
                'No changes'
            );
            HttpResponse::redirectTo($redirect_url);
            return;
        }

        // Handle verification
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody('emails=' . \implode(',', $emails_to_mark_verified))
            ->exec('PATCH', '/admin/accounts/mark-verified');
        if ($caResponse->hasError()) {
            $err_msg = 'Failed to mark as verified accounts: ' . $caResponse->getBody();

            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $err_msg
            );
            HttpResponse::redirectTo($redirect_url);
            return;
        }

        $verify_accounts_response_body = \json_decode($caResponse->getBody(), true, 3);
        $verify_accounts_has_error = false;
        if (!\is_null($verify_accounts_response_body)) {
            if (isset($verify_accounts_response_body['issue']) &&
                \is_string($verify_accounts_response_body['issue'])) {
                $verify_accounts_has_error = true;
                $compound_success_msg .= $verify_accounts_response_body['issue'];
            }

            $verified_emails = '';
            if (isset($verify_accounts_response_body['result']) &&
                \is_array($verify_accounts_response_body['result']) &&
                $verify_accounts_response_body['result'] !== []) {
                $compound_success_msg .= 'Accounts marked as verified: ';
                foreach ($verify_accounts_response_body['result'] as $entry) {
                    if ($verified_emails !== '') {
                        $verified_emails .= ', ';
                    }

                    $verified_emails .= $entry;
                }

                $compound_success_msg .= ': ' . $verified_emails;
            } else {
                $compound_success_msg .= 'Accounts marked as verified';
            }
        }


        CookieMessengerWriter::setMessage(
            null,
            $verify_accounts_has_error,
            $compound_success_msg
        );
        HttpResponse::redirectTo($redirect_url);
    }
}
