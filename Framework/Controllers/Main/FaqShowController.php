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
use Framework\Libraries\LocalQueues\FileOpsQueue;
use Framework\Libraries\Tasks\TaskHandler;
use Framework\Libraries\Utils\UrlUtils;
use Framework\Libraries\Utils\WarningHandler;
use Framework\Libraries\Validator\Validator;
use Framework\Libraries\View\HtmlView;

final class FaqShowController
{
    public function faqs_list(): void
    {

        $is_admin = CleanDeckStatics::isAuthenticated() && CleanDeckStatics::getAccountRank() >= 1000;

        // admins with rank at least 1000 can use a dedicated endpoint
        if ($is_admin) {
            $faqs_list_endpoint = '/admin/faqs';
        } else {
            $faqs_list_endpoint = '/faqs';
        }

        $validator = new Validator([
            'disabled' => ['if_exist', 'in_list' => ['--', 'true', 'false'],
                'label' => 'Show disabled FAQs'],
            'content' => ['if_exist', 'max_length' => 2000, 'label' => 'Search content'],
            'tags' => ['if_exist', 'max_length' => 300],
            'sortorder' => ['if_exist', 'in_list' => ['ASC', 'DESC'], 'label' => 'Sort order'],
            'page_number' => ['if_exist', 'is_natural',
                'greater_than' => 0, 'less_than' => 10000],
            'page_entries' => ['if_exist', 'is_natural',
                'greater_than_equal_to' => 5, 'less_than_equal_to' => 150],
        ]);
        if ($validator->redirectOnError(UrlUtils::baseUrl('/faqs'))) {
            return;
        }


        $adjusted_GET_array = $_GET;
        if ($is_admin && isset($adjusted_GET_array['disabled'])) {
            if ($adjusted_GET_array['disabled'] !== 'true' && $adjusted_GET_array['disabled'] !== 'false') {
                unset($adjusted_GET_array['disabled']);
            }
        }

        // Adjust below if you want to retrieve the answers in full
        $adjusted_GET_array['show_answers'] = 'false';
        if (isset($adjusted_GET_array['content'])) {
            if (\strlen(\trim((string)$adjusted_GET_array['content'])) < 1) {
                unset($adjusted_GET_array['content']);
            }
        }

        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setQuery($adjusted_GET_array)
            ->exec('GET', $faqs_list_endpoint);
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectToErrorPage();
            return;
        }

        $faqs_list_body = $caResponse->getBody();
        $faqs_list_array = \json_decode($faqs_list_body, true, 4);

        if (!isset($faqs_list_array, $faqs_list_array['stats'], $faqs_list_array['result'])) {
            CookieMessengerWriter::setMessage(500, true, 'Invalid listing of FAQs');
            HttpResponse::redirectToErrorPage();
            return;
        }

        $data = [
            'faqs' => $faqs_list_array,
            'is_admin' => $is_admin,
            'custom_page_name' => 'FAQs',
        ];

