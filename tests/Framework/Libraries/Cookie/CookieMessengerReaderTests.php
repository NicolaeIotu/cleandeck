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

#[CoversClass(CookieMessengerReader::class)]
final class CookieMessengerReaderTests extends TestCase
{
    private ?string $cleandeck_app_key;

    protected function setUp(): void
    {
        $this->cleandeck_app_key = \env('cleandeck.app_key');
    }

    protected function tearDown(): void
    {
        unset($_COOKIE[MESSAGING_COOKIE_NAME]);
        $_ENV['cleandeck']['app_key'] = $this->cleandeck_app_key;
    }

    public function testParse(): void
    {
        $this->assertNull(CookieMessengerReader::parse());

        // base64_decode fail
        $_COOKIE[MESSAGING_COOKIE_NAME] = \random_bytes(20);
        $this->assertNull(CookieMessengerReader::parse());
        unset($_COOKIE[MESSAGING_COOKIE_NAME]);

        // invalid value
        $_COOKIE[MESSAGING_COOKIE_NAME] = 'abc';
        $this->assertNull(CookieMessengerReader::parse());
        unset($_COOKIE[MESSAGING_COOKIE_NAME]);

        $content = '400' . CookieMessenger::INTERNAL_SEPARATOR . 'true' .
            CookieMessenger::INTERNAL_SEPARATOR . 'Valid Message' .
            CookieMessenger::INTERNAL_SEPARATOR . \http_build_query(['form' => 'data']);
        $signed_content = \env('cleandeck.app_key') . $content;
        $_COOKIE[MESSAGING_COOKIE_NAME] = \base64_encode($content . '|' . \md5($signed_content));
        $parsed_cookie_message = CookieMessengerReader::parse();
        $this->assertTrue(is_array($parsed_cookie_message));
        unset($_COOKIE[MESSAGING_COOKIE_NAME]);

        $content = '200' . CookieMessenger::INTERNAL_SEPARATOR . 'false' .
            CookieMessenger::INTERNAL_SEPARATOR . 'Valid Message' .
            CookieMessenger::INTERNAL_SEPARATOR . \http_build_query(['form' => 'data']);
        $signed_content = \env('cleandeck.app_key') . $content;
        $_COOKIE[MESSAGING_COOKIE_NAME] = \base64_encode($content . '|sep|' . \md5($signed_content));
        $parsed_cookie_message = CookieMessengerReader::parse();
        $this->assertNull($parsed_cookie_message);
        unset($_COOKIE[MESSAGING_COOKIE_NAME]);

        $content = '200' . CookieMessenger::INTERNAL_SEPARATOR . 'false' .
            CookieMessenger::INTERNAL_SEPARATOR . 'Valid Message' .
            CookieMessenger::INTERNAL_SEPARATOR . \http_build_query(['form' => 'data']);
        $signed_content = \env('cleandeck.app_key') . $content;
        $_COOKIE[MESSAGING_COOKIE_NAME] = \base64_encode($content . '|' . $signed_content);
        $parsed_cookie_message = CookieMessengerReader::parse();
        $this->assertNull($parsed_cookie_message);
        unset($_COOKIE[MESSAGING_COOKIE_NAME]);

        $content = '400' . CookieMessenger::INTERNAL_SEPARATOR . 'true' .
            CookieMessenger::INTERNAL_SEPARATOR . 'Valid Message' .
            CookieMessenger::INTERNAL_SEPARATOR . \http_build_query(['form' => 'data']);
        $signed_content = \env('cleandeck.app_key') . $content;
        $_COOKIE[MESSAGING_COOKIE_NAME] = \base64_encode($content . '|' . \md5($signed_content));

        $_ENV['cleandeck']['app_key'] = null;
        $parsed_cookie_message = CookieMessengerReader::parse();
        $this->assertNull($parsed_cookie_message);
        unset($_COOKIE[MESSAGING_COOKIE_NAME]);
    }

    public function testGetPreviousFormData(): void
    {
        $result = CookieMessengerReader::getPreviousFormData(['form' => 'data'], 'form');
        $this->assertEquals('data', $result);

        $result = CookieMessengerReader::getPreviousFormData(['form' => 'data'], 'ex-key');
        $this->assertEquals('', $result);

        $result = CookieMessengerReader::getPreviousFormData(['form' => 'data'], 'ex-key', 'default');
        $this->assertEquals('default', $result);
    }
}
