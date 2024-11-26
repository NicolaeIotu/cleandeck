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

namespace Framework\Libraries;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CleanDeckStatics::class)]
final class CleanDeckStaticsTests extends TestCase
{
    public function testGetSetAccountRank(): void
    {
        CleanDeckStatics::setAccountRank(20);
        $this->assertEquals(CleanDeckStatics::getAccountRank(), 20);
    }

    public function testGetSetIsAuthenticated(): void
    {
        CleanDeckStatics::setAuthenticated(true);
        $this->assertTrue(CleanDeckStatics::isAuthenticated());
    }

    public function testGetSetUserDetails(): void
    {
        CleanDeckStatics::setUserDetails(['name' => 'John']);
        $userDetails = CleanDeckStatics::getUserDetails();
        $this->assertIsArray($userDetails);
        $this->assertArrayHasKey('name', $userDetails);
    }

    public function testGetSetCaptcha(): void
    {
        CleanDeckStatics::setCaptchaImageData(['data' => 'image-data']);
        $captchaImageData = CleanDeckStatics::getCaptchaImageData();
        $this->assertIsArray($captchaImageData);
        $this->assertArrayHasKey('data', $captchaImageData);
        $this->assertTrue(CleanDeckStatics::isCaptcha());
    }

    public function testGetSetCookieMessage(): void
    {
        CleanDeckStatics::setCookieMessage(['data' => 'cookie-data']);
        $cookieMessage = CleanDeckStatics::getCookieMessage();
        $this->assertIsArray($cookieMessage);
        $this->assertArrayHasKey('data', $cookieMessage);
    }

    public function testGetSetIsSeoPage(): void
    {
        CleanDeckStatics::setSeoPage(true);
        $this->assertTrue(CleanDeckStatics::isSeoPage());
    }

    public function testGetSetSkipCache(): void
    {
        CleanDeckStatics::setSkipCache(true);
        $this->assertTrue(CleanDeckStatics::skipCache());
    }

    public function testGetSetIsEmployee(): void
    {
        CleanDeckStatics::setEmployee(true);
        $this->assertTrue(CleanDeckStatics::isEmployee());
    }
}
