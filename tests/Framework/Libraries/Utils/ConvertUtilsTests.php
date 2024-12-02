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

#[CoversClass(ConvertUtils::class)]
final class ConvertUtilsTests extends TestCase
{
    public function testGetByteSizeWithValidSizes(): void
    {
        $validSizes = [
            '10B' => 10,
            '2K' => 2 * 1024,
            '3M' => 3 * 1024 * 1024,
            '4G' => 4 * 1024 * 1024 * 1024,
            '5T' => 5 * 1024 * 1024 * 1024 * 1024,
            '6P' => 6 * 1024 * 1024 * 1024 * 1024 * 1024,
            7506 => 7 * 1024 + 338,
            10 => 10, // numeric input
        ];
        $expected = array_keys($validSizes);
        $input = array_values($validSizes);
        $counter = count($input);

        for ($i = 0; $i < $counter; $i++) {
            $this->assertEquals($input[$i], ConvertUtils::getByteSize($expected[$i]));
        }
    }

    public function testGetByteSizeWithInvalidSizes(): void
    {
        $invalidSizes = [
            'abc' => 'abc',
            '10Z' => 10, // unknown unit
            null => null,
            false => false,
            ' ' => ' ', // whitespace
        ];
        $expected = array_keys($invalidSizes);
        $input = array_values($invalidSizes);
        $counter = count($input);

        for ($i = 0; $i < $counter; $i++) {
            $this->assertEquals($input[$i], ConvertUtils::getByteSize($expected[$i]));
        }
    }
}
