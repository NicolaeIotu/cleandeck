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

/**
 * Using static variables as a unified way of providing values for Controllers and Views.
 */
final class CleanDeckStatics
{
    private static int $account_rank = 0;

    private static bool $authenticated = false;

    private static bool $employee = false;

    /**
     * @var array<string, mixed>|null
     */
    private static ?array $user_details;

    /**
     * @var array<string, mixed>|null
     */
    private static ?array $captcha_image_data;

    /**
     * @var array<string, mixed>|null
     */
    private static ?array $cookie_message;

    private static bool $seo_page = false;

    private static bool $captcha = false;

    private static bool $skip_cache = false;

    public static function getAccountRank(): int
    {
        return self::$account_rank;
    }

    public static function setAccountRank(int $account_rank): void
    {
        self::$account_rank = $account_rank;
    }

    public static function isAuthenticated(): bool
    {
        return self::$authenticated;
    }

    public static function setAuthenticated(bool $authenticated): void
    {
        self::$authenticated = $authenticated;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getUserDetails(): ?array
    {
        return self::$user_details ?? null;
    }

    /**
     * @param array<string, mixed>|null $user_details
     */
    public static function setUserDetails(?array $user_details): void
    {
        self::$user_details = $user_details;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getCaptchaImageData(): ?array
    {
        return self::$captcha_image_data ?? null;
    }

    /**
     * @param array<string, mixed>|null $captcha_image_data
     */
    public static function setCaptchaImageData(?array $captcha_image_data): void
    {
        self::$captcha_image_data = $captcha_image_data;
        self::$captcha = true;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getCookieMessage(): ?array
    {
        return self::$cookie_message ?? null;
    }

    /**
     * @param array<string, mixed>|null $cookie_message
     */
    public static function setCookieMessage(?array $cookie_message): void
    {
        self::$cookie_message = $cookie_message;
    }

    public static function isSeoPage(): bool
    {
        return self::$seo_page;
    }

    public static function setSeoPage(bool $seo_page): void
    {
        self::$seo_page = $seo_page;
    }

    public static function isCaptcha(): bool
    {
        return self::$captcha;
    }

    public static function skipCache(): bool
    {
        return self::$skip_cache;
    }

    public static function setSkipCache(bool $skip_cache): void
    {
        self::$skip_cache = $skip_cache;
    }

    /**
     * @return bool
     */
    public static function isEmployee(): bool
    {
        return self::$employee;
    }

    /**
     * @param bool $employee
     */
    public static function setEmployee(bool $employee): void
    {
        self::$employee = $employee;
    }
}
