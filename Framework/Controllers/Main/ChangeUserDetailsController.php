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
use Framework\Libraries\Email\EmailTemplates;
use Framework\Libraries\Email\SendEmail;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Http\HttpUpload;
use Framework\Libraries\LocalQueues\FileOpsQueue;
use Framework\Libraries\Tasks\TaskHandler;
use Framework\Libraries\Utils\WarningHandler;
use Framework\Libraries\Utils\HtmlUtils;
use Framework\Libraries\Utils\ImageResizer;
use Framework\Libraries\Utils\ImagesUtils;
use Framework\Libraries\Utils\UrlUtils;
use Framework\Libraries\Validator\Validator;
use Framework\Libraries\View\HtmlView;

final class ChangeUserDetailsController
{
    private string $new_profile_picture_filename;

    private string $profile_picture_static_path;

    public function index(): void
    {
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->exec('GET', '/user/details');
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
        $response_body_array = \json_decode($response_body, true, 2);

        if (!isset($response_body_array)) {
            CookieMessengerWriter::setMessage(
                500,
                true,
                'Invalid response body when requesting user details.'
            );
            HttpResponse::redirectToErrorPage();
            return;
        }

        $response_body_array = HtmlUtils::strip_tags_turbo($response_body_array);

        $cleandeck_user_details = [
            // form data
            'email' => $response_body_array['email'] ?? null,
            'username' => $response_body_array['username'] ?? null,
            'firstname' => $response_body_array['firstname'] ?? null,
            'lastname' => $response_body_array['lastname'] ?? null,
            'company_name' => $response_body_array['company_name'] ?? null,
            'avatar' => $response_body_array['avatar'] ?? null,
            'intro' => $response_body_array['intro'] ?? null,
            'description' => $response_body_array['description'] ?? null,
            'contact_details' => $response_body_array['contact_details'] ?? null,
            'web_profile' => $response_body_array['web_profile'] ?? null,
            'date_of_birth' => $response_body_array['date_of_birth'] ?? null,
            'gender' => $response_body_array['gender'] ?? null,
            'country' => $response_body_array['country'] ?? null,
            'city' => $response_body_array['city'] ?? null,
            'address' => $response_body_array['address'] ?? null,
            'postal_code' => $response_body_array['postal_code'] ?? null,
            'subscribed_newsletter_timestamp' => $response_body_array['subscribed_newsletter_timestamp'] ?? null,
            'subscribed_promotions_timestamp' => $response_body_array['subscribed_promotions_timestamp'] ?? null,
            'email_1' => $response_body_array['email_1'] ?? null,
            'email_2' => $response_body_array['email_2'] ?? null,
            'email_3' => $response_body_array['email_3'] ?? null,
            'email_4' => $response_body_array['email_4'] ?? null,
            'email_5' => $response_body_array['email_5'] ?? null,
        ];

        // for this application only the first picture is used as profile picture
        // this should be a string or null
        if (isset($response_body_array['pictures']) && \is_string($response_body_array['pictures'])) {
            $profile_picture_details = ImagesUtils::profilePictureHandler($response_body_array['pictures']);
            foreach ($profile_picture_details as $key => $value) {
                $cleandeck_user_details[$key] = $value;
            }
        }

        if (isset($response_body_array['notification_options']) && \is_string($response_body_array['notification_options'])) {
            $cleandeck_user_details['notification_options'] = \explode(',', $response_body_array['notification_options']);
        } else {
            $cleandeck_user_details['notification_options'] = null;
        }

        CleanDeckStatics::setUserDetails(\array_merge(CleanDeckStatics::getUserDetails(), $cleandeck_user_details));

        HttpResponse::noCache();

        echo new HtmlView('main/page-content/authenticated/user/account_change_user_details');
    }

