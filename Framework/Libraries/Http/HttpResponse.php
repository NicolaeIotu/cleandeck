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

namespace Framework\Libraries\Http;

use Framework\Libraries\Utils\UrlUtils;

final class HttpResponse
{
    public static function redirectTo(string $url): void
    {
        $request_method = $_SERVER['REQUEST_METHOD'];
        if ($request_method === 'GET') {
            $response_code = 302;
        } elseif (\in_array($request_method, ['POST', 'PUT', 'DELETE'], true)) {
            $response_code = 303;
        } else {
            $response_code = 307;
        }

        \header('location: ' . $url, true, $response_code);
        exit;
    }

    public static function redirectToErrorPage(): void
    {
        self::redirectTo(UrlUtils::baseUrl('/error'));
    }

    public static function noCache(): void
    {
        \header('cache-control: max-age=0, no-store, no-cache', true);
    }

    /**
     * @param array<string, string> $headers
     */
    public static function setHeaders(array $headers, bool $replace = true): void
    {
        foreach ($headers as $h_name => $h_value) {
            \header(\strtolower($h_name) . ': ' . $h_value, $replace);
        }
    }

    /**
     * @param array<string, mixed> ...$ca_responses_headers
     */
    public static function addRemoteSetCookieHeaders(array ...$ca_responses_headers): void
    {
        foreach ($ca_responses_headers as $ca_response_header) {
            // Instead of performing the check for 'set-cookie' each time this function is called, make the check here.
            if (!isset($ca_response_header['set-cookie'])) {
                continue;
            }

            $ca_response_set_cookie = $ca_response_header['set-cookie'];

            if (\is_array($ca_response_set_cookie)) {
                foreach ($ca_response_set_cookie as $set_cookie) {
                    \header('set-cookie: ' . $set_cookie, false);
                }
            } elseif (\is_string($ca_response_set_cookie)) {
                \header('set-cookie: ' . $ca_response_set_cookie, false);
            }
        }
    }

    public static function send(int $status_code = null, string $status_message = null): void
    {
        if (isset($status_code)) {
            \http_response_code($status_code);
        }

        if (isset($status_message)) {
            echo $status_message;
        }

        exit;
    }
}
