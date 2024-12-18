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

final class HtmlUtils
{
    public static function strip_tags_turbo(mixed $target): mixed
    {
        if (\is_string($target)) {
            return \strip_tags($target);
        }
        if (\is_array($target)) {
            foreach ($target as $key => $value) {
                $target[$key] = self::strip_tags_turbo($value);
            }
        } elseif (\is_object($target)) {
            foreach ($target as $key => $value) {
                $target->$key = self::strip_tags_turbo($value);
            }
        }

        return $target;
    }
}