    /**
     * @throws \Exception
     */
    private function getNewProfilePictureFilename(
        string $uploaded_profile_picture_filename,
        string $user_id
    ): void {
        $new_profile_picture_extension = \pathinfo($uploaded_profile_picture_filename, PATHINFO_EXTENSION);
        $new_profile_picture_filename = \hash('sha256', $user_id);
        if (\strlen($new_profile_picture_extension) < 3) {
            throw new \Exception('Could not generate a proper name for the profile picture', 500);
        }

        $new_profile_picture_filename .= '.' . $new_profile_picture_extension;

        $this->new_profile_picture_filename = $new_profile_picture_filename;
    }

    /**
     * @param string|null $previous_profile_picture_filename
     * @throws \Exception
     */
    private function storeUploadedPictures(
        string $user_id,
        string $profile_picture_static_dir,
        string $previous_profile_picture_filename = null
    ): void {
        $upload_failure_status_code = null;
        $upload_failure_message = null;

        // The image name is always the same for each individual user.
        $profile_picture = HttpUpload::uploadDetails('pictures');
        $uploaded_profile_picture_filename = $profile_picture['name'];

        // throws on error
        $this->getNewProfilePictureFilename($uploaded_profile_picture_filename, $user_id);

        // unlink any existing pics of this user
        if (isset($previous_profile_picture_filename)) {
            // unlink any existing pics in directory public (just in case)
            WarningHandler::run(static fn (): bool => \unlink($profile_picture_static_dir . '/' . $previous_profile_picture_filename), null, false);
        }

        $profile_picture_upload_path = $profile_picture_static_dir .
            '/' . $this->new_profile_picture_filename;
        $upload_result = HttpUpload::store('pictures',
            $profile_picture_static_dir, $this->new_profile_picture_filename);

        if (HttpUpload::success($upload_result)) {
            $this->profile_picture_static_path = $profile_picture_static_dir . '/' .
                $this->new_profile_picture_filename;

            // resize to max 128 x 128
            $imageResizer = new ImageResizer();
            $imageResizer->run(
                $profile_picture_upload_path,
                $profile_picture_upload_path,
                128,
                128
            );

            if (WarningHandler::run(
                fn (): bool => \rename($profile_picture_upload_path, $this->profile_picture_static_path),
                null, false)) {
                $fileOpsQueue = new FileOpsQueue();

                $fileOpsQueue->queueAdd(
                    'delete',
                    'user-pics/' . $this->new_profile_picture_filename
                );
                $fileOpsQueue->queueAdd(
                    'upload',
                    'user-pics/' . $this->new_profile_picture_filename,
                    $this->profile_picture_static_path
                );
            } else {
                WarningHandler::run(static fn (): bool => \unlink($profile_picture_upload_path), null, false);

                $upload_failure_status_code = 500;
                $upload_failure_message = 'Could not store the image locally';
                if (\env('cleandeck.ENVIRONMENT') === 'development') {
                    $upload_failure_message .= ': cannot move picture to final destination';
                }
            }
        } else {
            $upload_failure_status_code = 500;
            $upload_failure_message = 'Could not store the image locally';
            if (\env('cleandeck.ENVIRONMENT') === 'development') {
                $upload_failure_message .= ': cannot move picture to initial destination';
            }
        }

        if (isset($upload_failure_status_code, $upload_failure_message)) {
            throw new \Exception($upload_failure_message, $upload_failure_status_code);
        }
    }

