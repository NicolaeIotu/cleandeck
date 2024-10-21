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

namespace Framework\Libraries\CA;

use Framework\Libraries\Cookie\AppCookies;
use Framework\Libraries\Cookie\JWTCookiesHandler;

final class BriefUserDetails
{
    /**
     * @return array<string, mixed>
     */
    public static function get(): array
    {
        $user_details_cookie_name = AppCookies::USER_DETAILS_COOKIE_NAME();

        // extract user details
        if (isset($_COOKIE[$user_details_cookie_name])) {
            try {
                $jwt_user_details = JWTCookiesHandler::describeCookie($user_details_cookie_name);
            } catch (\Exception $e) {
                \error_log('Failed to extract user details from JWT cookie: ' . $e->getMessage());
            }
        } else {
            // If user details cookie is missing, retrieve the details again and restore the cookie.
            // CMD-Auth sets this cookie with a few endpoints (the list may update):
            // - GET /user/details
            // - PATCH /user/details
            // - PATCH /username
            // - PUT /login
            // - PUT /login/quick
            // - PUT /login/mfa-step-2
            $caRequest = new CARequest();
            $user_details_response = $caRequest
                // no multipart downstream
                ->addHeaders([
                    'content-type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                ])
                ->exec('GET', '/user/details');
            if ($user_details_response->hasError()) {
                \error_log('Failed to retrieve user details cookie: ' . $user_details_response->getBody() .
                    ' (code ' . $user_details_response->getStatusCode() . ')');
            }

            $sc_arr = $user_details_response->getHeader('set-cookie');
            if (\is_array($sc_arr) && $sc_arr !== []) {
                foreach ($sc_arr as $value) {
                    if (\str_contains((string) $value, $user_details_cookie_name)) {
                        // extracting from format like ' __Host-OUzZX07UAvPNehZA=eyJhbGciOiJSUzI1N...sds; Path=/...'
                        $pm = \preg_match('/=([^;]{8,});/', (string) $value, $matches);
                        if ($pm === 1) {
                            try {
                                $jwt_user_details = JWTCookiesHandler::describeValue($matches[1]);
                            } catch (\Exception $e) {
                                \error_log('Failed to extract user details from JWT cookie (2): ' . $e->getMessage());
                            }
                        } else {
                            \error_log('Failed to retrieve JWT cookie contents');
                        }

                        break;
                    }
                }
            }
        }

        if (isset($jwt_user_details, $jwt_user_details['user'])) {
            return (array)$jwt_user_details['user'];
        }

        return [];
    }
}
