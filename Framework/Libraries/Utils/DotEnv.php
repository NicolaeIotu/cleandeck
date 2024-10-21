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

final class DotEnv
{
    /**
     * @param string $dot_env_dot_ini_path
     * @return array<string, mixed>|false
     */
    public static function read(string $dot_env_dot_ini_path): array|false
    {
        return \parse_ini_file($dot_env_dot_ini_path, true, INI_SCANNER_TYPED);
    }

    /**
     * @param string $dot_env_dot_ini_path
     * @throws \Exception
     */
    public static function setEnvironment(string $dot_env_dot_ini_path): void
    {
        $result = \parse_ini_file($dot_env_dot_ini_path, true, INI_SCANNER_TYPED);
        if (!\is_array($result)) {
            throw new \Exception('Invalid configuration file: ' . $dot_env_dot_ini_path);
        }

        // add to $_ENV
        $_ENV = \array_merge($_ENV, $result);
    }
}
