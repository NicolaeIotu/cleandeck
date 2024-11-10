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

namespace Framework\Support\Utils;

require_once __DIR__ . '/../../Config/constants.php';

use ZipArchive;

final class CleanDeckUnzip
{
    /**
     * @param string $archive_path
     * @param string $destination_path
     * @throws \Exception
     */
    public static function run(string $archive_path, string $destination_path): void
    {
        if (!\is_file($archive_path)) {
            throw new \Exception('Expecting a zip archive file instead of ' . $archive_path);
        }

        $archive_realpath = \realpath($archive_path);
        if ($archive_realpath === false) {
            throw new \Exception('No such archive ' . $archive_path);
        }

        $zipArchive = new ZipArchive();
        if ($zipArchive->open($archive_realpath) !== true) {
            throw new \Exception('Unzip cannot open ' . $archive_path . ': ' . $zipArchive->getStatusString());
        }

        // extract
        for ($idx = 0; $s = $zipArchive->statIndex($idx); ++$idx) {
            // Extracting ONLY the directory Application/ and file .env.ini because the framework should be installed already.
            if ($s['name'] === '.env.ini' ||
                \str_starts_with($s['name'], 'Application/')) {
                // directly unzip to destination while adjusting attributes (extract from Php Manuals)
                if ($zipArchive->extractTo($destination_path, $s['name'])) {
                    if ($zipArchive->getExternalAttributesIndex($idx, $opsys, $attr)
                        && $opsys == ZipArchive::OPSYS_UNIX) {
                        \chmod($destination_path . '/' . $s['name'], ($attr >> 16) & 0o755);
                    }
                }
            }
        }

        $zipArchive->close();
    }
}
