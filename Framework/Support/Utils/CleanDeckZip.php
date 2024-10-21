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

require_once __DIR__ . '/../../Config/Constants.php';

use DirectoryIterator;
use ZipArchive;

final class CleanDeckZip
{
    private static ?string $last_zip_archive_path;

    public static function getLastZipArchivePath(): ?string
    {
        return self::$last_zip_archive_path ?? null;
    }

    /**
     * @var string[]
     */
    private static array $zip_paths = [
        'Application',
        'bin',
        'deploy',
        'Framework',
        '.env.ini',
        'composer.json',
        'LICENSE',
    ];

    private static function getZipArchiveName(?string $tag): string
    {
        $base_name = 'cleandeck-';

        if (isset($tag)) {
            $tag = preg_replace('/[^a-zA-Z0-9-]/', '', $tag);
            $tag = rtrim((string)$tag, '-');
            $base_name .= $tag !== '' ? $tag . '-' : '';
        }

        $dateTimeImmutable = new \DateTimeImmutable();

        return $base_name . $dateTimeImmutable->format('Ymd_His_T') . '.zip';
    }

    /**
     * @param string|null $tag
     * @throws \Exception
     */
    public static function run(?string $tag): void
    {
        $archive_name = self::getZipArchiveName($tag);
        $archive_path = CLEANDECK_ROOT_PATH . '/' . $archive_name;

        $zipArchive = new ZipArchive();
        if ($zipArchive->open($archive_path, ZipArchive::CREATE) !== true) {
            throw new \Exception('Cannot open zip archive: ' . $zipArchive->getStatusString());
        }

        $cwd = getcwd();

        foreach (self::$zip_paths as $zip_path) {
            if (is_file($zip_path) || is_link($zip_path)) {
                $zipArchive->addFile($zip_path, str_replace($cwd . '/', '', $zip_path));
            } elseif (is_dir($zip_path)) {
                self::zipAddDirectory($cwd, $zipArchive, $zip_path);
            }
        }


        $zip_result = $zipArchive->close();
        if ($zip_result === false) {
            throw new \Exception('Cannot create zip archive: ' . $zipArchive->getStatusString());
        }

        self::$last_zip_archive_path = realpath($archive_path);
    }

    private static function zipAddDirectory(string $cwd, ZipArchive $zipArchive, string $dir_path): void
    {
        $iterator = new DirectoryIterator($dir_path);
        while ($iterator->valid()) {
            $file = $iterator->current();
            if (!$file->isDot()) {
                $pathname = $file->getPathname();
                $add_entryname = str_replace($cwd . '/', '', $pathname);
                if ($file->isFile()) {
                    $zipArchive->addFile($pathname, $add_entryname);
                    $zipArchive->setExternalAttributesName($add_entryname,
                    ZipArchive::OPSYS_UNIX, fileperms($pathname) << 16);
                } elseif ($file->isDir()) {
                    self::zipAddDirectory($cwd, $zipArchive, $pathname);
                }
            }

            $iterator->next();
        }
    }
}
