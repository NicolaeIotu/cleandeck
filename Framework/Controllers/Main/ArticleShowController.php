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

final class ArticleShowController
{
    public function articles_list(): void
    {
        $is_admin = CleanDeckStatics::isAuthenticated() && CleanDeckStatics::getAccountRank() >= 1000;

        // admins with rank at least 1000 can use a dedicated endpoint
        if ($is_admin) {
            $articles_list_endpoint = '/admin/articles';
        } else {
            $articles_list_endpoint = '/articles';
        }

        // form validation
        $validator = new Validator([
            'disabled' => ['if_exist', 'in_list' => ['--', 'true', 'false'],
                'label' => 'Show disabled articles'],
            'published' => ['if_exist', 'in_list' => ['--', 'true', 'false'],
                'label' => 'Show published articles'],
            'content' => ['if_exist', 'max_length' => 2000,
                'label' => 'Search content'],
            'tags' => ['if_exist', 'max_length' => 300],
            'sortorder' => ['if_exist', 'in_list' => ['ASC', 'DESC'],
                'label' => 'Sort order'],
            'page_number' => ['if_exist', 'is_natural',
                'greater_than' => 0, 'less_than' => 10000,
                'label' => 'Page number'],
            'page_entries' => ['if_exist', 'is_natural',
                'greater_than_equal_to' => 5, 'less_than_equal_to' => 150,
                'label' => 'Page entries'],
        ]);
        if ($validator->redirectOnError(UrlUtils::baseUrl('/articles'))) {
            return;
        }

        $adjusted_GET_array = $_GET;
        if ($is_admin) {
            if (isset($adjusted_GET_array['disabled'])) {
                if ($adjusted_GET_array['disabled'] !== 'true' && $adjusted_GET_array['disabled'] !== 'false') {
                    unset($adjusted_GET_array['disabled']);
                }
            }

            if (isset($adjusted_GET_array['published'])) {
                if ($adjusted_GET_array['published'] !== 'true' && $adjusted_GET_array['published'] !== 'false') {
                    unset($adjusted_GET_array['published']);
                }
            }
        }

        if (isset($adjusted_GET_array['content'])) {
            if (\strlen(\trim((string)$adjusted_GET_array['content'])) < 1) {
                unset($adjusted_GET_array['content']);
            }
        }

        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setQuery($adjusted_GET_array)
            ->exec('GET', $articles_list_endpoint);
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectToErrorPage();
            return;
        }

        $articles_list_body = $caResponse->getBody();
        $articles_list_array = \json_decode($articles_list_body, true, 4);

        if (!isset($articles_list_array, $articles_list_array['stats'], $articles_list_array['result'])) {
            CookieMessengerWriter::setMessage(500, true, 'Invalid listing of articles');
            HttpResponse::redirectToErrorPage();
            return;
        }

        $data = [
            'articles' => $articles_list_array,
            'is_admin' => $is_admin,
            'custom_page_name' => 'Articles',
        ];

        echo new HtmlView('articles_list', $data);
    }


    public function article_details(string $article_id = null, string $article_title = ''): void
    {
        $is_admin = CleanDeckStatics::isAuthenticated() && CleanDeckStatics::getAccountRank() >= 1000;

        $ca_request = new CARequest();
        if (isset($article_id)) {
            $article_details_response =
                $ca_request->exec('GET', ($is_admin ? '/admin/article/' : '/article/') . $article_id);
        } else {
            $ca_request->setQuery(['title' => $article_title]);
            $article_details_response =
                $ca_request->exec('GET', $is_admin ? '/admin/article' : '/article');
        }

        if ($article_details_response->hasError()) {
            CookieMessengerWriter::setMessage(
                $article_details_response->getStatusCode(),
                true,
                $article_details_response->getBody()
            );
            HttpResponse::redirectTo(UrlUtils::baseUrl('/articles'));
            return;
        }

        $article_details_array = \json_decode($article_details_response->getBody(), true, 2);
        if (!isset($article_details_array, $article_details_array['article_id'])) {
            CookieMessengerWriter::setMessage(500, true, 'Invalid article details.');
            HttpResponse::redirectTo(UrlUtils::baseUrl('/articles'));
            return;
        }

        // increase the number of views
        // Important: use the id retrieved because this function may receive or not the id as parameter
        $ca_request_2 = new CARequest();
        $caResponse = $ca_request_2
            ->exec(
                'PATCH',
                '/article/views-count/' . $article_details_array['article_id']
            );
        if ($caResponse->hasError()) {
            $error_msg = $caResponse->getBody();
            \error_log('Could not increase the number of views: ' . $error_msg);
        }

        // setup download of article_attachments
        if (isset($article_details_array['article_attachments'])) {
            $article_download_dir = CLEANDECK_DYNAMIC_PATH . '/articles/' . $article_details_array['article_id'];

            $article_attachments_array = \explode(',', (string)$article_details_array['article_attachments']);
            $missing_attachments = [];
            $download_error_message = '';
            if (\is_dir($article_download_dir)) {
                foreach ($article_attachments_array as $article_attachment_array) {
                    if (\file_exists($article_download_dir . '/' . $article_attachment_array)) {
                        // Important! Make sure this file is not in queue anymore
                        if (!isset($fileOpsQueue)) {
                            $fileOpsQueue = new FileOpsQueue();
                        }

                        try {
                            $fileOpsQueue->queueRemove(
                                'download',
                                $article_download_dir,
                                'articles/' . $article_details_array['article_id'] . '/' . $article_attachment_array
                            );
                        } catch (\Exception $e) {
                            $remove_failure_message = 'Could not remove download from queue: ' . $e->getMessage();
                            \error_log($remove_failure_message);
                        }
                    } else {
                        $missing_attachments[] = $article_attachment_array;
                    }
                }
            } else {
                $download_base_err_msg = 'Could not create a directory for static images';
                if (\env('cleandeck.ENVIRONMENT') === 'development') {
                    $download_error_message = $download_base_err_msg . ': ' . $article_download_dir;
                }
                try {
                    if (!WarningHandler::run(
                        static fn (): bool => \mkdir($article_download_dir, 0o775, true),
                        $download_base_err_msg)) {
                        $download_error_message = $download_base_err_msg . '.';
                    }
                } catch (\Exception $exception) {
                    $download_error_message = $exception->getMessage();
                }

                $missing_attachments = $article_attachments_array;
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
                            \realpath($article_download_dir),
                            'articles/' . $article_details_array['article_id'] . '/' . $missing_attachment
                        );
                    } catch (\Exception $e) {
                        $has_download_error = true;
                        \error_log('Cannot set download of attachments for article id ' .
                            $article_details_array['article_id'] . ': ' . $e->getMessage());
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
            'article_details' => $article_details_array,
            'is_admin' => $is_admin,
            'custom_page_name' => \ucwords((string)$article_details_array['article_title']),
        ];
        if (isset($article_details_array['article_summary'])) {
            $data['seo_description'] = $article_details_array['article_summary'];
        }

        echo new HtmlView('article_details', $data);
    }

    public function article_details_by_title(): void
    {
        if (isset($_GET['title']) && \strlen((string)$_GET['title']) > 1) {
            $this->article_details(null, $_GET['title']);
        } else {
            CookieMessengerWriter::setMessage(500, true, 'Invalid article details.');
            HttpResponse::redirectTo(UrlUtils::baseUrl('/articles'));
        }
    }
}
