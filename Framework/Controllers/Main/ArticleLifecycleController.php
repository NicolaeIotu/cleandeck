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
use Framework\Libraries\Http\HttpUpload;
use Framework\Libraries\LocalQueues\FileOpsQueue;
use Framework\Libraries\Tasks\TaskHandler;
use Framework\Libraries\Utils\ConvertUtils;
use Framework\Libraries\Utils\FileSystemUtils;
use Framework\Libraries\Utils\UrlUtils;
use Framework\Libraries\View\HtmlView;

final class ArticleLifecycleController
{
    public function admin_article_new(): void
    {
        // if the account rank is less than 1000 then the article is saved disabled and another admin having
        // account rank >= 1000 can enable/publish the article

        $data = [
            'is_admin' => true,
            'upload_max_filesize' => \ini_get('upload_max_filesize'),
            'max_file_uploads' => (int)\ini_get('max_file_uploads'),
            'upload_max_filesize_bytes' => ConvertUtils::getByteSize(\ini_get('upload_max_filesize')),
        ];

        echo new HtmlView('main/page-content/authenticated/admin/article_new_or_modify', true, $data);
    }

    /**
     * Used both for new articles and when modifying existing articles.
     *
     * IMPORTANT!
     * The validation is done by CMD-Auth. This is required when changing the encoding in order for the content
     *  to survive the transfer to/from the database.
     *
     * As a reminder, CMD-Auth will always validate input, but it is better to filter calls and stop
     *  invalid requests before ever reaching CMD-Auth level in order to optimize network usage.
     */
    public function remote_request_admin_article_edit(string $article_id = null): void
    {
        $is_modify_action = isset($article_id);

        // At this point the data is base64 encoded so form validation is done by CMD-Auth alone

        $redirect_url = UrlUtils::baseUrl($is_modify_action ?
            '/admin/article/modify/' . $article_id : '/admin/article/new');

        $adjusted_POST = $_POST;

        // prepare to relay
        // add the names of any attachments
        $attachments_names = HttpUpload::uploadDetails('article_attachments', 'name');
        $has_attachments = \is_array($attachments_names) && $attachments_names !== [];
        if ($has_attachments) {
            $adjusted_POST['article_attachments'] = $attachments_names;
        }

        $remove_attachments = false;
        if ($has_attachments) {
            $encoded_attachment_names_array = [];
            foreach ($attachments_names as $attachment_name) {
                $encoded_attachment_names_array[] = \base64_encode(\mb_convert_encoding((string)$attachment_name, 'UTF-16LE'));
            }

            $adjusted_POST['article_attachments'] = $encoded_attachment_names_array;
            if ($adjusted_POST['article_attachments'] !== []) {
                $remove_attachments = true;
            }
        } else {
            if ($is_modify_action && isset($adjusted_POST['remove_attachments'])) {
                if ($adjusted_POST['remove_attachments'] === 'true') {
                    $remove_attachments = true;
                    $adjusted_POST['article_attachments'] = '';
                }

                unset($adjusted_POST['remove_attachments']);
            }
        }
        // set HTML format
        $adjusted_POST['format'] = \base64_encode(\mb_convert_encoding('html', 'UTF-16LE'));


        // CRITICAL!
        // In order to make sure that the content of the article survives the transport,
        // a conversion to base64 is done at the frontend and here (for attachments).
        // At the same time CMD-Auth's setting validation.others.character_encoding_per_request
        // must be set to *true* in order to convert base64 values to the encoding set using
        // *character_encoding_downstream* (utf16le) before storing to database.
        // If another approach is required you should adjust frontend logic, CMD-Auth's settings and this class.
        $caRequest = new CARequest();
        $caRequest
            ->addHeaders(['content-type' => 'application/x-www-form-urlencoded; charset=UTF-8'])
            ->setQuery('character_encoding_upstream=base64&character_encoding_downstream=utf16le')
            ->setBody($adjusted_POST);

        if ($is_modify_action) {
            $edit_article_response = $caRequest->exec('PATCH', '/admin/article/' . $article_id);
        } else {
            $edit_article_response = $caRequest->exec('POST', '/admin/articles');
        }

        if ($edit_article_response->hasError()) {
            CookieMessengerWriter::setMessage(
                $edit_article_response->getStatusCode(),
                true,
                $edit_article_response->getBody(),
                $_POST
            );
            HttpResponse::redirectTo($redirect_url);
            return;
        }

        // SUCCESS!
        $edit_article_response_array = \json_decode($edit_article_response->getBody(), true, 2);
        if (!isset($edit_article_response_array, $edit_article_response_array['article_id']) ||
            \strlen((string)$edit_article_response_array['article_id']) < 8) {
            // invalid response
            CookieMessengerWriter::setMessage(
                500,
                true,
                'Item added, but a valid id could not be retrieved. Attachments are NOT uploaded. Further checks required.'
            );
            HttpResponse::redirectTo($redirect_url);
            return;
        }

        if ($is_modify_action) {
            if ($article_id !== $edit_article_response_array['article_id']) {
                // unexpected
                CookieMessengerWriter::setMessage(
                    500,
                    true,
                    'Item ids mismatch. Attachments are Not uploaded. Further checks required.'
                );
                HttpResponse::redirectTo($redirect_url);
                return;
            }
        }

        $article_id = $edit_article_response_array['article_id'];

        $article_download_dir = \realpath(CLEANDECK_DYNAMIC_PATH) . '/articles/' . $article_id;

        // remove previous attachments
        if ($is_modify_action && $remove_attachments) {
            try {
                if ($has_attachments) {
                    FileSystemUtils::emptydir($article_download_dir);
                } else {
                    FileSystemUtils::deletedir($article_download_dir);
                }
            } catch (\Exception $e) {
                \error_log($e->getMessage());
            }

            $fileOpsQueue = new FileOpsQueue();
            try {
                $fileOpsQueue->queueAdd(
                    'delete',
                    'articles/' . $article_id,
                    null,
                    'directory',
                    !$has_attachments
                );
            } catch (\Exception $e) {
                $delete_queue_error_message = 'Cannot set deletion of remote content: ' . $e->getMessage();
                \error_log($delete_queue_error_message);
            }
        }

        // END remove previous attachments


        // process any attachment files
        if ($has_attachments) {
            // prepare error message
            $main_error_message = 'The article was recorded, but some attachments could not be stored. ' . PHP_EOL .
                'Please ' . UrlUtils::anchor_clean(
                    'support-cases/new',
                    'contact us',
                    ['title' => 'Open a new support case in order to send the attachments',
                        'target' => '_blank']
                ) .
                ' in order to send these attachments again: ' . PHP_EOL;
            // END prepare error message


            $attachments_error_message = '';
            try {
                $store_dir = CLEANDECK_DYNAMIC_PATH . '/articles/' . $article_id;

                $store_uploads_result = HttpUpload::store(
                    'article_attachments',
                    $store_dir
                );
            } catch (\Exception $e) {
                $attachments_error_message = $main_error_message . $e->getMessage() . PHP_EOL;
                $attachments_error_code = $e->getCode();
            }

            if (isset($store_uploads_result)) {
                if (HttpUpload::success($store_uploads_result)) {
                    if (!isset($fileOpsQueue)) {
                        $fileOpsQueue = new FileOpsQueue();
                    }

                    foreach (array_keys($store_uploads_result) as $file_name) {
                        $file_basename = \basename($file_name);
                        try {
                            $fileOpsQueue->queueAdd(
                                'upload',
                                'articles/' . $article_id . '/' . $file_basename,
                                $article_download_dir . '/' . $file_basename
                            );
                        } catch (\Exception $e) {
                            // warn admins
                            \error_log($e->getMessage());
                        }
                    }
                } else {
                    if ($store_uploads_result !== []) {
                        $failed_uploads = [];
                        foreach ($store_uploads_result as $file_name => $result) {
                            if ($result === false) {
                                $failed_uploads[] = $file_name;
                            }
                        }

                        $attachments_error_message .= \implode(', ', $failed_uploads) . PHP_EOL;
                    }
                }
            }
        }

        if (isset($fileOpsQueue)) {
            // start processing queue
            new TaskHandler(TaskHandler::CLEANDECK_TASK_PROCESS_PENDING_FILE_OPS);
        }


        // This seems to be a good time to run the cleanup of attachments.
        new TaskHandler(TaskHandler::CLEANDECK_TASK_CLEANUP_DYNAMIC_DIRECTORY,
            [
                'target_directory' => CLEANDECK_PUBLIC_PATH . '/misc/articles',
                'remove_linked_content' => true,
            ]);


        // all operations successful
        $response_message = CleanDeckStatics::getAccountRank() >= 1000 ?
            ($is_modify_action ? 'Article modified' : 'Article added')
            : 'Success! An admin having minimum account rank 1000 must enable this article.';
        if (isset($attachments_error_message) && \strlen($attachments_error_message) > 0) {
            $response_message .= PHP_EOL . $attachments_error_message;
        }

        CookieMessengerWriter::setMessage(
            $attachments_error_code ?? null,
            false,
            $response_message);
        HttpResponse::redirectTo(UrlUtils::baseUrl('/admin/article/modify/' . $article_id));
    }


