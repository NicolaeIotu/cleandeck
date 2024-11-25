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
    public function test_env(): void
    {
        $this->assertTrue(function_exists('env'));
        $abc = env('abc', 123);
        $this->assertEquals($abc, 123);
        $this->assertArrayNotHasKey('abc', $_ENV);
        $this->assertNull(env('abc'));
    }
}
