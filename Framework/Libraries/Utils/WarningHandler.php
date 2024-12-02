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

namespace Framework\Libraries\Utils;

class WarningHandler
{
    /**
     * Run callable and return result. Throws exception or logs error for warnings captured.
     * @param callable $op A callable i.e. arrow function: fn() => ...
     * @param string|null $error_prefix If specified, the error prefix plus sequence <strong>'.' . PHP_EOL</strong>
     *   will be appended to any error messages encountered.
     * @param bool $throw On warning throw error (true) or log error (false).
     * @param int|null $error_code
     * @param mixed ...$args Arguments for callable $op.
     * @return mixed
     * @throws \Exception
     */
    public static function run(callable $op,
                               string   $error_prefix = null,
                               bool     $throw = true,
                               int      $error_code = null,
                               mixed    ...$args): mixed
    {
        set_error_handler(
        /**
         * @throws \Exception
         */
            static function (int $errno, string $errstr) use ($error_prefix, $throw, $error_code): void {
                if ($errno !== 0) {
                    $err_msg = (isset($error_prefix) ? $error_prefix . '.' . PHP_EOL : '') . $errstr . '.';
                    if ($throw) {
                        restore_error_handler();
                        throw new \Exception($err_msg, $error_code ?? $errno);
                    }
                    \error_log($err_msg);
                    \syslog(LOG_WARNING, $err_msg);
                }
            }, E_WARNING);

        $result = $op(...$args);

        restore_error_handler();

        return $result;
    }
}