    public function admin_article_modify(string $article_id): void
    {
        // if the account rank is less than 1000 then the article is saved disabled and another admin having
        // account rank >= 1000 can enable/publish the article

        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->exec('GET', '/admin/article/' . $article_id);
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectToErrorPage();
            return;
        }

        $article_details_body = $caResponse->getBody();
        $article_details_array = \json_decode($article_details_body, true, 2);

        if (!isset($article_details_array, $article_details_array['article_id'], $article_details_array['article_title'])) {
            // invalid response body
            CookieMessengerWriter::setMessage(500, true, 'Could not get valid article details');
            HttpResponse::redirectTo(UrlUtils::baseUrl('/article/' . $article_id));
            return;
        }

        if ($article_details_array['article_id'] !== $article_id) {
            CookieMessengerWriter::setMessage(500, true, 'Invalid article details');
            HttpResponse::redirectTo(UrlUtils::baseUrl('/article/' . $article_id));
            return;
        }

        $data = [
            'custom_page_name' => 'Edit article - ' . \ucfirst((string)$article_details_array['article_title']),
            'article_details' => $article_details_array,
            'is_admin' => true,
            'upload_max_filesize' => \ini_get('upload_max_filesize'),
            'max_file_uploads' => (int)\ini_get('max_file_uploads'),
            'upload_max_filesize_bytes' => ConvertUtils::getByteSize(\ini_get('upload_max_filesize')),
        ];

