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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CookieMessengerWriter::class)]
final class CookieMessengerWriterTests extends TestCase
{
    private ?string $cleandeck_app_key;

    protected function setUp(): void
    {
        $this->cleandeck_app_key = \env('cleandeck.app_key');
    }

    protected function tearDown(): void
    {
        $_ENV['cleandeck']['app_key'] = $this->cleandeck_app_key;
    }

    public function testSetMessage(): void
    {
        $this->assertTrue(CookieMessengerWriter::setMessage(200,
            false, 'content'));

        $this->assertTrue(CookieMessengerWriter::setMessage(200,
            false, 'content', ['form' => 'data']));

        $this->assertTrue(CookieMessengerWriter::setMessage(200,
            false, 'content', ['form' => ['data']]));

        $this->assertTrue(CookieMessengerWriter::setMessage(200,
            false, 'content',
            [
                'key' =>
                    \bin2hex(\random_bytes(CookieMessengerWriter::MAXIMUM_FORM_ENTRY_BYTE_SIZE)),
            ]));

        $entries = 2 * ceil(CookieMessengerWriter::MAXIMUM_FORM_BYTE_SIZE /
                CookieMessengerWriter::MAXIMUM_FORM_ENTRY_BYTE_SIZE);
        $entry_byte_size = (int)floor(CookieMessengerWriter::MAXIMUM_FORM_ENTRY_BYTE_SIZE / 2);
        $test_arr = [];
        for ($i = 0; $i < $entries; $i++) {
            $test_arr['key' . $i] = \bin2hex(\random_bytes($entry_byte_size));
        }

        $this->assertTrue(CookieMessengerWriter::setMessage(400,
            true, 'content', $test_arr));

        $_ENV['cleandeck']['app_key'] = null;
        $this->assertFalse(CookieMessengerWriter::setMessage(200,
            false, 'content'));
    }
}
