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
 * A queue system recommended for sending emails.
 */
class EmailsQueue extends SQLite3
{
    /**
     * Some rules are only valid for a local queue, and some rules are only valid for shared (remote) queues.
     * At the moment this queue is only local.
     * You may automatically switch to a remote queue by updating the settings for AWS_SQS in file .env.ini.
     */
    public const LOCAL_QUEUE = true;

    private const PHRASES_CREATE_TABLE =
        'CREATE TABLE IF NOT EXISTS emails_queue ' .
        '(' .
        'timestamp               INTEGER    NOT NULL, ' .
        'sender                  TEXT       NOT NULL, ' .
        'destination             TEXT       NOT NULL, ' .
        'subject                 TEXT       NOT NULL, ' .
        'body_html               TEXT       NOT NULL, ' .
        'attachments_paths       TEXT       default NULL, ' .
        'sent_timestamp          TEXT       default NULL ' .
        ')';

    private const PHRASES_INSERT =
        'INSERT INTO emails_queue ' .
        '(timestamp, sender, destination, subject, body_html, attachments_paths) ' .
        'VALUES (?, ?, ?, ?, ?, ?)';

    private const PHRASES_SELECT_LAST_N =
        'SELECT rowid, * FROM emails_queue ' .
        'ORDER BY rowid DESC ' .
        'LIMIT ?';

    private const PHRASES_SELECT_ALL =
        'SELECT rowid, * FROM emails_queue ' .
        'ORDER BY rowid DESC';

    private const PHRASES_DELETE =
        'DELETE FROM emails_queue WHERE ' .
        'sender = ? AND destination = ? AND subject = ? ' .
        'AND body_html = ? AND attachments_paths = ?';

    private const PHRASES_DELETE_ROWID =
        'DELETE FROM emails_queue WHERE rowid = ?';

    private const PHRASES_MARK_EMAIL_SENT =
        'UPDATE emails_queue SET sent_timestamp = ? WHERE rowid = ?';


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
            $this->DB_PATH = CLEANDECK_WRITE_PATH . '/database/emails-queue.sqlite';
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
            $this->throwException('Cannot create emails_queue table');
        }
    }

    /**
     * @throws \Exception
     */
    private function throwException(string $error_message): never
    {
        throw new \Exception($error_message . ' [error ' . $this->lastErrorCode() . ']:'
            . $this->lastErrorMsg());
    }

    /**
     * @throws \Exception
     */
    public function queueAdd(
        string $sender,
        string $destination,
        string $subject,
        string $body_html,
        string $attachments_paths = null
    ): int {
        // no duplicates!?
        $this->queueRemove($sender,
            $destination,
            $subject,
            $body_html,
            $attachments_paths);

        // main operation
        $statement = $this->prepare(self::PHRASES_INSERT);
        $dateTimeImmutable = new DateTimeImmutable();
        $timestamp = $dateTimeImmutable->getTimestamp();

        $statement->bindValue(1, $timestamp, SQLITE3_INTEGER);
        $statement->bindValue(2, $sender, SQLITE3_TEXT);
        $statement->bindValue(3, $destination, SQLITE3_TEXT);
        $statement->bindValue(4, $subject, SQLITE3_TEXT);
        $statement->bindValue(5, $body_html, SQLITE3_TEXT);
        $statement->bindValue(6, $attachments_paths, isset($attachments_paths) ? SQLITE3_TEXT : SQLITE3_NULL);

        $statement_result = $statement->execute();
        if ($statement_result === false) {
            $this->throwException('Cannot add entry to queue');
        }

        return $timestamp;
    }

    /**
     * @return array<array<string, mixed>>
     * @throws \Exception
     */
    public function queueGetLastN(int $limit): array
    {
        $statement = $this->prepare(self::PHRASES_SELECT_LAST_N);

        $statement->bindValue(1, $limit, SQLITE3_INTEGER);

        $statement_result = $statement->execute();
        if ($statement_result === false) {
            $this->throwException('Cannot get last ' . $limit . ' entries in queue');
        }

        $data = [];

        while ($row = $statement_result->fetchArray(SQLITE3_ASSOC)) {
            if (\is_array($row)) {
                $data[] = $row;
            }
        }

        $statement_result->finalize();

        return $data;
    }

    /**
     * @return array<array<string, mixed>>
     * @throws \Exception
     */
    public function queueGetAll(): array
    {
        $statement = $this->prepare(self::PHRASES_SELECT_ALL);

        $statement_result = $statement->execute();
        if ($statement_result === false) {
            $this->throwException('Cannot get all entries in queue');
        }

        $data = [];

        while ($row = $statement_result->fetchArray(SQLITE3_ASSOC)) {
            if (\is_array($row)) {
                $data[] = $row;
            }
        }

        $statement_result->finalize();

        return $data;
    }


    /**
     * @throws \Exception
     */
    public function queueRemove(
        string $sender,
        string $destination,
        string $subject,
        string $body_html,
        string $attachments_paths = null
    ): void {
        $adjusted_phrase = self::PHRASES_DELETE;
        if (!isset($attachments_paths)) {
            $adjusted_phrase = \str_replace('attachments_paths = ?', 'attachments_paths IS NULL', $adjusted_phrase);
        }

        $statement = $this->prepare($adjusted_phrase);

        $statement->bindValue(1, $sender, SQLITE3_TEXT);
        $statement->bindValue(2, $destination, SQLITE3_TEXT);
        $statement->bindValue(3, $subject, SQLITE3_TEXT);
        $statement->bindValue(4, $body_html, SQLITE3_TEXT);
        if (isset($attachments_paths)) {
            $statement->bindValue(5, $attachments_paths, SQLITE3_TEXT);
        }

        $statement_result = $statement->execute();
        if ($statement_result === false) {
            $this->throwException('Cannot delete from queue');
        }
    }

    /**
     * @throws \Exception
     */
    public function queueRemoveByRowId(int $rowid): void
    {
        $statement = $this->prepare(self::PHRASES_DELETE_ROWID);

        $statement->bindValue(1, $rowid, SQLITE3_INTEGER);

        $statement_result = $statement->execute();
        if ($statement_result === false) {
            $this->throwException('Cannot delete rowid ' . $rowid);
        }
    }


    /**
     * @throws \Exception
     */
    public function queueMarkEmailSent(int $rowid): void
    {
        $statement = $this->prepare(self::PHRASES_MARK_EMAIL_SENT);

        $dateTimeImmutable = new DateTimeImmutable();
        $timestamp = $dateTimeImmutable->getTimestamp();

        $statement->bindValue(1, $timestamp, SQLITE3_INTEGER);
        $statement->bindValue(2, $rowid, SQLITE3_INTEGER);

        $statement_result = $statement->execute();
        if ($statement_result === false) {
            $this->throwException('Cannot mark email sent: rowid ' . $rowid);
        }
    }
}
