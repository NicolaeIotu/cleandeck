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

final class ConvertUtils
{
    /**
     * @param string|mixed $source_size
     */
    public static function getByteSize(mixed $source_size): mixed
    {
        if (\is_string($source_size)) {
            $source_size_array = \preg_split('/([BKMGTP])/', $source_size, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

            if (\is_array($source_size_array)) {
                $source_size_array_length = \count($source_size_array);

                if ($source_size_array_length > 0) {
                    $source_number = (int)$source_size_array[0];
                    if ($source_number > 0) {
                        if ($source_size_array_length === 1) {
                            return $source_number;
                        }
                        switch ($source_size_array[1]) {
                            case 'B':
                                $exponent = 0;
                                break;
                            case 'K':
                                $exponent = 1;
                                break;
                            case 'M':
                                $exponent = 2;
                                break;
                            case 'G':
                                $exponent = 3;
                                break;
                            case 'T':
                                $exponent = 4;
                                break;
                            case 'P':
                                $exponent = 5;
                                break;
                            default:
                                return $source_size;
                        }
                        return $source_number * \pow(1024, $exponent);
                    }
                }
            }
        }

        return $source_size;
    }
}
