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

namespace Framework\Libraries\CSRF;

use Framework\Libraries\Cookie\CookieUtils;
use Framework\Libraries\Http\HttpRequest;

final class CSRF
{
    // Critical!
    // This allows use of multiple forms on the same page.
    // Once generated, for the same page, the same hash is used.
    private static string $random_sequence;

    public static function get_random_sequence(): string|false
    {
        return self::$random_sequence ?? false;
    }

    private static string $csrf_cookie_name;

    public static function validate(): bool
    {
        $tArr = HttpRequest::getMethod() === 'POST' ? $_POST : $_GET;
        if (!isset($tArr[CSRFConstants::CSRF_TOKEN])) {
            return false;
        }


        $o_prefix = \env('cleandeck.cookie.prefix', '') . CSRFConstants::CSRF_COOKIE_BASE_NAME;
        $csrf_cookies_arr = \array_filter($_COOKIE, static function ($k) use ($o_prefix): bool {
            return \str_starts_with($k, $o_prefix);
        }, ARRAY_FILTER_USE_KEY);

        // inspect csrf cookies one by one
        $expected_csrf_cookie_value = \sha1((string) $tArr[CSRFConstants::CSRF_TOKEN]);
        foreach ($csrf_cookies_arr as $csrf_cookie_name => $csrf_cookie_value) {
            if ($expected_csrf_cookie_value === $csrf_cookie_value) {
                try {
                    CookieUtils::deleteCookie($csrf_cookie_name);
                } catch (\Exception $exception) {
                    \error_log('Cannot delete CSRF cookie on validate: ' . $exception->getMessage());
                }
                return true;
            }
        }

        return false;
    }

    private static function getCSRFCookieName(): string
    {
        if (isset(self::$csrf_cookie_name)) {
            return self::$csrf_cookie_name;
        }

        $csrf_cookie_name = \env('cleandeck.cookie.prefix', '') . CSRFConstants::CSRF_COOKIE_BASE_NAME;

        $retry_counter = 0;
        while (isset($_COOKIE[$csrf_cookie_name]) && $retry_counter <= 10) {
            ++$retry_counter;
            try {
                $csrf_cookie_suffix = \bin2hex(\random_bytes(4));
            } catch (\Exception $exception) {
                \syslog(LOG_ERR, 'Randomness error: ' . $exception->getMessage());
                $csrf_cookie_suffix = '';
            }
            $csrf_cookie_name = \env('cleandeck.cookie.prefix', '') . CSRFConstants::CSRF_COOKIE_BASE_NAME .
                '-' . $csrf_cookie_suffix;
        }

        self::$csrf_cookie_name = $csrf_cookie_name;

        return $csrf_cookie_name;
    }

    public static function init(): string|false
    {
        if (isset(self::$random_sequence)) {
            return self::$random_sequence;
        }

        try {
            $random_sequence = \bin2hex(random_bytes(12));
        } catch (\Exception $exception) {
            \syslog(LOG_ERR, 'Randomness error: ' . $exception->getMessage());
            $random_sequence = \time() . (\env('cleandeck.app_key', ''));
        }

        try {
            if (
                CookieUtils::setCookie(
                    self::getCSRFCookieName(),
                    \sha1($random_sequence),
                    \time() + CSRFConstants::CSRF_SESSION_VALIDITY)
            ) {
                self::$random_sequence = $random_sequence;
                return $random_sequence;
            }
        } catch (\Exception $exception) {
            \error_log('Cannot set CSRF cookie: ' . $exception->getMessage());
        }

        // catch-all
        return false;
    }

    public static function cleanup(): void
    {
        try {
            CookieUtils::deleteCookie(self::getCSRFCookieName());
        } catch (\Exception $exception) {
            \error_log('Cannot delete CSRF cookie on cleanup: ' . $exception->getMessage());
        }
    }
}