    /**
     * @throws \Exception
     */
    private function picturesHandler(
        string $profile_picture_static_dir,
        string $user_id,
        bool   $remove_previous_profile_picture,
        string $previous_profile_picture_filename = null
    ): void {
        if (HttpUpload::hasError('pictures')) {
            $upload_failure_status_code = 500;
            $upload_failure_message = HttpUpload::getError('pictures');

            if (isset($previous_profile_picture_filename) &&
                $remove_previous_profile_picture) {
                $previous_profile_picture_path = $profile_picture_static_dir . '/' .
                    $previous_profile_picture_filename;

                WarningHandler::run(static fn (): bool => \unlink($previous_profile_picture_path), null, false);
                $fileOpsQueue = new FileOpsQueue();
                try {
                    $fileOpsQueue->queueAdd(
                        'delete',
                        'user-pics/' . $previous_profile_picture_filename
                    );
                } catch (\Exception $e) {
                    $upload_failure_message .= '.' . PHP_EOL .
                        'Could not queue removal of previous profile picture';
                    if (\env('cleandeck.ENVIRONMENT') === 'development') {
                        $upload_failure_message .= ': ' . $e->getMessage();
                    }
                }
            }
        } else {
            try {
                $this->storeUploadedPictures(
                    $user_id,
                    $profile_picture_static_dir,
                    $previous_profile_picture_filename
                );
            } catch (\Exception $e) {
                $upload_failure_status_code = 500;
                $upload_failure_message = 'Could not store the image locally';
                if (\env('cleandeck.ENVIRONMENT') === 'development') {
                    $upload_failure_message .= ': ' . $e->getMessage();
                }
            }
        }

        if (isset($upload_failure_status_code, $upload_failure_message)) {
            throw new \Exception($upload_failure_message, $upload_failure_status_code);
        }
    }

