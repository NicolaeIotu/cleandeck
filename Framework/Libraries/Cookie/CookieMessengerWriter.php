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
 * Sets custom messaging cookies used for:
 *   - error messages
 *   - success messages
 *   - informative messages
 *   - forms data restoration in case of errors
 *   a.o.
 *
 * By default, there's a byte limit of 4k for each entry of form data and
 * this limit is already extremely high. You should probably not increase that.
 * Large data should not be sent through headers.
 */
final class CookieMessengerWriter extends CookieMessenger
{
    /**
     * @param int|string|null $status_code
     * @param string|null $content
     * @param array<string, mixed>|null $form_data
     */
    public static function setMessage(
        int|string $status_code = null,
        bool       $is_error = true,
        string     $content = null,
        array      $form_data = null
    ): bool {
        CookieUtils::deleteCookie(MESSAGING_COOKIE_NAME);

        $cmsg_cookie_contents = ($status_code ?? '') . self::INTERNAL_SEPARATOR .
            ($is_error ? 'true' : 'false') . self::INTERNAL_SEPARATOR .
            ($content ?? '');

        if (isset($form_data)) {
            $form_data = \array_filter($form_data,
                static function ($value): bool {
                    $byte_limit = 1024 * 4;
                    if (\is_array($value)) {
                        return \strlen(\http_build_query($value)) < $byte_limit;
                    }
                    return \strlen((string)$value) < $byte_limit;
                },
                ARRAY_FILTER_USE_BOTH);
            $cmsg_cookie_contents .= self::INTERNAL_SEPARATOR . \http_build_query($form_data);
        }

        $signed_cmsg = self::sign_string($cmsg_cookie_contents);
        if (!\is_string($signed_cmsg)) {
            return false;
        }

        return CookieUtils::setCookie(
            MESSAGING_COOKIE_NAME,
            \base64_encode($signed_cmsg),
            \time() + self::MAX_DURATION_SECONDS
        );
    }

    private static function sign_string(string $content): bool|string
    {
        if (\strlen($content) < 1) {
            return false;
        }

        $app_key = \env('cleandeck.app_key');
        if (is_null($app_key)) {
            return false;
        }

        $content = \str_replace('|', ';', $content);
        $signed_content = $app_key . $content;

        return $content . '|' . \md5($signed_content);
    }
}
