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

use DateTimeImmutable;

final class TimeUtils
{
    /**
     * @param string $format Required date format i.e. 'Y-m-d T', 'Y-m-d H:i:s T', 'Y-m-d H:i:s T'
     */
    public static function timestampToDateString(int $micro_timestamp, string $format = 'Y-m-d H:i:s T'): string
    {
        $dateTimeImmutable = new DateTimeImmutable();
        $normal_timestamp = (int)\floor($micro_timestamp / 1000);
        return $dateTimeImmutable->setTimestamp($normal_timestamp)->format($format);
    }

    public static function dateToTimestamp(string $date_string): int
    {
        try {
            $dateTimeImmutable = new DateTimeImmutable($date_string);
            return ($dateTimeImmutable->getTimestamp() * 1000);
        } catch (\Exception) {
            return 0;
        }
    }

    public static function timestampInRange(int $startTimestamp, int $validityMs): bool
    {
        try {
            $dateTimeImmutable = new DateTimeImmutable();
            $ts = $dateTimeImmutable->getTimestamp() * 1000;
        } catch (\Exception) {
            return false;
        }

        $limitTimestamp = $startTimestamp + $validityMs;

        return $ts > $startTimestamp && $ts <= $limitTimestamp;
    }

    public static function getYearNow(): string
    {
        try {
            $dateTimeImmutable = new DateTimeImmutable();
            return $dateTimeImmutable->format('Y');
        } catch (\Exception) {
            return 'Cannot retrieve valid current year';
        }
    }
}
