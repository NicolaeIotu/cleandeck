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

namespace Framework\Libraries;

require_once CLEANDECK_FRAMEWORK_PATH . '/Libraries/common.php';

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\TestCase;

#[CoversFunction('env')]
#[CoversFunction('view')]
#[CoversFunction('view_app')]
#[CoversFunction('view_main')]
#[CoversFunction('view_addon')]
final class CommonTests extends TestCase
{
    public function testEnv(): void
    {
        $this->assertTrue(\function_exists('env'));
        $abc = \env('abc', 123);
        $this->assertEquals($abc, 123);
        $this->assertArrayNotHasKey('abc', $_ENV);
        $this->assertNull(\env('abc'));

        $_ENV['test']['arr'] = 'value';
        $test_arr = \env('test.arr');
        $this->assertEquals($test_arr, 'value');

        $test_arr = \env('test.arr2', 'default');
        $this->assertEquals($test_arr, 'default');
        unset($_ENV['test']);
    }

    public function testView(): void
    {
        $view_data = \view(
            ['<body>', '</body>'],
            ['key' => 'value']
        );
        $this->assertEquals($view_data, '<body>' . PHP_EOL . '</body>' . PHP_EOL);

        $error_reporting = \error_reporting();
        \error_reporting($error_reporting ^ (E_COMPILE_WARNING | E_CORE_WARNING));
        $view_data = \view(
            ['missing-file'],
        );
        \error_reporting($error_reporting);
        $this->assertTrue(\strlen($view_data) > 0);
    }

    public function testView_app(): void
    {
        $error_reporting = \error_reporting();
        \error_reporting($error_reporting ^ (E_COMPILE_WARNING | E_CORE_WARNING));
        $view_app_data = \view_app('missing-file');
        \error_reporting($error_reporting);
        $this->assertTrue(\strlen($view_app_data) > 0);
    }

    public function testView_main(): void
    {
        $view_main_data = \view_main('page-content/contact');
        $this->assertTrue(\strlen($view_main_data) > 0);
    }

    public function testView_addon(): void
    {
        $error_reporting = \error_reporting();
        \error_reporting($error_reporting ^ (E_COMPILE_WARNING | E_CORE_WARNING));
        $view_addon_data = \view_addon('missing-file', 'addon-name');
        \error_reporting($error_reporting);
        $this->assertTrue(\strlen($view_addon_data) > 0);
    }
}
