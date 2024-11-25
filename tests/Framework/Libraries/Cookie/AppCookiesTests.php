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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AppCookies::class)]
final class AppCookiesTests extends TestCase
{
    public function testSTATUS_COOKIE_NAME()
    {
        $original_env = [
            'cleandeck.cookie.prefix' => \env('cleandeck.cookie.prefix'),
            'cleandeck.cookie.status_cookie_name' =>
                \env('cleandeck.cookie.status_cookie_name'),
        ];
        unset($_ENV['cleandeck']['cookie']['prefix']);
        unset($_ENV['cleandeck']['cookie']['status_cookie_name']);
        $this->assertEquals(AppCookies::STATUS_COOKIE_NAME(),
            AppCookies::DEFAULT_COOKIES_PREFIX . AppCookies::DEFAULT_STATUS_COOKIE_NAME);

        $_ENV['cleandeck']['cookie']['prefix'] = $original_env['cleandeck.cookie.prefix'];
        $_ENV['cleandeck']['cookie']['status_cookie_name'] =
            $original_env['cleandeck.cookie.status_cookie_name'];
        $this->assertEquals(AppCookies::STATUS_COOKIE_NAME(),
            \env('cleandeck.cookie.prefix') .
            \env('cleandeck.cookie.status_cookie_name'));
    }

    public function testUSER_DETAILS_COOKIE_NAME()
    {
        $original_env = [
            'cleandeck.cookie.prefix' => \env('cleandeck.cookie.prefix'),
            'cleandeck.cookie.user_details_cookie_name' =>
                \env('cleandeck.cookie.user_details_cookie_name'),
        ];
        unset($_ENV['cleandeck']['cookie']['prefix']);
        unset($_ENV['cleandeck']['cookie']['user_details_cookie_name']);
        $this->assertEquals(AppCookies::USER_DETAILS_COOKIE_NAME(),
            AppCookies::DEFAULT_COOKIES_PREFIX . AppCookies::DEFAULT_USER_DETAILS_COOKIE_NAME);

        $_ENV['cleandeck']['cookie']['prefix'] = $original_env['cleandeck.cookie.prefix'];
        $_ENV['cleandeck']['cookie']['user_details_cookie_name'] =
            $original_env['cleandeck.cookie.user_details_cookie_name'];
        $this->assertEquals(AppCookies::USER_DETAILS_COOKIE_NAME(),
            \env('cleandeck.cookie.prefix') .
            \env('cleandeck.cookie.user_details_cookie_name'));
    }

    public function testPRIVATE_CACHE_COOKIE_NAME()
    {
        $original_env = [
            'cleandeck.cookie.prefix' => \env('cleandeck.cookie.prefix'),
            'cleandeck.cookie.private_cache_cookie_name' =>
                \env('cleandeck.cookie.private_cache_cookie_name'),
        ];
        unset($_ENV['cleandeck']['cookie']['prefix']);
        unset($_ENV['cleandeck']['cookie']['private_cache_cookie_name']);
        $this->assertEquals(AppCookies::PRIVATE_CACHE_COOKIE_NAME(),
            AppCookies::DEFAULT_COOKIES_PREFIX . AppCookies::DEFAULT_PRIVATE_CACHE_COOKIE_NAME);

        $_ENV['cleandeck']['cookie']['prefix'] = $original_env['cleandeck.cookie.prefix'];
        $_ENV['cleandeck']['cookie']['private_cache_cookie_name'] =
            $original_env['cleandeck.cookie.private_cache_cookie_name'];
        $this->assertEquals(AppCookies::PRIVATE_CACHE_COOKIE_NAME(),
            \env('cleandeck.cookie.prefix') .
            \env('cleandeck.cookie.private_cache_cookie_name'));
    }
}
