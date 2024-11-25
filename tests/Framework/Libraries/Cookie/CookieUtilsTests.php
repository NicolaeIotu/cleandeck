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

#[CoversClass(CookieUtils::class)]
final class CookieUtilsTests extends TestCase
{
    public function testSetCookie(): void
    {
        $this->assertTrue(CookieUtils::setCookie('name', 'value'));
        $this->assertTrue(CookieUtils::setCookie('__Host-name', 'value'));
    }

    public function testHasCookie(): void
    {
        $_COOKIE['name'] = 'value';
        $this->assertTrue(CookieUtils::hasCookie('name'));
    }

    public function testDeleteCookie(): void
    {
        $this->assertTrue(CookieUtils::deleteCookie('name'));
    }

    public function testDeleteAllCookies(): void
    {
        $this->assertTrue(CookieUtils::deleteAllCookies());
        $_COOKIE = [];
        $this->assertTrue(CookieUtils::deleteAllCookies());
    }

    public function testDeleteAllCookiesExcept(): void
    {
        $_COOKIE = [
            'name' => 'value',
            '__Host-name' => 'value',
        ];
        $this->assertTrue(CookieUtils::deleteAllCookiesExcept('name'));
        $this->assertTrue(CookieUtils::deleteAllCookiesExcept());

        $_COOKIE = [];
        $this->assertTrue(CookieUtils::deleteAllCookiesExcept('name'));
    }
}