        echo new HtmlView('main/page-content/faqs_list', true, $data);
    }


    /**
     * Can retrieve the details of a FAQ identified by its id or the contents of the question.
     */
    public function faq_details(string $faq_id = null, string $faq_question = ''): void
    {
        $is_admin = CleanDeckStatics::isAuthenticated() && CleanDeckStatics::getAccountRank() >= 1000;

        $ca_request = new CARequest();
        if (isset($faq_id)) {
            $faq_details_response =
                $ca_request->exec('GET', ($is_admin ? '/admin/faq/' : '/faq/') . $faq_id);
        } else {
            $ca_request->setQuery(['q' => $faq_question]);
            $faq_details_response =
                $ca_request->exec('GET', $is_admin ? '/admin/faq' : '/faq');
        }

        if ($faq_details_response->hasError()) {
            CookieMessengerWriter::setMessage(
                $faq_details_response->getStatusCode(),
                true,
                $faq_details_response->getBody()
            );
            HttpResponse::redirectTo(UrlUtils::baseUrl('/faqs'));
            return;
        }

        $faq_details_array = \json_decode($faq_details_response->getBody(), true, 2);
        if (!isset($faq_details_array, $faq_details_array['faq_id'])) {
            CookieMessengerWriter::setMessage(500, true, 'Invalid FAQ details.');
            HttpResponse::redirectTo(UrlUtils::baseUrl('/faqs'));
            return;
        }

        // increase the number of views
        // Important: use the id retrieved because this function may receive or not the id as parameter
        $ca_request_2 = new CARequest();
        $caResponse = $ca_request_2
            ->exec('PATCH', '/faq/views-count/' . $faq_details_array['faq_id']);
        if ($caResponse->hasError()) {
            \error_log('Could not increase the number of views: ' .
                $caResponse->getBody());
        }


        // setup download of faq_attachments
        if (isset($faq_details_array['faq_attachments'])) {
            $faq_download_dir = CLEANDECK_DYNAMIC_PATH . '/faqs/' . $faq_details_array['faq_id'];

            $faq_attachments_array = \explode(',', (string)$faq_details_array['faq_attachments']);
            $missing_attachments = [];
            $download_error_message = '';
            if (\is_dir($faq_download_dir)) {
                foreach ($faq_attachments_array as $faq_attachment_array) {
                    if (\file_exists($faq_download_dir . '/' . $faq_attachment_array)) {
                        // Important! Make sure this file is not in queue anymore
                        if (!isset($fileOpsQueue)) {
                            $fileOpsQueue = new FileOpsQueue();
                        }

                        try {
                            $fileOpsQueue->queueRemove(
                                'download',
                                $faq_download_dir,
                                'articles/' . $faq_details_array['faq_id'] . '/' . $faq_attachment_array
                            );
                        } catch (\Exception $e) {
                            $remove_failure_message = 'Could not remove download from queue: ' . $e->getMessage();
                            \error_log($remove_failure_message);
                        }
                    } else {
                        $missing_attachments[] = $faq_attachment_array;
                    }
                }
            } else {
                $attachments_dir_base_err_msg = 'Cannot create a directory for attachments';
                if (\env('cleandeck.ENVIRONMENT') === 'development') {
                    $download_error_message = $attachments_dir_base_err_msg . ': ' . $faq_download_dir;
                }
                try {
                    if (!WarningHandler::run(
                        static fn (): bool => \mkdir($faq_download_dir, 0o775, true),
                        $attachments_dir_base_err_msg)) {
                        $download_error_message = $attachments_dir_base_err_msg . '.';
                    }
                } catch (\Exception $exception) {
                    $download_error_message = $exception->getMessage();
                }

                $missing_attachments = $faq_attachments_array;
            }

            if ($missing_attachments !== []) {
                if (!isset($fileOpsQueue)) {
                    $fileOpsQueue = new FileOpsQueue();
                }

                $has_download_error = false;

                foreach ($missing_attachments as $missing_attachment) {
                    try {
                        $fileOpsQueue->queueAdd(
                            'download',
                            $faq_download_dir,
                            'faqs/' . $faq_details_array['faq_id'] . '/' . $missing_attachment
                        );
                    } catch (\Exception $e) {
                        $has_download_error = true;
                        \error_log('Cannot set download of attachments for faq id ' .
                            $faq_details_array['faq_id'] . ': ' . $e->getMessage());
                    }
                }

                if ($has_download_error) {
                    $download_error_message .= 'Error(s) while setting up download of attachments.';
                }

                // start processing queue
                new TaskHandler(TaskHandler::CLEANDECK_TASK_PROCESS_PENDING_FILE_OPS);
            }

            if ($download_error_message !== '') {
                CookieMessengerWriter::setMessage(null, true, $download_error_message);
            }
        }

        $data = [
            'faq_details' => $faq_details_array,
            'is_admin' => $is_admin,
            'custom_page_name' => 'FAQ - ' . \ucwords((string)$faq_details_array['question']),
        ];
        $data['seo_description'] = $faq_details_array['answer_summary'] ?? $faq_details_array['question'] ?? null;

        echo new HtmlView('main/page-content/faq_details', true, $data);
    }

    public function faq_details_by_question(): void
    {
        if (isset($_GET['q']) && \strlen((string)$_GET['q']) > 1) {
            $this->faq_details(null, $_GET['q']);
        } else {
            CookieMessengerWriter::setMessage(500, true, 'Invalid FAQ details.');
            HttpResponse::redirectTo(UrlUtils::baseUrl('/faqs'));
        }
    }
}
