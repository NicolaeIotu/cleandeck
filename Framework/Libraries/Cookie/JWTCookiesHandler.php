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

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class JWTCookiesHandler
{
    // The $default_public_key may be overridden by the value in file .env.ini => cleandeck.jwt_cookies_public_key
    // which should only be modified if changing the default keys on CMD-Auth server(s) at
    // /etc/cmd-auth/certificates/jwt-cookies.
    private const DEFAULT_PUBLIC_KEY = <<<EOD
        -----BEGIN PUBLIC KEY-----
        MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArwIfSrDQsQnBzuvONgC+
        tUZMs2XErmHtUAOAbfoHbjmUJntilTDMLBrwrqfQNr2HAXSW/nmgiLCtc+0UBXAE
        yMzaIWJ2nYpqSuag8jmR83j6VDvPamwRsvBvxokax5pmEjPL6k768XxvEJIqHBXF
        uKzw7IPA5WqcGvDr+2bsfRsU7NM78rORT4XUCOrVGMozcuUF1Qda/iH4sub4qSSj
        24irDxF9hkfGSBw77lTAdcsDnibexFhurJAE+KXn9uR37XrT/pHUpBlqHgRk8o7O
        n2QTbtIgXUtwtBbQBQDukW+oVNouwD1wOi2EwjtUiWUN19RuT5EL0fBTD45JfmAf
        nQIDAQAB
        -----END PUBLIC KEY-----
        EOD;


    /**
     * The method removes the signature of the JWT status cookie.
     * @param string|null $cookie_name
     * @param string|null $cookie_value
     */
    private static function extractJWTString(string $cookie_name = null, string $cookie_value = null): ?string
    {
        $exam_value = $cookie_value ?? $_COOKIE[$cookie_name] ?? null;
        if (\is_null($exam_value)) {
            return null;
        }

        // remove signature
        $jwt_dot_arr = \explode('.', (string)$exam_value);
        \array_pop($jwt_dot_arr);
        return \implode('.', $jwt_dot_arr);
    }

    /**
     * @return array<string, mixed>
     * @throws \Exception
     */
    private static function decodeJWT(string $jwt_string): array
    {
        $key = new Key(\env('cleandeck.jwt_cookies_public_key', self::DEFAULT_PUBLIC_KEY), 'RS256');
        $decoded = JWT::decode($jwt_string, $key);
        return (array)$decoded;
    }

    /**
     * @return array<string, mixed>|null
     * @throws \Exception
     */
    public static function describeCookie(string $cookie_name): ?array
    {
        $jwt_string = self::extractJWTString($cookie_name);
        if (!isset($jwt_string)) {
            return null;
        }

        return self::decodeJWT($jwt_string);
    }

    /**
     * @return array<string, mixed>|null
     * @throws \Exception
     */
    public static function describeValue(?string $value): ?array
    {
        $jwt_string = self::extractJWTString(null, $value);
        if (!isset($jwt_string)) {
            return null;
        }

        return self::decodeJWT($jwt_string);
    }
}
