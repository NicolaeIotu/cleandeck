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

namespace Framework\Libraries\Cookie;

final class CookieUtils extends CookieBase
{
    /**
     * @param string $cookie_name Full cookie name including any prefixes.
     * @param string $cookie_value
     * @param int $cookie_expires
     * @return bool
     */
    public static function setCookie(
        string $cookie_name,
        string $cookie_value,
        int    $cookie_expires = 0,
    ): bool {
        $opts = [
            'expires' => $cookie_expires,
            'path' => \env('cleandeck.cookie.path', '/'),
            'secure' => \env('cleandeck.cookie.secure', true),
            'httponly' => \env('cleandeck.cookie.httponly', true),
            'samesite' => \in_array($cookie_name, self::LAX_COOKIES) ?
                'Lax' : \env('cleandeck.cookie.samesite', 'Lax'),
        ];
        // Important!
        if (!\str_starts_with($cookie_name, '__Host-')) {
            $opts['domain'] = \env('cleandeck.cookie.domain', '');
        }

        return \setcookie($cookie_name, $cookie_value, $opts);
    }

    /**
     * For convenience only.
     * @param string $cookie_name
     * @return bool
     */
    public static function hasCookie(string $cookie_name): bool
    {
        return isset($_COOKIE[$cookie_name]);
    }

    /**
     * @param string $cookie_name
     * @return bool
     */
    public static function deleteCookie(string $cookie_name): bool
    {
        return self::setCookie($cookie_name, '', \time() - 10000000);
    }

    /**
     * @return bool
     */
    public static function deleteAllCookies(): bool
    {
        if ($_COOKIE === []) {
            return true;
        }

        $result = true;

        foreach (\array_keys($_COOKIE) as $c_name) {
            $result &= self::setCookie($c_name, '', \time() - 10000000);
        }

        return $result;
    }

    /**
     * @param string ...$excepted_cookie_names
     * @return bool
     */
    public static function deleteAllCookiesExcept(string ...$excepted_cookie_names): bool
    {
        if ($_COOKIE === []) {
            return true;
        }

        $result = true;

        if ($excepted_cookie_names !== []) {
            foreach (\array_keys($_COOKIE) as $c_name) {
                if (!\in_array($c_name, $excepted_cookie_names)) {
                    $result &= self::setCookie($c_name, '', \time() - 10000000);
                }
            }
        } else {
            \header_remove('cookie');
        }

        return $result;
    }
}
