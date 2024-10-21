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

namespace Framework\Libraries\LocalQueues;

require_once __DIR__ . '/FileOpsQueue.php';
require_once __DIR__ . '/../AWS/AWSS3Upload.php';
require_once __DIR__ . '/../AWS/AWSS3Download.php';
require_once __DIR__ . '/../AWS/AWSS3Delete.php';


use Framework\Libraries\AWS\AWSS3Upload;
use Framework\Libraries\AWS\AWSS3Download;
use Framework\Libraries\AWS\AWSS3Delete;

/**
 * Run in a separate process. Must be able to run as a standalone script.
 * Handles pending AWS S3 file operations.
 */
final class ProcessPendingFileOps extends FileOpsQueue
{
    private const PHRASES_GET_ALL =
        'SELECT * FROM file_ops_queue ' .
        'ORDER BY rowid';

    private const PHRASES_DELETE_PREVIOUS_SIMILAR_OPS =
        'DELETE FROM file_ops_queue ' .
        'WHERE timestamp < ? AND ' .
        '(destination LIKE ? OR ' .
        'source LIKE ?)';


    /**
     * @param string|null $db_path
     * @param array<string, mixed>|null $single_op The details of a single operation.
     * @throws \Exception
     */
    public function __construct(string $db_path = null, array $single_op = null)
    {
        // initialization
        parent::__construct($db_path);


        if (isset($single_op, $single_op['op_type'], $single_op['destination'])) {
            // process a single operation
            $source = $single_op['source'] ?? null;
            $remote_content_type = $single_op['remote_content_type'] ?? 'file';
            $delete_directory = $single_op['delete_directory'] ?? false;

            $timestamp = $this->queueAdd(
                $single_op['op_type'],
                $single_op['destination'],
                $source,
                $remote_content_type,
                $delete_directory
            );

            $queue = [
                [
                    'timestamp' => $timestamp,
                    'op_type' => $single_op['op_type'],
                    'destination' => $single_op['destination'],
                    'source' => $source,
                    'remote_content_type' => $remote_content_type,
                    'delete_directory' => $delete_directory,
                ],
            ];
        } else {
            // process all pending
            $queue = $this->queueGetAll();
        }


        $queue_size = \count($queue);
        if ($queue_size === 0) {
            \syslog(LOG_INFO, 'No file operations in queue');
            return;
        }

        $process_queue_errors = $this->processQueue($queue);

        if ($process_queue_errors < 1) {
            \syslog(LOG_INFO, 'File operations queue processed successfully');
        } else {
            \syslog(LOG_ERR, 'Queued file operations (' . \count($queue) .
                ') completed with errors (' . $process_queue_errors . ')');
        }
    }


    /**
     * @return array<array<string, mixed>>
     * @throws \Exception
     */
    public function queueGetAll(): array
    {
        $result = $this->query(self::PHRASES_GET_ALL);

        if ($result === false) {
            $this->throwException('Cannot get all entries in queue');
        }

        $data = [];

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if (\is_array($row)) {
                $data[] = $row;
            }
        }

        $result->finalize();

        return $data;
    }


    /**
     * @param array<array<string, mixed>> $queue
     */
    private function processQueue(array $queue): int
    {
        $count_errors = 0;

        foreach ($queue as $row) {
            if (isset($row['op_type'], $row['destination']) &&
                \is_string($row['op_type']) && \is_string($row['destination'])) {
                $op_success = $this->processAWSS3operation($row);

                if ($op_success) {
                    try {
                        $this->queueRemove($row['op_type'], $row['destination'], $row['source']);
                    } catch (\Exception $e) {
                        $err_msg = $e->getMessage() . '(' . \implode(',', $row) . ')';
                        \syslog(LOG_ERR, $err_msg);
                        \error_log($err_msg);
                        ++$count_errors;
                    }
                } else {
                    ++$count_errors;
                }
            }
        }

        return $count_errors;
    }

    /**
     * Does not throw. Send error messages to syslog.
     * @param array<string, mixed> $row
     */
    private function processAWSS3operation(array $row): bool
    {
        switch ($row['op_type']) {
            case 'upload':
                return $this->upload($row);
            case 'download':
                return $this->download($row);
            case 'delete':
                return $this->delete($row);
            default:
                \syslog(LOG_ERR, 'Unknown operation type: ' . $row['op_type']);
                return false;
        }
    }


    /**
     * @param array<string, mixed> $row
     */
    private function upload(array $row): bool
    {
        $bucket_path = null;
        if (isset($row['destination'])) {
            $destination_dirname = \dirname((string) $row['destination']);
            if (\strlen($destination_dirname) > 0) {
                if (\preg_match('/^[a-zA-Z0-9_-].*/', $destination_dirname) !== false) {
                    $bucket_path = $destination_dirname;
                }
            }
        }

        $realpath_source = \realpath($row['source']);
        if ($realpath_source === false) {
            $error_message = 'Cannot find file to upload: ' . $row['source'];
            \syslog(LOG_ERR, $error_message);
            \error_log($error_message);
            return false;
        }

        try {
            return AWSS3Upload::init(
                $row['source'],
                $bucket_path
            );
        } catch (\Exception $exception) {
            \error_log($exception->getMessage());
            \syslog(LOG_ERR, $exception->getMessage());
            return false;
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function download(array $row): bool
    {
        // This operation returns a boolean value on success or on failure if the status code cannot be retrieved,
        // or a status code when available and only in case of failures.
        try {
            $download_result = AWSS3Download::init(
                $row['source'],
                $row['destination']
            );
        } catch (\Exception $exception) {
            \error_log($exception->getMessage());
            \syslog(LOG_ERR, $exception->getMessage());
            return false;
        }

        // check for code 404
        if (\is_int($download_result)) {
            return $download_result !== 404;
        }
        return $download_result;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function delete(array $row): bool
    {
        try {
            $this->deletePreviousSimilarOps($row);
        } catch (\Exception $exception) {
            \syslog(LOG_INFO, $exception->getMessage());
        }

        try {
            $delete_result = AWSS3Delete::init(
                $row['destination'],
                $row['remote_content_type'] === 'directory',
                $row['delete_directory'] > 0
            );
        } catch (\Exception $exception) {
            \error_log($exception->getMessage());
            \syslog(LOG_ERR, $exception->getMessage());
            return false;
        }


        return $delete_result;
    }

    /**
     * @param array<string, mixed> $row
     * @throws \Exception
     */
    private function deletePreviousSimilarOps(array $row): void
    {
        if ($row['remote_content_type'] === 'directory') {
            $search_suffix = $row['destination'] . '/%';
        } else {
            $search_suffix = '%' . \basename(\dirname((string) $row['destination'])) . '/' .
                \basename((string) $row['destination']);
        }

        $statement = $this->prepare(self::PHRASES_DELETE_PREVIOUS_SIMILAR_OPS);

        $statement->bindValue(1, $row['timestamp'], SQLITE3_INTEGER);
        $statement->bindValue(2, $search_suffix, SQLITE3_TEXT);
        $statement->bindValue(3, $search_suffix, SQLITE3_TEXT);

        $statement_result = $statement->execute();
        if ($statement_result === false) {
            $this->throwException('Cannot delete previous similar ops in queue');
        }
    }
}
