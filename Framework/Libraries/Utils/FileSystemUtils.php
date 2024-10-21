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

final class FileSystemUtils
{
    /**
     * Returns true if at the end of operation the path is a valid directory.
     * @throws \Exception
     */
    public static function createdir(string $path): void
    {
        if (!\file_exists($path)) {
            $base_err_msg = 'Cannot create directory ' . $path;
            if (!WarningHandler::run(
                static fn (): bool => \mkdir($path, 0o775, true),
                $base_err_msg)) {
                throw new \Exception('Cannot create directory ' . $path);
            }
        } else {
            if (!\is_dir($path)) {
                throw new \Exception('Path is a file instead of directory ' . $path);
            }
        }
    }

    /**
     * @param array<string>|null $skip A list of files and directories which should not be copied.
     * @throws \Exception
     */
    public static function copydir(string $from, string $to,
                                   array  $skip = null, int &$success = 0, int &$fail = 0): float|bool
    {
        if (!\is_dir($from)) {
            throw new \Exception('Invalid source ' . $from);
        }

        if (\file_exists($to)) {
            if (!\is_dir($to)) {
                throw new \Exception('Destination ' . $to . ' exists and is not a directory.');
            }
        } elseif (!\mkdir($to, 0o755)) {
            throw new \Exception('Cannot create destination directory ' . $to);
        }

        $from_realpath = \realpath($from);
        if ($from_realpath === false) {
            throw new \Exception('Invalid source ' . $from);
        }

        $to_realpath = \realpath($to);
        if ($to_realpath === false) {
            throw new \Exception('Invalid destination ' . $to);
        }

        $has_skip = isset($skip) && [] !== $skip;
        if ($has_skip) {
            $skip_realpaths = \array_map(static function ($elem) {
                return \realpath($elem);
            }, $skip);
            if (\in_array(false, $skip_realpaths, true)) {
                throw new \Exception('Invalid entries to be skipped: ' . \implode(', ', $skip));
            }
        }


        $scandir_result = \scandir($from_realpath);
        $scandir_result = \array_filter($scandir_result, static function ($elem): bool {
            $last_dot_pos = \strrpos($elem, '.');
            // this means that nothing that ends in '.' cannot be considered further
            // (safer as it includes '.', '..' and variations)
            return $last_dot_pos === false || $last_dot_pos < \strlen($elem) - 1;
        });

        foreach ($scandir_result as $scandir_entry) {
            $scandir_entry_path = $from_realpath . '/' . $scandir_entry;
            // skip some
            if ($has_skip && \in_array($scandir_entry_path, $skip_realpaths)) {
                continue;
            }

            if (\is_file($scandir_entry_path) || \is_link($scandir_entry_path)) {
                $copy_result = WarningHandler::run(
                    static fn (): bool => \copy($scandir_entry_path, $to_realpath . '/' . \basename($scandir_entry_path)),
                    null, false);
                if ($copy_result) {
                    ++$success;
                } else {
                    echo 'Failed to copy file ' . $scandir_entry_path .
                        ' (' . self::describe_file_on_error($scandir_entry_path) . ')' . PHP_EOL;
                    ++$fail;
                }
            } elseif (\is_dir($scandir_entry_path)) {
                self::copydir(
                    $scandir_entry_path,
                    $to_realpath . '/' . $scandir_entry,
                    $skip_realpaths ?? $skip,
                    $success, $fail
                );
            }
        }

        if ($success === 0 && $fail === 0) {
            // no operations
            return false;
        }
        return $success / ($success + $fail);
    }

    private static int|false $mygid;

    private static int|false $myuid;

    public static function describe_file_on_error(string $file_path): string
    {
        $result = '';
        $file_stat = \stat($file_path);
        if ($file_stat !== false) {
            $result .= 'file user id ' . $file_stat['uid'] . ', file group id ' . $file_stat['gid'];
        }

        $mygid = self::$mygid ?? (self::$mygid = \getmygid());
        $myuid = self::$myuid ?? (self::$myuid = \getmyuid());

        if ($mygid !== false) {
            $result .= ($result === '' ? '' : ', ') . 'PHP user id ' . $mygid;
        }

        if ($myuid !== false) {
            $result .= ($result === '' ? '' : ', ') . 'PHP group id ' . $myuid;
        }

        return $result === '' ? 'no file details available' : $result;
    }

