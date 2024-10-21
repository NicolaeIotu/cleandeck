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

namespace Framework\Libraries\Http;

use Framework\Libraries\Utils\WarningHandler;

final class HttpUpload
{
    /**
     * @param string $field_name
     * @param string|null $key
     * @return array<mixed>|bool
     */
    public static function uploadDetails(string $field_name, string $key = null): array|bool
    {
        if (!self::fieldHasEntries($field_name)) {
            return false;
        }

        if (self::hasError($field_name)) {
            return false;
        }

        if (isset($key)) {
            if (!isset($_FILES[$field_name][$key])) {
                return false;
            }

            if (\is_array($_FILES[$field_name][$key])) {
                return $_FILES[$field_name][$key];
            }
            return [$_FILES[$field_name][$key]];
        }

        return $_FILES[$field_name];
    }

    /**
     * Handles both single and multi file uploads.<br>
     * By default, the original file name is used, but one of the following options can be used to rename
     *  uploaded file(s):
     *  - a new file name for single file uploads only ($rename_full_name) ,
     *  - a prefix to append to the original file name(s) ($rename_prefix)
     * @param string|null $rename_full_name For single file uploads only.
     * @param string|null $rename_prefix A custom prefix to add to each file name.
     * @return array<string, bool> Array where the keys are the paths to final store destination and the values are the results of the
     *   operation: true for success and false for failure.
     * @throws \Exception
     */
    public static function store(string $field_name, string $destination_directory,
                                 string $rename_full_name = null, string $rename_prefix = null): array
    {
        if (!\file_exists($destination_directory)) {
            // default permissions 0775
            $base_err_msg = 'Could not create ' . $destination_directory . ' directory';
            if (!WarningHandler::run(
                static fn (): bool => \mkdir($destination_directory, 0o775, true),
                $base_err_msg, false, 500)) {
                throw new \Exception($base_err_msg, 500);
            }
        } else {
            if (!\is_dir($destination_directory)) {
                throw new \Exception('Path is not a directory ' . $destination_directory, 500);
            }
        }

        $move_result = [];

        if (\is_array($_FILES[$field_name]['error'])) {
            // multi file upload
            foreach ($_FILES[$field_name]['error'] as $key => $error) {
                if ($error == UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES[$field_name]['tmp_name'][$key];
                    $destination = $destination_directory . '/' . ($rename_prefix ?? '') . \basename((string)$_FILES[$field_name]['name'][$key]);
                    $move_result[$destination] = \move_uploaded_file($tmp_name, $destination);
                }
            }
        } elseif ($_FILES[$field_name]['error'] === UPLOAD_ERR_OK) {
            // single file upload
            if (isset($rename_full_name)) {
                $file_name = \basename($rename_full_name);
            } else {
                $file_name = ($rename_prefix ?? '') . \basename((string)$_FILES[$field_name]['name']);
            }

            $destination = $destination_directory . '/' . $file_name;
            $move_result[$destination] = \move_uploaded_file($_FILES[$field_name]['tmp_name'], $destination);
        } else {
            throw new \Exception("Invalid upload operation for field '" . $field_name .
                "', destination directory '" . $destination_directory, 403);
        }

        return $move_result;
    }

    /**
     * @param array<string, bool> $store_result
     * @return bool
     */
    public static function success(array $store_result): bool
    {
        foreach ($store_result as $v) {
            if (!$v) {
                return false;
            }
        }

        return true;
    }

    public static function fieldHasEntries(string $field_name): bool
    {
        if (!isset($_FILES[$field_name], $_FILES[$field_name]['name'])) {
            return false;
        }
        if (\is_array($_FILES[$field_name]['name'])) {
            return \array_key_exists(0, $_FILES[$field_name]['name']) && $_FILES[$field_name]['name'][0] !== '';
        }
        return $_FILES[$field_name]['name'] !== '';
    }


    /**
     * @param string[]|null $allowed_mime_types
     */
    public static function hasError(string $field_name, array $allowed_mime_types = null): bool
    {
        if (!isset($_FILES[$field_name],
            $_FILES[$field_name]['error'], $_FILES[$field_name]['name'], $_FILES[$field_name]['tmp_name'])) {
            return true;
        }

        $name_array = \is_array($_FILES[$field_name]['name']) ?
            $_FILES[$field_name]['name'] : [$_FILES[$field_name]['name']];
        foreach ($name_array as $name) {
            if (!\is_string($name) ||
                \preg_match('/^[a-zA-Z0-9_. -]{4,}$/', $name) !== 1) {
                return true;
            }
        }

        if (isset($allowed_mime_types)) {
            $tmp_name_array = \is_array($_FILES[$field_name]['tmp_name']) ?
                $_FILES[$field_name]['tmp_name'] : [$_FILES[$field_name]['tmp_name']];
            foreach ($tmp_name_array as $tmp_name) {
                $file_info = \finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = \finfo_file($file_info, $tmp_name);
                \finfo_close($file_info);

                if (!\in_array($mime_type, $allowed_mime_types)) {
                    return true;
                }
            }
        }

        $error_array = \is_array($_FILES[$field_name]['error']) ?
            $_FILES[$field_name]['error'] : [$_FILES[$field_name]['error']];
        foreach ($error_array as $err) {
            if ($err !== UPLOAD_ERR_OK) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[]|null $allowed_mime_types
     */
    public static function getError(string $field_name, array $allowed_mime_types = null): string
    {
        if (!isset($_FILES[$field_name])) {
            return 'No such field ' . $field_name;
        }

        if (!isset($_FILES[$field_name]['error'])) {
            return 'Invalid format of error for field ' . $field_name;
        }

        $name_array = \is_array($_FILES[$field_name]['name']) ?
            $_FILES[$field_name]['name'] : [$_FILES[$field_name]['name']];
        foreach ($name_array as $name) {
            if (!\is_string($name) ||
                \preg_match('/^[a-zA-Z0-9_. -]{4,}$/', $name) !== 1) {
                return 'Invalid file name. Expecting letters, numbers, underscores, dots, spaces and dashes.';
            }
        }

        if (isset($allowed_mime_types, $_FILES[$field_name]['tmp_name'])) {
            $tmp_name_array = \is_array($_FILES[$field_name]['tmp_name']) ?
                $_FILES[$field_name]['tmp_name'] : [$_FILES[$field_name]['tmp_name']];
            foreach ($tmp_name_array as $tmp_name) {
                $file_info = \finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = \finfo_file($file_info, $tmp_name);
                \finfo_close($file_info);

                if (!\in_array($mime_type, $allowed_mime_types)) {
                    return 'Forbidden mime type: ' . $mime_type;
                }
            }
        }

        $error_array = \is_array($_FILES[$field_name]['error']) ?
            $_FILES[$field_name]['error'] : [$_FILES[$field_name]['error']];
        foreach ($error_array as $err) {
            if ($err !== UPLOAD_ERR_OK) {
                return match ($_FILES[$field_name]['error']) {
                    UPLOAD_ERR_INI_SIZE => 'php.ini - upload_max_filesize directive exceeded',
                    UPLOAD_ERR_FORM_SIZE => 'HTML form - MAX_FILE_SIZE directive exceeded',
                    UPLOAD_ERR_PARTIAL => 'Partial upload',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary directory',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
                    default => 'Unknown upload error',
                };
            }
        }

        return 'No upload error';
    }
}
