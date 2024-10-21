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

use Framework\Libraries\CA\BriefAuthenticationStatus;
use Framework\Libraries\CA\BriefUserDetails;
use Framework\Libraries\CleanDeckStatics;
use Framework\Libraries\Http\HttpRequest;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\UrlUtils;
use Framework\Libraries\View\HtmlView;

class ErrorController
{
    public function general_errors(): void
    {
        // Important! Used for various purposes
        $data['standard_error_page'] = true;

        // errors are handled by the header
        echo \view_main('components/header', $data);
        echo \view_main('components/footer', $data);
    }


    public function error404(): void
    {
        $current_url = UrlUtils::current_url();

        // keep the error_log below
        \error_log('[Error 404] [' . HttpRequest::getIP() . '] ' . $current_url);

        if (HttpRequest::isAJAX()) {
            HttpResponse::setHeaders([
                'content-type' => 'application/json; charset=UTF-8',
            ]);
            HttpResponse::send(404);
        } else {
            HttpResponse::setHeaders([
                'content-type' => 'text/html; charset=UTF-8',
            ]);
            $is_authenticated = BriefAuthenticationStatus::get();
            CleanDeckStatics::setAuthenticated($is_authenticated);
            if ($is_authenticated) {
                $user_details = BriefUserDetails::get();
                CleanDeckStatics::setAccountRank($user_details['account_rank'] ?? 0);
            }

            $data = [
                'custom_page_name' => 'Error 404',
                // Important!
                'standard_error_page' => true,
            ];

            echo new HtmlView('main/components/error404', true, $data);
        }
    }
}
