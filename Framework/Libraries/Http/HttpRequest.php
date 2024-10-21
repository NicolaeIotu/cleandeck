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

final class HttpRequest
{
    public static function getIP(): string
    {
        // Can be improved with validation etc.
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public static function isAJAX(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            \strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public static function isCLI(): bool
    {
        return !isset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    }

    /**
     * @return string Request method uppercase, or 'UNKNOWN'.
     */
    public static function getMethod(): string
    {
        return \strtoupper($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN');
    }

    public static function getHeader(string $header_name): string|false
    {
        $real_header_name = 'HTTP_' . \str_replace("-", "_", \strtoupper($header_name));

        if (isset($_SERVER[$real_header_name])) {
            return $_SERVER[$real_header_name];
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    public static function getHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $k => $v) {
            if (\str_starts_with($k, "HTTP_")) {
                $k = \str_replace("_", "-", \substr($k, 5));
                $headers[$k] = $v;
            }
        }

        return \array_change_key_case($headers, CASE_LOWER);
    }
}
