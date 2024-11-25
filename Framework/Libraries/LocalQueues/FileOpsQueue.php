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

use DateTimeImmutable;
use Framework\Libraries\Utils\WarningHandler;
use SQLite3;

/**
 * A queue system recommended for operations involving cloud storage.
 */
class FileOpsQueue extends SQLite3
{
    /**
     * Some rules are only valid for a local queue, and some rules are only valid for shared (remote) queues.
     * For example if a local upload is missing, we can remove the operation only if the queue is local.
     * At the moment the queue is only local.
     */
    public const LOCAL_QUEUE = true;

    /**
     * Instead of using extra calls in order to determine remote ContentType (i.e. AWS.S3.headObject),
     * use database field 'remote_content_type' which is used to indicate only if the remote key is a 'file' or a 'directory'.
     * Field 'remote_content_type' is usually used in combination 'op_type = delete' & 'remote_content_type = directory'
     * in which case the operation produces the deletion of 'destination' key and other keys prefixed with
     * 'destination' key (the equivalent of deleting a directory and its content).
     * At the moment class AWSS3Delete does not perform recursive deletions.
     * Class ProcessPendingFileOps recognizes only values 'file' and 'directory' for field 'remote_content_type'.
     */
    private const PHRASES_CREATE_TABLE =
        'CREATE TABLE IF NOT EXISTS file_ops_queue ' .
        '(' .
        'timestamp               INTEGER    NOT NULL, ' .
        'op_type                 TEXT       NOT NULL, ' .
        'destination             TEXT       NOT NULL, ' .
        'source                  TEXT       default NULL, ' .
        "remote_content_type     TEXT       NOT NULL default 'file', " .
        'delete_directory        INTEGER    default NULL' .
        ')';

    private const PHRASES_INSERT =
        'INSERT INTO file_ops_queue ' .
        '(timestamp, op_type, destination, source, remote_content_type, delete_directory) ' .
        'VALUES (?, ?, ?, ?, ?, ?)';

    private const PHRASES_DELETE =
        'DELETE FROM file_ops_queue WHERE ' .
        'op_type = ? AND destination = ? AND source = ?';

    private const PHRASES_DELETE_ROWID =
        'DELETE FROM file_ops_queue WHERE rowid = ?';


    private string $DB_PATH;

    public function getDBPATH(): string
    {
        return $this->DB_PATH;
    }

    private readonly string $DB_DIR;

    public function getDBDIR(): string
    {
        return $this->DB_DIR;
    }

    /**
     * @throws \Exception
     */
    public function __construct(string $db_path = null)
    {
        if (isset($db_path)) {
            $this->DB_PATH = $db_path;
        } else {
            $this->DB_PATH = CLEANDECK_WRITE_PATH . '/database/file-ops-queue.sqlite';
        }

        $this->DB_DIR = \dirname($this->DB_PATH);
        $this->createDbDir();

        parent::__construct($this->DB_PATH, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
        $this->initDb();
    }

    public function __destruct()
    {
        $this->close();
    }


    /**
     * @throws \Exception
     */
    private function createDbDir(): void
    {
        if (\file_exists($this->DB_DIR)) {
            if (\is_dir($this->DB_DIR)) {
                return;
            }
            throw new \Exception('Path is not a directory ' . $this->DB_DIR);
        }

        $create_dbdir_base_err_msg = 'Cannot create directory ' . $this->DB_DIR . ' on this server';
        if (!WarningHandler::run(
            fn (): bool => \mkdir($this->DB_DIR, 0o775, true),
            $create_dbdir_base_err_msg)) {
            throw new \Exception($create_dbdir_base_err_msg);
        }
    }

    /**
     * @throws \Exception
     */
    private function initDb(): void
    {
        $this->busyTimeout(5000);
        $this->exec('PRAGMA journal_mode = WAL');

        if (!$this->exec(self::PHRASES_CREATE_TABLE)) {
            $this->throwException('Cannot create file_ops_queue table');
        }
    }

    /**
     * @throws \Exception
     */
    protected function throwException(string $error_message): never
    {
        throw new \Exception($error_message . ' [error ' . $this->lastErrorCode() . ']:'
            . $this->lastErrorMsg());
    }

    /**
     * Adds an operation to the queue only if the last similar operation doesn't have the same 'op_type'.
     * @throws \Exception
     */
    public function queueAdd(
        string $op_type,
        string $destination,
        string $source = null,
        string $remote_content_type = 'file',
        bool   $delete_directory = false
    ): int {
        // no duplicates!?
        $this->queueRemove($op_type,
            $destination,
            $source);


        $statement = $this->prepare(self::PHRASES_INSERT);
        $dateTimeImmutable = new DateTimeImmutable();
        $timestamp = $dateTimeImmutable->getTimestamp();

        $statement->bindValue(1, $timestamp, SQLITE3_INTEGER);
        $statement->bindValue(2, $op_type, SQLITE3_TEXT);
        $statement->bindValue(3, $destination, SQLITE3_TEXT);
        $statement->bindValue(4, $source, isset($source) ? SQLITE3_TEXT : SQLITE3_NULL);
        $statement->bindValue(5, $remote_content_type, SQLITE3_TEXT);
        $statement->bindValue(
            6,
            $delete_directory ? 1 : null,
            $delete_directory ? SQLITE3_INTEGER : SQLITE3_NULL
        );

        $statement_result = $statement->execute();
        if ($statement_result === false) {
            $this->throwException('Cannot add entry to queue');
        }

        return $timestamp;
    }

    /**
     * @throws \Exception
     */
    public function queueRemove(string $op_type, string $destination, string $source = null): void
    {
        $adjusted_phrase = self::PHRASES_DELETE;
        if (!isset($source)) {
            $adjusted_phrase = \str_replace('source = ?', 'source IS NULL', $adjusted_phrase);
        }

        $statement = $this->prepare($adjusted_phrase);

        $statement->bindValue(1, $op_type, SQLITE3_TEXT);
        $statement->bindValue(2, $destination, SQLITE3_TEXT);
        if (isset($source)) {
            $statement->bindValue(3, $source, SQLITE3_TEXT);
        }

        $statement_result = $statement->execute();
        if ($statement_result === false) {
            $this->throwException('Cannot delete from queue');
        }
    }

    /**
     * @throws \Exception
     */
    public function queueRemoveLastInsert(): void
    {
        $lastInsertRowID = $this->lastInsertRowID();
        $statement = $this->prepare(self::PHRASES_DELETE_ROWID);

        $statement->bindValue(1, $lastInsertRowID, SQLITE3_INTEGER);

        $statement_result = $statement->execute();
        if ($statement_result === false) {
            $this->throwException('Cannot delete last insert from queue');
        }
    }
}
