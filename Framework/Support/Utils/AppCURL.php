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

namespace Framework\Support\Utils;

use Framework\Libraries\Http\HttpRequest;

final class AppCURL
{
    /**
     * @throws \Exception
     */
    public function __construct(string $url, string $description)
    {
        if (!HttpRequest::isCLI()) {
            // @codeCoverageIgnoreStart
            throw new \Exception('[AppCURL error - ' . $description . '] This is a CLI operation only!');
            // @codeCoverageIgnoreEnd
        }

        $curl_options = [
            CURLOPT_HEADER => true,
            CURLOPT_FRESH_CONNECT => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS => 0,
            CURLOPT_FAILONERROR => false,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_ENCODING => '',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ];

        $curl_options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_2TLS;

        $curl_options[CURLOPT_URL] = $url;

        $ch = \curl_init();
        \curl_setopt_array($ch, $curl_options);
        $curl_response = \curl_exec($ch);

        // When CURLOPT_RETURNTRANSFER is set to 'true', \curl_exec() will return bool(true) for empty responses
        if ($curl_response === false || $curl_response === '' || $curl_response === '0') {
            // @codeCoverageIgnoreStart
            $curl_error_description = \curl_strerror(\curl_errno($ch));
            // @codeCoverageIgnoreEnd
        }
        \curl_close($ch);

        if (isset($curl_error_description)) {
            // @codeCoverageIgnoreStart
            throw new \Exception('[AppCURL error - ' . $description . '] ' . $curl_error_description);
            // @codeCoverageIgnoreEnd
        }

        $curl_info = \curl_getinfo($ch);
        if ($curl_info['http_code'] > 299) {
            $curl_response_arr = explode(PHP_EOL, $curl_response);
            $err_msg = '';
            $is_content = false;
            foreach ($curl_response_arr as $value) {
                if (trim($value) === '') {
                    $is_content = true;
                }
                if ($is_content) {
                    $err_msg .= $value;
                }
            }

            throw new \Exception('[AppCURL http error - ' . $description . '] Error ' . $curl_info['http_code'] .
                ' - ' . $err_msg);
        }
    }
}
