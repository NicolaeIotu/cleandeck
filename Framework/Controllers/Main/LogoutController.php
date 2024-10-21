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
use Framework\Libraries\Cookie\AppCookies;
use Framework\Libraries\Cookie\CookieMessengerWriter;
use Framework\Libraries\Cookie\CookieUtils;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\UrlUtils;

final class LogoutController
{
    private function common_logout(string $logout_type, string $session_internal_id = null): void
    {
        switch ($logout_type) {
            case 'all':
                $logout_url = '/logout/all';
                $redirect_to_url = UrlUtils::baseUrl();
                break;
            case 'all_except_this':
                $logout_url = '/logout/all-except-this';
                $redirect_to_url = UrlUtils::baseUrl('/user');
                break;
            case 'session':
                $logout_url = '/logout/session/' . $session_internal_id;
                $redirect_to_url = UrlUtils::baseUrl('/active-sessions-details');
                break;
            default:
                $logout_url = '/logout';
                $redirect_to_url = UrlUtils::baseUrl();
                break;
        }

        $caRequest = new CARequest();
        $caResponse = $caRequest->exec('DELETE', $logout_url);

        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectToErrorPage();
        } else {
            if ($logout_type === 'all_except_this') {
                CookieMessengerWriter::setMessage(
                    null,
                    false,
                    'Logout of all other sessions completed successfully!'
                );
            } elseif ($logout_type === 'session') {
                // it is assumed the user did not log out of this session
                CookieMessengerWriter::setMessage(null, false, 'Session deleted successfully!');
            }

            HttpResponse::redirectTo($redirect_to_url);
        }
    }

    public function remote_request_this(): void
    {
        // Important!
        CookieUtils::deleteCookie(AppCookies::PRIVATE_CACHE_COOKIE_NAME());
        $this->common_logout('this');
    }

    public function remote_request_all(): void
    {
        // Important!
        CookieUtils::deleteCookie(AppCookies::PRIVATE_CACHE_COOKIE_NAME());
        $this->common_logout('all');
    }

    public function remote_request_all_except_this(): void
    {
        $this->common_logout('all_except_this');
    }

    public function remote_request_session(string $session_internal_id): void
    {
        $this->common_logout('session', $session_internal_id);
    }
}