        echo new HtmlView('main/page-content/authenticated/admin/article_new_or_modify', true, $data);
    }

    public function remote_request_admin_article_modify(string $article_id): void
    {
        $this->remote_request_admin_article_edit($article_id);
    }

    public function remote_request_admin_article_delete(string $article_id): void
    {
        $redirect_on_error_url = UrlUtils::baseUrl('/article/' . $article_id);


        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->exec('DELETE', '/admin/article/' . $article_id);
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }


        // Delete Attachments
        // delete local files
        $article_download_dir = CLEANDECK_DYNAMIC_PATH . '/articles/' . $article_id;
        if (\is_dir($article_download_dir)) {
            try {
                FileSystemUtils::deletedir($article_download_dir);
            } catch (\Exception $e) {
                \error_log($e->getMessage());
            }
        }

        // setup deletion of cloud files
        $fileOpsQueue = new FileOpsQueue();
        try {
            $fileOpsQueue->queueAdd(
                'delete',
                'articles/' . $article_id,
                null,
                'directory',
                true
            );
        } catch (\Exception $exception) {
            $delete_queue_error_message = 'Cannot set deletion of remote content: ' . $exception->getMessage();
            \error_log($delete_queue_error_message);
        }

        // start processing queue
        new TaskHandler(TaskHandler::CLEANDECK_TASK_PROCESS_PENDING_FILE_OPS);
        // END Delete Attachments
        ///////////////////////////////////////////////////////////////////////////////////

        CookieMessengerWriter::setMessage(null, false, 'Article deleted successfully.');
        HttpResponse::redirectTo(UrlUtils::baseUrl('/articles'));
    }
}
