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
use Framework\Libraries\Email\EmailTemplates;
use Framework\Libraries\Email\SendEmail;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\UrlUtils;
use Framework\Libraries\Validator\Validator;
use Framework\Libraries\View\HtmlView;

final class ApproveAccountsController
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
            ->setQuery('approved_timestamp_max=0&page_number=' . $page_number . '&page_entries=' . $page_entries)
            ->exec('GET','/admin/users');
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
            'custom_page_name' => 'Approve Accounts',
            'unapproved_details' => $response_body_array,
            'is_admin' => true,
        ];

        echo new HtmlView('main/page-content/authenticated/admin/approve_accounts', true, $data);
    }

    /**
     * @param array<string[]> $activation_details
     * @return bool|array<int, array<string, string>>
     */
    private function sendActivationEmails(array $activation_details): bool|array
    {
        $failed_email_details = [];
        foreach ($activation_details as $activation_detail) {
            if (isset($activation_detail['email'], $activation_detail['activation_hash']) &&
                \is_string($activation_detail['email']) && \is_string($activation_detail['activation_hash'])) {
                $activation_link = UrlUtils::baseUrl('/activate-user') . '?' . \http_build_query([
                        'email' => $activation_detail['email'],
                        'activation_hash' => $activation_detail['activation_hash'],
                    ]);

                // send email
                $email_content = EmailTemplates::buildEmail(
                    EmailTemplates::ACTIVATE_ACCOUNT,
                    $activation_link,
                    $activation_link,
                    $activation_link
                );
                try {
                    $sendEmailResult = SendEmail::init(
                        'Activate Your Account',
                        $activation_detail['email'],
                        $email_content
                    );
                } catch (\Exception) {
                    $sendEmailResult = false;
                }

                if (!$sendEmailResult) {
                    $failed_email_details[] = [
                        'email' => $activation_detail['email'],
                        'activation_link' => $email_content,
                    ];
                }
            }
        }

        if ($failed_email_details !== []) {
            return $failed_email_details;
        }
        return true;
    }

    /**
     * @var string[]
     */
    private array $emails_to_delete = [];

    /**
     * @var array<string[]>
     */
    private array $failed_email_deletions = [];

    private function deleteAccount(int $index): void
    {
        if (!isset($this->emails_to_delete[$index])) {
            return;
        }

        $email = $this->emails_to_delete[$index];
        $caRequest = new CARequest();
        $caRequest->setBody([
            'email' => $email,
        ]);
        $caResponse = $caRequest
            ->exec('DELETE', '/admin/user');
        if ($caResponse->hasError()) {
            $this->failed_email_deletions[] = [
                'email' => $email,
                'reason' => $caResponse->getBody(),
            ];
        }

        $this->deleteAccount($index + 1);
    }

    public function remote_request(): void
    {
        $redirect_url = UrlUtils::baseUrl('/admin/accounts/approve');

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
        $emails_to_approve = [];
        $this->emails_to_delete = [];
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
                $emails_to_approve[] = $email;
            } elseif ($option === '-1') {
                $this->emails_to_delete[] = $email;
            }
        }

        if (\count($emails_to_approve) < 1 && \count($this->emails_to_delete) < 1) {
            CookieMessengerWriter::setMessage(
                null,
                true,
                'No changes'
            );
            HttpResponse::redirectTo($redirect_url);
            return;
        }

        $compound_err_msg = '';
        $compound_success_msg = '';
        // Handle deletions (must run first -> runs with session-no-update)
        if ($this->emails_to_delete !== []) {
            $this->deleteAccount(0);

            if ($this->failed_email_deletions !== []) {
                $compound_err_msg .= PHP_EOL .
                    '<strong>Could not delete some accounts as follows:</strong> ' . PHP_EOL;
                foreach ($this->failed_email_deletions as $failed_email_deletion) {
                    $compound_err_msg .= '<strong>' . $failed_email_deletion['email'] . '</strong>' .
                        ' - ' . $failed_email_deletion['reason'] . '; ';
                }

                $compound_err_msg .= '<hr>';
            } else {
                $compound_success_msg .= 'Accounts deleted successfully: ' . \implode(', ', $this->emails_to_delete);
            }
        }

        // Handle approvals
        if ($emails_to_approve !== []) {
            $caRequest = new CARequest();
            $approve_accounts_response = $caRequest
                ->setBody('emails=' . \implode(',', $emails_to_approve))
                ->exec('PATCH', '/admin/accounts/approve');
            if ($approve_accounts_response->hasError()) {
                $compound_err_msg .= PHP_EOL . 'Failed to approve accounts: ' . $approve_accounts_response->getBody();

                CookieMessengerWriter::setMessage(
                    $approve_accounts_response->getStatusCode(),
                    true,
                    $compound_err_msg
                );
                HttpResponse::redirectTo($redirect_url);
                return;
            }

            if ($compound_success_msg !== '') {
                $compound_success_msg .= '<hr>';
            }

            // approval went ok
            // send activation emails for approved accounts
            $approve_accounts_response_body = \json_decode($approve_accounts_response->getBody(), true, 4);
            if (!\is_null($approve_accounts_response_body)) {
                if (isset($approve_accounts_response_body['issue']) &&
                    \is_string($approve_accounts_response_body['issue'])) {
                    $compound_success_msg .= $approve_accounts_response_body['issue'];
                }

                if (isset($approve_accounts_response_body['result']) &&
                    \is_array($approve_accounts_response_body['result']) &&
                    $approve_accounts_response_body['result'] !== []) {
                    $sendActivationEmails_result = $this->sendActivationEmails($approve_accounts_response_body['result']);

                    if (\is_array($sendActivationEmails_result)) {
                        // errors while sending activation emails
                        $compound_err_msg .= PHP_EOL .
                            '<strong>Could not send activation emails as follows:</strong> ' . PHP_EOL;
                        foreach ($sendActivationEmails_result as $sendActivationEmail_result) {
                            $compound_err_msg .= PHP_EOL . $sendActivationEmail_result['email'] .
                                ' - ' . $sendActivationEmail_result['activation_link'] . '<hr>';
                        }
                    } else {
                        $approved_emails = '';
                        // If there are no results show a simple success message.
                        $compound_success_msg .= 'Accounts approved successfully';
                        foreach ($approve_accounts_response_body['result'] as $entry) {
                            if (\strlen($approved_emails) > 0) {
                                $approved_emails .= ', ';
                            }

                            $approved_emails .= $entry['email'];
                        }

                        $compound_success_msg .= ': ' . $approved_emails;
                    }
                } else {
                    $compound_success_msg .= 'Accounts approved successfully';
                }
            }
        }

        CookieMessengerWriter::setMessage(
            null,
            \strlen($compound_err_msg) > 0,
            \strlen($compound_err_msg) > 0 ? $compound_err_msg : $compound_success_msg
        );
        HttpResponse::redirectTo($redirect_url);
    }
}