    /**
     * @param string[]|null $keep_files Accepts glob expressions.
     * @throws \Exception
     */
    public static function emptydir(string $dir_path, bool $recursive = true, array $keep_files = null): bool
    {
        $real_dir_path = \realpath($dir_path);
        if ($real_dir_path === false) {
            throw new \Exception('Invalid directory to empty: ' . $dir_path);
        }

        $paths_array = [];

        $std_paths_array = \glob($real_dir_path . '/' . '*');
        if (\is_array($std_paths_array)) {
            $paths_array = [...$paths_array, ...$std_paths_array];
        }

        $dot_paths_array = \glob($real_dir_path . '/' . '.*');
        if (\is_array($dot_paths_array)) {
            $paths_array = [...$paths_array, ...$dot_paths_array];
        }

        if (isset($keep_files)) {
            $real_keep_files_array = [];
            foreach ($keep_files as $keep_file) {
                $kf_entry = \glob($real_dir_path . '/' . $keep_file);
                if (\is_array($kf_entry)) {
                    $real_keep_files_array = [...$real_keep_files_array, ...$kf_entry];
                }
            }

            $paths_array = \array_diff($paths_array, $real_keep_files_array);
        }


        $paths_array = \array_filter($paths_array, static function ($elem): bool {
            $last_dot_pos = \strrpos($elem, '.');
            // this means that nothing that ends in '.' cannot be considered further
            // (safer as it includes '.', '..' and variations)
            return $last_dot_pos === false || $last_dot_pos < \strlen($elem) - 1;
        });

        $result = true;

        foreach ($paths_array as $path_array) {
            if (\is_dir($path_array)) {
                if ($recursive) {
                    $result = $result && self::emptydir($path_array, $recursive);
                }
            } else {
                $unlink_result = \unlink($path_array);
                if (!$unlink_result) {
                    echo "Function 'emptydir' cannot remove " . $path_array . PHP_EOL;
                }

                $result = $result && $unlink_result;
            }
        }

        return $result;
    }

    /**
     * @param string[] $dir_paths
     * @param string[]|null $keep_files Accepts glob expressions.
     * @throws \Exception
     */
    public static function emptydirs(array $dir_paths, bool $recursive = true, array $keep_files = null): bool
    {
        $result = true;

        foreach ($dir_paths as $dir_path) {
            if (!\is_string($dir_path)) {
                continue;
            }

            $result = $result && self::emptydir($dir_path, $recursive, $keep_files);
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    public static function deletedir(string $dir_path): bool
    {
        if (!\is_dir($dir_path)) {
            throw new \Exception('Invalid directory to delete: ' . $dir_path);
        }

        $empty_dir_result = self::emptydir($dir_path);
        if (!$empty_dir_result) {
            throw new \Exception('Invalid directory to delete: ' . $dir_path);
        }

        $scandir_result = \scandir($dir_path);
        $scandir_result = \array_filter($scandir_result, static function ($elem): bool {
            $last_dot_pos = \strrpos($elem, '.');
            // this means that nothing that ends in '.' cannot be considered further
            // (safer as it includes '.', '..' and variations)
            return $last_dot_pos === false || $last_dot_pos < \strlen($elem) - 1;
        });

        if ($scandir_result === []) {
            \rmdir($dir_path);
            return true;
        }

        $result = true;

        foreach ($scandir_result as $scandir_entry) {
            $scandir_entry_path = $dir_path . '/' . $scandir_entry;
            if (\is_dir($scandir_entry_path)) {
                $result = $result && self::deletedir($scandir_entry_path);
            } else {
                $unlink_result = \unlink($scandir_entry_path);
                if (!$unlink_result) {
                    echo 'Cannot remove ' . $scandir_entry_path . PHP_EOL;
                }

                $result = $result && $unlink_result;
            }
        }

        \rmdir($dir_path);

        return $result;
    }

    /**
     * @param string[] $dir_paths
     * @throws \Exception
     */
    public static function deletedirs(array $dir_paths): bool
    {
        $result = true;

        foreach ($dir_paths as $dir_path) {
            if (!\is_string($dir_path)) {
                continue;
            }

            $result = $result && self::deletedir($dir_path);
        }

        return $result;
    }
}
