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
use Framework\Libraries\Utils\HtmlUtils;
use Framework\Libraries\View\HtmlView;

final class UserController
{
    public function index(): void
    {
        // tentatively do not redirect this page in case some info cannot be retrieved
        $user_home_response_body_array = [];
        $articles_response_body_array = [];

        $ca_request = new CARequest();
        $caResponse = $ca_request
            ->setQuery(['page_entries' => 5])
            ->exec('GET', '/articles');
        if (!$caResponse->hasError()) {
            $articles_response_body_array = \json_decode($caResponse->getBody(), true, 4);
        }


        $ca_request_2 = new CARequest();
        $user_home_response = $ca_request_2
            ->exec('GET', '/user/home');
        if (!$user_home_response->hasError()) {
            $user_home_response_body_array = HtmlUtils::strip_tags_turbo(
                \json_decode($user_home_response->getBody(), true, 3)
            );
        }


        $data = [
            'account_overview' => $user_home_response_body_array,
            'articles' => $articles_response_body_array,
            'seo_description' => 'Account Home',
            'custom_page_name' => 'Account Home',
        ];

        echo new HtmlView('authenticated/user/account_home', $data);
    }
}
