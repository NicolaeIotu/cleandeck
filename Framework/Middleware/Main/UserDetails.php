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

namespace Framework\Middleware\Main;

use Framework\Middleware\MiddlewareInterface;
use Framework\Libraries\CA\BriefUserDetails;
use Framework\Libraries\CA\CMDAuth;
use Framework\Libraries\CleanDeckStatics;

final class UserDetails implements MiddlewareInterface
{
    /**
     * @param array<mixed>|null $arguments
     */
    public static function before(array $arguments = null): void
    {
        $required_auth = $arguments[0] ?? null;
        $on_error_redirect_to = $arguments[1] ?? '';

        $CMDAuth = new CMDAuth($required_auth, $on_error_redirect_to);
        // If MFA in progress skip additional calls to backend
        // and only use MFA related actions performed by other middleware.
        if (!$CMDAuth->mfa_in_progress) {
            $auth_result = $CMDAuth->auth();
            if ($auth_result) {
                // Important! share authentication status
                CleanDeckStatics::setAuthenticated($CMDAuth->isAuthenticated());
            }
        }

        if ($CMDAuth->isAuthenticated()) {
            // In case cleandeck.cookie.user_details_cookie_name is missing (i.e. deleted), the following call will
            //  try to retrieve the missing details and set the corresponding cookie.
            $user_details = BriefUserDetails::get();
        } else {
            $user_details = [];
        }

        // Important! share data
        CleanDeckStatics::setAccountRank($user_details['account_rank'] ?? 0);
        CleanDeckStatics::setEmployee(isset($user_details['employee_type']) &&
            \is_string($user_details['employee_type']));
        CleanDeckStatics::setUserDetails($user_details);
    }

    /**
     * @param string $payload
     * @param array<mixed>|null $arguments
     * @return string
     */
    public static function after(string $payload, array $arguments = null): string
    {
        // must return the payload
        return $payload;
    }
}
