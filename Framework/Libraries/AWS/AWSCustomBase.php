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

namespace Framework\Libraries\AWS;

require_once __DIR__ . '/../../Config/constants.php';
require_once CLEANDECK_VENDOR_PATH . '/autoload.php';

use Aws\Result;

class AWSCustomBase
{
    /**
     * @param Result<mixed> $result
     * @param string|null $success_message
     * @param string|null $error_message
     * @param bool $return_error_status_code
     * @return bool|int
     */
    protected static function checkResult(
        Result $result,
        string $success_message = null,
        string $error_message = null,
        bool   $return_error_status_code = false
    ): bool|int {
        $metadata = $result->get('@metadata');
        if (isset($metadata) && \is_array($metadata)) {
            if (isset($metadata['statusCode'])) {
                $status_code = (int)$metadata['statusCode'];
                if ($status_code >= 200 && $status_code < 300) {
                    if (isset($success_message)) {
                        \syslog(LOG_INFO, $success_message);
                    }

                    return true;
                }

                $error_message_ = $error_message ?? 'Error';
                if (isset($metadata['errorMessage']) && \is_string($metadata['errorMessage'])) {
                    $error_message_ .= ': ' . $metadata['errorMessage'];
                }
                \syslog(LOG_ERR, $error_message_);
                \error_log($error_message_);

                return ($return_error_status_code ? $status_code : false);
            }
        } elseif ($result->get('MessageId') !== null) {
            // try to use the MessageId
            if (isset($success_message)) {
                \syslog(LOG_INFO, $success_message);
            }

            return true;
        }

        $warn_message = $error_message ?? 'Invalid AWS response';
        \syslog(LOG_ERR, $warn_message);
        \error_log($warn_message);
        return false;
    }
}
