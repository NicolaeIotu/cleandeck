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

final class AppCookies
{
    // All these are CMD-Auth defaults. Do not change them!
    public const DEFAULT_COOKIES_PREFIX = '';

    public const DEFAULT_STATUS_COOKIE_NAME = 'bFZTex31NBtbdrZW';

    public const DEFAULT_USER_DETAILS_COOKIE_NAME = 'OUzZX07UAvPNehZA';

    public const DEFAULT_PRIVATE_CACHE_COOKIE_NAME = 'd8rpvAsnUfSeJpc9Vxc8';
    // End CMD-Auth defaults.

    public static function STATUS_COOKIE_NAME(): string
    {
        $cookies_prefix = \env('cleandeck.cookie.prefix') ?? self::DEFAULT_COOKIES_PREFIX;
        $status_cookie_name = \env('cleandeck.cookie.status_cookie_name') ?? self::DEFAULT_STATUS_COOKIE_NAME;
        return $cookies_prefix . $status_cookie_name;
    }

    public static function USER_DETAILS_COOKIE_NAME(): string
    {
        $cookies_prefix = \env('cleandeck.cookie.prefix') ?? self::DEFAULT_COOKIES_PREFIX;
        $user_details_cookie_name = \env('cleandeck.cookie.user_details_cookie_name') ?? self::DEFAULT_USER_DETAILS_COOKIE_NAME;
        return $cookies_prefix . $user_details_cookie_name;
    }

    public static function PRIVATE_CACHE_COOKIE_NAME(): string
    {
        $cookies_prefix = \env('cleandeck.cookie.prefix') ?? self::DEFAULT_COOKIES_PREFIX;
        $user_details_cookie_name = \env('cleandeck.cookie.private_cache_cookie_name') ?? self::DEFAULT_PRIVATE_CACHE_COOKIE_NAME;
        return $cookies_prefix . $user_details_cookie_name;
    }
}
