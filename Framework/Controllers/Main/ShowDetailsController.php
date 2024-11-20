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
use Framework\Libraries\Utils\HtmlUtils;
use Framework\Libraries\Utils\ImagesUtils;
use Framework\Libraries\View\HtmlView;

final class ShowDetailsController
{
    private function common_get_details(string $details_type): void
    {
        $details_url = match ($details_type) {
            'user_details' => '/user/details',
            'active_sessions_details' => '/user/sessions-details',
            'user_failed_logins' => '/user/failed-logins',
            default => '/user/minimal-details',
        };
        // start procedure
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->exec('GET', $details_url);
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectToErrorPage();
            return;
        }


        // success!
        if ($caResponse->getStatusCode() === 204) {
            // cannot find any details
            $data = [];
        } else {
            $response_body = $caResponse->getBody();
            $response_body_array = \json_decode($response_body, true, 3);

            if (!isset($response_body_array)) {
                CookieMessengerWriter::setMessage(500, true, 'Invalid response');
                HttpResponse::redirectToErrorPage();
                return;
            }

            $response_body_array = HtmlUtils::strip_tags_turbo($response_body_array);
            $data = [
                'custom_data' => [
                    'details_list' => $response_body_array,
                ],
            ];
        }

        if ($details_type === 'user_details') {
            if (isset($response_body_array['pictures']) && \is_string($response_body_array['pictures'])) {
                $profile_picture_details = ImagesUtils::profilePictureHandler($response_body_array['pictures']);
                foreach ($profile_picture_details as $key => $value) {
                    $data[$key] = $value;
                }
            }
        }

        $main_content_file = match ($details_type) {
            'active_sessions_details' => 'authenticated/user/account_show_sessions_details',
            'user_failed_logins' => 'authenticated/user/account_show_failed_logins',
            default => 'authenticated/user/account_show_full_details',
        };
        echo new HtmlView($main_content_file, $data);
    }

    public function user_details(): void
    {
        $this->common_get_details('user_details');
    }

    public function active_sessions_details(): void
    {
        $this->common_get_details('active_sessions_details');
    }

    public function user_failed_logins(): void
    {
        $this->common_get_details('user_failed_logins');
    }
}
