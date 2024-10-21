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

final class TextUtils
{
    public static function prettify_camelcase_vars(string $cc_var): string
    {
        $cc_array = \explode('_', $cc_var);
        array_walk($cc_array, static function (&$value): void {
            $value = \ucwords($value);
        });
        return \implode(' ', $cc_array);
    }
}
