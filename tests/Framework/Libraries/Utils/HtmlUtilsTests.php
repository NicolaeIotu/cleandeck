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

/*
 * Code generated partly by qwen2.5-coder AI.
 */

namespace Framework\Libraries\Utils;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HtmlUtils::class)]
final class HtmlUtilsTests extends TestCase
{
    public function testStripTagsTurboWithArray(): void
    {
        $input = [
            'key1' => '<script>alert("XSS")</script>',
            'key2' => [
                'subKey1' => '<p>Paragraph with <b>Bold</b></p>',
                'subKey2' => '<div>Div content</div>',
            ],
            'key3' => (object) [
                'prop1' => '<a href="#">Link</a>',
                'prop2' => '<img src="image.jpg" alt="Image">',
            ],
        ];

        $expected = [
            'key1' => 'alert("XSS")',
            'key2' => [
                'subKey1' => 'Paragraph with Bold',
                'subKey2' => 'Div content',
            ],
            'key3' => (object) [
                'prop1' => 'Link',
                'prop2' => '',
            ],
        ];

        $output = HtmlUtils::strip_tags_turbo($input);

        $this->assertEquals($expected, $output);
    }

    public function testStripTagsTurboWithObject(): void
    {
        $input = (object) [
            'prop1' => '<script>alert("XSS")</script>',
            'prop2' => [
                'subKey1' => '<p>Paragraph with <b>Bold</b></p>',
                'subKey2' => '<div>Div content</div>',
            ],
            'prop3' => (object) [
                'nestedProp1' => '<a href="#">Link</a>',
                'nestedProp2' => '<img src="image.jpg" alt="Image">',
            ],
        ];

        $expected = (object) [
            'prop1' => 'alert("XSS")',
            'prop2' => [
                'subKey1' => 'Paragraph with Bold',
                'subKey2' => 'Div content',
            ],
            'prop3' => (object) [
                'nestedProp1' => 'Link',
                'nestedProp2' => '',
            ],
        ];

        $output = HtmlUtils::strip_tags_turbo($input);

        $this->assertEquals($expected, $output);
    }

    public function testStripTagsTurboWithString(): void
    {
        $input = '<script>alert("XSS")</script>';
        $expected = 'alert("XSS")';

        $output = HtmlUtils::strip_tags_turbo($input);

        $this->assertEquals($expected, $output);
    }

    public function testStripTagsTurboWithNull(): void
    {
        $input = null;
        $expected = null;

        $output = HtmlUtils::strip_tags_turbo($input);

        $this->assertEquals($expected, $output);
    }

    public function testStripTagsTurboWithInt(): void
    {
        $input = 123;
        $expected = 123;

        $output = HtmlUtils::strip_tags_turbo($input);

        $this->assertEquals($expected, $output);
    }

    public function testStripTagsTurboWithFloat(): void
    {
        $input = 123.45;
        $expected = 123.45;

        $output = HtmlUtils::strip_tags_turbo($input);

        $this->assertEquals($expected, $output);
    }

    public function testStripTagsTurboWithBoolean(): void
    {
        $input = true;
        $expected = true;

        $output = HtmlUtils::strip_tags_turbo($input);

        $this->assertEquals($expected, $output);
    }

}
