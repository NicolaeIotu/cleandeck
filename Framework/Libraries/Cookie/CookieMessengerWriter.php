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
    // Sensitive limits. Adjust to match the settings of your server if required.
    // The strings are base64 encoded so the real size of the messaging cookie will be higher.
    public const MAXIMUM_FORM_BYTE_SIZE = 2048;
    public const MAXIMUM_FORM_ENTRY_BYTE_SIZE = 512;

    private static int $actual_form_byte_size = 0;

    /**
     * @param array<string, mixed> $target
     * @return array<string, string>
     */
    private static function byteSizeArrayFilter(array $target): array
    {
        return \array_filter($target,
            static function ($value): bool {
                if (\is_array($value)) {
                    $item = \http_build_query($value);
                } else {
                    $item = (string)$value;
                }
                $item_byte_size = \strlen($item);
                if ($item_byte_size > self::MAXIMUM_FORM_ENTRY_BYTE_SIZE) {
                    return false;
                }
                if ($item_byte_size + self::$actual_form_byte_size > self::MAXIMUM_FORM_BYTE_SIZE) {
                    return false;
                }
                self::$actual_form_byte_size += $item_byte_size;
                return true;
            },
            ARRAY_FILTER_USE_BOTH);
    }

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
            // Header size is important: filter entries by size in order to prevent header size errors.
            $form_data = self::byteSizeArrayFilter($form_data);
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
        $app_key = \env('cleandeck.app_key');
        if (is_null($app_key)) {
            return false;
        }

        $content = \str_replace('|', ';', $content);
        $signed_content = $app_key . $content;

        return $content . '|' . \md5($signed_content);
    }
}
