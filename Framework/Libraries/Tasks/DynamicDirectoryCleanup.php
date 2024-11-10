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

namespace Framework\Libraries\Tasks;

use Framework\Libraries\Utils\WarningHandler;

require_once __DIR__ . '/../../Config/constants.php';
require_once __DIR__ . '/../Tasks/UniqueCommand.php';


final class DynamicDirectoryCleanup
{
    private readonly string|false $target_directory;

    private readonly int $max_allowed_files;

    private readonly int $max_age_days;

    private readonly bool $recursive;

    private readonly bool $remove_linked_content;


    /**
     * @return false|array<string, mixed>
     * @throws \Exception
     */
    public function runCleanup(string $target_directory): bool|array
    {
        $result = [
            'count' => [
                'dirs' => 0,
                'files' => 0,
            ],
            'deleted' => [
                'dirs' => 0,
                'files' => 0,
            ],
            'failures' => [
                'dirs' => 0,
                'files' => 0,
            ],
        ];

        $chdir_err_msg = 'Cannot access directory ' . $target_directory;
        if (!WarningHandler::run(
            static fn (): bool => \chdir($target_directory),
            $chdir_err_msg)) {
            throw new \Exception($chdir_err_msg);
        }

        \clearstatcache();
        $dir_handle = \opendir($target_directory);
        if ($dir_handle === false) {
            return false;
        }

        $minimum_accepted_timestamp = \time() - ($this->max_age_days * 24 * 60 * 60);
        $count = 0;

        $dir_entry = \readdir($dir_handle);
        while ($dir_entry !== false) {
            if ($dir_entry !== '.' && $dir_entry !== '..') {
                if (\is_dir($dir_entry)) {
                    // directories
                    ++$result['count']['dirs'];

                    if ($this->recursive) {
                        $recursive_cleanup_result = $this->runCleanup(\realpath('./' . $dir_entry));
                        $chdir_err_msg = '(2) Cannot access directory ' . $target_directory;
                        if (!WarningHandler::run(
                            static fn (): bool => \chdir($target_directory),
                            $chdir_err_msg)) {
                            throw new \Exception($chdir_err_msg);
                        }

                        if ($recursive_cleanup_result !== false) {
                            $result['count']['dirs'] += $recursive_cleanup_result['count']['dirs'];
                            $result['count']['files'] += $recursive_cleanup_result['count']['files'];
                            $result['deleted']['dirs'] += $recursive_cleanup_result['deleted']['dirs'];
                            $result['deleted']['files'] += $recursive_cleanup_result['deleted']['files'];
                            $result['failures']['dirs'] += $recursive_cleanup_result['failures']['dirs'];
                            $result['failures']['files'] += $recursive_cleanup_result['failures']['files'];
                        }

                        // remove empty recursive dirs
                        $scan_dir_result = WarningHandler::run(static fn (): array|false => \scandir($dir_entry), null, false);
                        if ($scan_dir_result == false) {
                            ++$result['failures']['dirs'];
                        } else {
                            if (\array_diff($scan_dir_result, ['..', '.']) === []) {
                                $rmdir_result = WarningHandler::run(static fn (): bool => \rmdir($dir_entry), null, false);
                                if ($rmdir_result === false) {
                                    ++$result['failures']['dirs'];
                                } else {
                                    ++$result['deleted']['dirs'];
                                }
                            }
                        }
                    }
                } else {
                    // files
                    if (\stripos($dir_entry, 'index') === false) {
                        ++$result['count']['files'];

                        $file_path = $target_directory . '/' . $dir_entry;

                        $do_delete_file = false;
                        if ($count >= $this->max_allowed_files) {
                            $do_delete_file = true;
                        } else {
                            $modification_ts = \filemtime($file_path);

                            if ($modification_ts === false) {
                                ++$result['failures']['files'];
                            } else {
                                if ($modification_ts < $minimum_accepted_timestamp) {
                                    $do_delete_file = true;
                                }
                            }
                        }

                        if ($do_delete_file) {
                            // delete linked file
                            if ($this->remove_linked_content && \is_link($file_path)) {
                                $link_path = WarningHandler::run(static fn (): string|false => \readlink($file_path), null, false);
                                if ($link_path !== false) {
                                    if (WarningHandler::run(static fn (): bool => \unlink($link_path), null, false)) {
                                        ++$result['deleted']['files'];
                                    } else {
                                        ++$result['failures']['files'];
                                    }
                                }

                            }

                            // delete file
                            if (WarningHandler::run(static fn (): bool => \unlink($file_path), null, false)) {
                                ++$result['deleted']['files'];
                            } else {
                                ++$result['failures']['files'];
                            }
                        }

                        ++$count;
                    }
                }
            }

            $dir_entry = \readdir($dir_handle);
        }

        return $result;
    }


    /**
     * Clean up a directory with content which changes dynamically.
     * By default, files having names which start with 'index' will not be deleted.
     * @param string $target_directory An absolute path.
     * @param int $max_allowed_files The maximum allowed number of files.
     * @param int $max_age_days The maximum allowed age of files. Minimum 1 day.
     * @param bool $recursive Recurse in directories, delete empty directories,
     *      delete files in directories as per parameters $MAX_COUNT and $MAX_AGE_DAYS.
     * @param bool $remove_linked_content If true, the targets of link files are also removed if required.
     *      This setting does not affect the directories containing the targets of link files.
     * @throws \Exception
     */
    public function __construct(
        string $target_directory,
        int    $max_allowed_files,
        int    $max_age_days,
        bool   $recursive = true,
        bool   $remove_linked_content = false
    ) {
        $this->target_directory = \realpath($target_directory);
        if ($this->target_directory === false) {
            throw new \InvalidArgumentException('Invalid directory ' . $this->target_directory);
        }

        if ($max_age_days < 1) {
            throw new \InvalidArgumentException('The minimum age of files must be at least 1 (day).');
        }

        $this->max_allowed_files = \max(0, $max_allowed_files);
        $this->max_age_days = $max_age_days;
        $this->recursive = $recursive;
        $this->remove_linked_content = $remove_linked_content;

        $cleanup_result = $this->runCleanup($this->target_directory);

        $hdr_msg = 'CLEANUP RESULT - ';
        if ($cleanup_result === false) {
            $cleanup_has_errors = true;
            \syslog(
                LOG_ERR,
                $hdr_msg . 'FAILURE: cleanup failed to start for directory ' . $this->target_directory
            );
        } else {
            $cleanup_has_errors = ($cleanup_result['failures']['dirs'] !== 0 ||
                $cleanup_result['failures']['files'] !== 0);
            \syslog(
                $cleanup_has_errors ? LOG_ERR : LOG_INFO,
                'Completed cleanup of directory ' . $this->target_directory . '.' . PHP_EOL .
                $hdr_msg .
                'DELETED FILES: ' . $cleanup_result['deleted']['files'] .
                ' (errors: ' . $cleanup_result['failures']['files'] . ')' .
                ', DELETED DIRECTORIES: ' . $cleanup_result['deleted']['dirs'] .
                ' (errors: ' . $cleanup_result['failures']['dirs'] . ')' .
                ', TOTAL FILES: ' . $cleanup_result['count']['files'] .
                ', TOTAL DIRECTORIES: ' . $cleanup_result['count']['dirs']
            );
        }

        exit($cleanup_has_errors ? 1 : 0);
    }
}
