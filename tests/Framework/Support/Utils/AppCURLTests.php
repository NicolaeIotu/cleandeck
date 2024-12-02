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

use Framework\Libraries\Utils\UrlUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AppCURL::class)]
final class AppCURLTests extends TestCase
{
    public function testConstructor(): void
    {
        $dot_env_dot_ini_path = CLEANDECK_ROOT_PATH . '/.env.ini';
        if (file_exists($dot_env_dot_ini_path)) {
            $settings = \parse_ini_file($dot_env_dot_ini_path, true, INI_SCANNER_TYPED);

            $home_url = $settings['cleandeck']['baseURL'];
            try {
                new AppCURL($home_url, 'desc');
            } catch (\Exception $exception) {
                // The webserver is inactive,
                // or is not handling cleandeck.baseURL.
            }

            $this->expectException(\Exception::class);
            new AppCURL($home_url . '/missing-url', 'desc');
        }

        $home_url = UrlUtils::baseUrl();
        $this->expectException(\Exception::class);
        new AppCURL($home_url, 'desc');
    }
}