    public function remote_request(): void
    {
        $redirect_on_error_url = UrlUtils::baseUrl('/change-user-details');

        // form validation
        $validator = new Validator([
            'has_pic' => ['regex_match' => '/^[01]?$/', 'label' => 'picture check'],
            'firstname' => ['max_length' => 100],
            'lastname' => ['max_length' => 100],
            'company_name' => ['max_length' => 100],
            'intro' => ['max_length' => 150, 'label' => 'Profile intro'],
            'description' => ['max_length' => 3000, 'label' => 'Profile description'],
            'contact_details' => ['max_length' => 3000],
            'web_profile' => ['max_length' => 3000],
            'date_of_birth' => ['permit_empty', 'max_length' => 10, 'regex_match' => '/^\d{4}-\d{2}-\d{2}$/'],
            'gender' => ['permit_empty', 'in_list' => ['male', 'female', 'other']],
            'country' => ['max_length' => 40],
            'city' => ['max_length' => 60],
            'address' => ['max_length' => 1000],
            'postal_code' => ['max_length' => 20],
            'email_1' => ['permit_empty', 'email'],
            'email_2' => ['permit_empty', 'email'],
            'email_3' => ['permit_empty', 'email'],
            'email_4' => ['permit_empty', 'email'],
            'email_5' => ['permit_empty', 'email'],
        ]);
        if ($validator->redirectOnError($redirect_on_error_url, $_POST)) {
            return;
        }

        // retrieve the details required for further checks
        $ca_request = new CARequest();
        $caResponse = $ca_request
            ->addHeaders([
                'content-type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            ])
            ->exec('GET', '/user/details');
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

        // extract user_id
        $user_details_array = \json_decode($caResponse->getBody(), true, 2);
        if (!isset($user_details_array,
            $user_details_array['user_id'])) {
            CookieMessengerWriter::setMessage(
                500,
                true,
                'Could not retrieve essential user details.',
                $_POST
            );
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        // extract the initial profile picture name
        if (isset($user_details_array['pictures']) && \is_string($user_details_array['pictures'])) {
            $previous_profile_picture_filename = \explode(',', $user_details_array['pictures'])[0];
        }


        $user_images_dir = CLEANDECK_DYNAMIC_PATH . '/user-pics';
        if (!\file_exists($user_images_dir)) {
            $static_dir_base_err_msg = 'Could not create a directory for static images';
            if (\env('cleandeck.ENVIRONMENT') === 'development') {
                $static_dir_base_err_msg .= ': ' . $user_images_dir;
            }
            try {
                if (!WarningHandler::run(
                    static fn (): bool => \mkdir($user_images_dir, 0o775, true),
                    $static_dir_base_err_msg)) {
                    $pp_static_dir_error = $static_dir_base_err_msg . '.';
                }
            } catch (\Exception $exception) {
                $pp_static_dir_error = $exception->getMessage();
            }
        } else {
            if (!\is_dir($user_images_dir)) {
                $pp_static_dir_error = 'Path is not a directory (1)';
            }
        }

        if (isset($pp_static_dir_error)) {
            CookieMessengerWriter::setMessage(
                500,
                true,
                $pp_static_dir_error,
                $_POST
            );
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        $profile_picture_static_dir = \realpath($user_images_dir);

        $remove_previous_profile_picture = isset($_POST['has_pic']) &&
            $_POST['has_pic'] === '0' &&
            isset($previous_profile_picture_filename);

        // the image is stored locally in all cases


        // a single picture at the moment
        if (HttpUpload::fieldHasEntries('pictures')) {
            try {
                $this->picturesHandler(
                    $profile_picture_static_dir,
                    $user_details_array['user_id'],
                    $remove_previous_profile_picture,
                    $previous_profile_picture_filename ?? null
                );
            } catch (\Exception $e) {
                CookieMessengerWriter::setMessage($e->getCode(), true, $e->getMessage(), $_POST);
                HttpResponse::redirectTo($redirect_on_error_url);
                return;
            }
        }

        // end store profile picture locally
        ////////////////////////////////////////////////////////////////////////////////////////////


        // adjust the content to be forwarded
        $request_body = $_POST;
        if (isset($this->new_profile_picture_filename)) {
            $request_body['pictures'] = $this->new_profile_picture_filename;
        } else {
            if ($remove_previous_profile_picture) {
                $request_body['pictures'] = '';
            }
        }

        if (!isset($request_body['subscribed_newsletter'])) {
            $request_body['subscribed_newsletter'] = '0';
        }

        if (!isset($request_body['subscribed_promotions'])) {
            $request_body['subscribed_promotions'] = '0';
        }

        if (!isset($request_body['notification_options'])) {
            $request_body['notification_options'] = 'none';
        }

        unset($request_body['has_pic']);
        // end adjust the content to be forwarded


        ///////////////////////////////////////////////////////////////////////////////////
        // start procedure to change user details
        $ca_request_3 = new CARequest();
        $change_user_details_response = $ca_request_3
            ->addHeaders([
                'content-type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            ])
            ->setBody($request_body)
            ->exec('PATCH', '/user/details');
        if ($change_user_details_response->hasError()) {
            if (isset($this->profile_picture_static_path)) {
                // remove the profile picture
                WarningHandler::run(fn (): bool => \unlink($this->profile_picture_static_path), null, false);
                // @ -> maybe the file is not on this server
                if (!isset($fileOpsQueue)) {
                    $fileOpsQueue = new FileOpsQueue();
                }

                try {
                    $fileOpsQueue->queueRemoveLastInsert();
                } catch (\Exception $e) {
                    $remove_failure_message = 'Could not remove picture from queue';
                    if (\env('cleandeck.ENVIRONMENT') === 'development') {
                        $remove_failure_message .= ': ' . $e->getMessage();
                    }
                }
            }

            CookieMessengerWriter::setMessage(
                $change_user_details_response->getStatusCode(),
                true,
                $change_user_details_response->getBody() . PHP_EOL . ($remove_failure_message ?? ''),
                $_POST
            );
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        if ($remove_previous_profile_picture) {
            //remove the profile picture
            WarningHandler::run(static fn (): bool => \unlink($profile_picture_static_dir . '/' .
                $previous_profile_picture_filename), null, false);
            // @ -> maybe the file is not on this server
            if (!isset($fileOpsQueue)) {
                $fileOpsQueue = new FileOpsQueue();
            }

            try {
                $fileOpsQueue->queueAdd(
                    'delete',
                    'user-pics/' . $previous_profile_picture_filename
                );
            } catch (\Exception $e) {
                $delete_queue_error_message = 'Cannot set deletion of remote content: ' . $e->getMessage();
                \syslog(LOG_ERR, $delete_queue_error_message);
                \error_log($delete_queue_error_message);
            }
        }

        // SUCCESS!
        if ($change_user_details_response->getStatusCode() === 204) {
            CookieMessengerWriter::setMessage(null, false, 'Details modified successfully.');
        } else {
            // this is the case when the response might contain activation codes for emails
            $response_body = $change_user_details_response->getBody();
            $response_body_array = \json_decode($response_body, true, 2);

            if (!isset($response_body_array)) {
                // unexpected response body ... redirect to / which should check further
                CookieMessengerWriter::setMessage(
                    null,
                    false,
                    "Details modified successfully, but the response was invalid: " . \json_last_error_msg()
                );
            } else {
                $response_body_array = HtmlUtils::strip_tags_turbo($response_body_array);

                // handle the emails here (maybe in the future something else will come here also)
                $pending_emails_array = \array_filter($response_body_array, static function ($key): bool {
                    return \preg_match('/^(email_[1-5]|activation_hash_email_[1-5])$/', $key) === 1;
                }, ARRAY_FILTER_USE_KEY);
                $response_message = '';
                $has_activations = false;
                $response_message_emails = '';
                $email_error = false;
                $email_error_message = 'There was an error when trying to send the activation email to: ' . PHP_EOL;

                if ($pending_emails_array !== []) {
                    $is_dev_env = \env('cleandeck.ENVIRONMENT') !== 'production';
                    $sendEmailResults = [];

                    for ($i = 1; $i < 6; ++$i) {
                        if (\array_key_exists('email_' . $i, $pending_emails_array) &&
                            \array_key_exists('activation_hash_email_' . $i, $pending_emails_array)) {
                            if ($has_activations === false) {
                                $has_activations = true;
                                $response_message .= 'The following email(s) must be activated: ';
                            }

                            // send AWS SES email
                            $activation_link = UrlUtils::baseUrl('/activate-email') . '?' . \http_build_query([
                                    'email' => $pending_emails_array['email_' . $i],
                                    'activation_hash' => $pending_emails_array['activation_hash_email_' . $i],
                                ]);

                            try {
                                $individual_email_result = SendEmail::init(
                                    'Activate Email',
                                    $pending_emails_array['email_' . $i],
                                    EmailTemplates::buildEmail(
                                        EmailTemplates::ACTIVATE_EMAIL,
                                        $activation_link,
                                        $activation_link,
                                        $activation_link
                                    )
                                );
                            } catch (\Exception) {
                                $individual_email_result = false;
                            }

                            $sendEmailResults[$pending_emails_array['email_' . $i]] = $individual_email_result;


                            if (\strlen($response_message_emails) > 0) {
                                $response_message_emails .= ', ';
                            }

                            $response_message_emails .= $pending_emails_array['email_' . $i];
                            // during development show the activation hash
                            if ($is_dev_env) {
                                $response_message_emails .= ' (activation_hash: ' . $pending_emails_array['activation_hash_email_' . $i] . ')';
                            }
                        }
                    }


                    // handle the results from AWS SES
                    foreach ($sendEmailResults as $email => $sendEmailResult) {
                        if ($sendEmailResult === false) {
                            $email_error = true;
                            $email_error_message .= ' - ' . $email . PHP_EOL;
                        }
                    }
                }

                $final_response_message = 'Details modified successfully. ' . PHP_EOL .
                    $response_message . $response_message_emails;
                if ($email_error) {
                    $final_response_message .= PHP_EOL . $email_error_message . PHP_EOL .
                        'You can retry this operation with the same email(s) after deleting the email(s) which encountered errors.';
                }

                // end handle the email
                CookieMessengerWriter::setMessage(
                    null,
                    $email_error,
                    nl2br($final_response_message)
                );
            }
        }

        if (isset($fileOpsQueue)) {
            // start processing queue
            new TaskHandler(TaskHandler::CLEANDECK_TASK_PROCESS_PENDING_FILE_OPS);
        }


        // This seems to be a good time to run the cleanup of user images.
        new TaskHandler(TaskHandler::CLEANDECK_TASK_CLEANUP_DYNAMIC_DIRECTORY,
            [
                'target_directory' => CLEANDECK_PUBLIC_PATH . '/misc/user-pics',
                'remove_linked_content' => true,
            ]);

        HttpResponse::redirectTo(UrlUtils::baseUrl('/account-details'));
    }
}
