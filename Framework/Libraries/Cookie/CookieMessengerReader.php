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

/**
 * Reads custom messaging cookies used for:
 *   - error messages
 *   - success messages
 *   - informative messages
 *   - forms data restoration in case of errors
 *   a.o.
 */
final class CookieMessengerReader extends CookieMessenger
{
    /**
     * @return array<string, mixed>|null
     */
    private static function get_cookie_msg(): ?array
    {
        if (!isset($_COOKIE[MESSAGING_COOKIE_NAME])) {
            return null;
        }

        $cmsg_cookie = \base64_decode((string) $_COOKIE[MESSAGING_COOKIE_NAME]);
        if ($cmsg_cookie === false) {
            return null;
        }

        $cmsg_cookie = self::unsign_string($cmsg_cookie);
        if ($cmsg_cookie === false) {
            return null;
        }

        $cmsg_cookie_array = \explode(self::INTERNAL_SEPARATOR, $cmsg_cookie);
        if (!\is_array($cmsg_cookie_array)) {
            return null;
        }

        return [
            'code' => isset($cmsg_cookie_array[0]) && \strlen($cmsg_cookie_array[0]) > 0 ? (int)$cmsg_cookie_array[0] : null,
            'is_error' => isset($cmsg_cookie_array[1]) && \strtolower($cmsg_cookie_array[1]) === 'true',
            'content' => isset($cmsg_cookie_array[2]) && \strlen($cmsg_cookie_array[2]) > 0 ? $cmsg_cookie_array[2] : null,
            'form_data' => isset($cmsg_cookie_array[3]) && \strlen($cmsg_cookie_array[3]) > 0 ? $cmsg_cookie_array[3] : null,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function parse(): ?array
    {
        $get_cookie_msg_result = self::get_cookie_msg();
        CookieUtils::deleteCookie(MESSAGING_COOKIE_NAME);

        if (!\is_array($get_cookie_msg_result)) {
            return null;
        }

        $cmsg_is_error = 'true';
        $cmsg_title = null;
        $cmsg_body = null;
        $cmsg_body_footer = null;
        $cmsg_form_data = null;
        if ($get_cookie_msg_result !== []) {
            if (isset($get_cookie_msg_result['code'])) {
                $error_code = $get_cookie_msg_result['code'];
                $cmsg_title = 'Error ' . $error_code;
                $cmsg_body_footer = \env('cleandeck.error_' . $error_code . '.footer');
            }

            if (isset($get_cookie_msg_result['is_error'])) {
                $cmsg_is_error = $get_cookie_msg_result['is_error'];
            }

            if (isset($get_cookie_msg_result['content'])) {
                $cmsg_body = $get_cookie_msg_result['content'];
                if (isset($cmsg_body_footer)) {
                    $cmsg_body .= PHP_EOL . $cmsg_body_footer;
                }
            }

            if (isset($get_cookie_msg_result['form_data'])) {
                \parse_str((string) $get_cookie_msg_result['form_data'], $cmsg_form_data);
            }
        }

        return [
            'cmsg_is_error' => $cmsg_is_error,
            'cmsg_title' => $cmsg_title,
            'cmsg_body' => $cmsg_body,
            'cmsg_form_data' => $cmsg_form_data,
        ];
    }

    /**
     * Returns the value of an element in $cmsg_form_data array.
     * Use this to retrieve previous data and repopulate the forms in case of failures.
     * The data is passed using cookies.
     * Inside Views files you can always manually detect and use $cmsg_form_data array in order to repopulate forms.
     * @param array<string, mixed> $cmsg_form_data
     */
    public static function getPreviousFormData(array $cmsg_form_data, string $key, mixed $default = null): mixed
    {
        return $cmsg_form_data[$key] ?? $default ?? '';
    }

    private static function unsign_string(string $content): bool|string
    {
        if (\strlen($content) < 1 ||
            !\str_contains($content, '|')) {
            return false;
        }

        $content_array = \explode('|', $content);
        if (\count($content_array) !== 2) {
            return false;
        }

        $app_key = \env('cleandeck.app_key');
        if (is_null($app_key)) {
            return false;
        }

        $test_content = $app_key . $content_array[0];
        if ($content_array[1] === \md5($test_content)) {
            return $content_array[0];
        }
        return false;
    }
}
