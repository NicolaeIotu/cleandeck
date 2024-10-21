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

require_once __DIR__ . '/EmailsQueue.php';
require_once __DIR__ . '/../Email/SendEmail.php';
require_once __DIR__ . '/../AWS/AWSSES.php';
require_once __DIR__ . '/../Utils/DotEnv.php';

use Framework\Libraries\AWS\AWSSES;
use Framework\Libraries\Email\SendEmail;
use Framework\Libraries\Utils\DotEnv;

/**
 * Runs in a separate process. Must be able to run as a standalone script.
 * Handles pending email operations.
 */
final class ProcessPendingEmails extends EmailsQueue
{
    /**
     * @var array<string, mixed>
     */
    private array $app_env;

    /**
     * @param string|null $db_path
     * @param bool $single_op
     * @throws \Exception
     */
    public function __construct(string $db_path = null, bool $single_op = false)
    {
        // initialization
        parent::__construct($db_path);

        $this->app_env = DotEnv::read(CLEANDECK_ROOT_PATH . '/.env.ini');
        if ($this->app_env === false) {
            $error_message = self::class . ': Invalid ini file .env.ini';
            \syslog(LOG_ERR, $error_message);
            throw new \Exception($error_message);
        }
        // END initialization


        if ($single_op) {
            // process a single operation
            $queue = $this->queueGetLastN(1);
        } else {
            // process all pending
            $queue = $this->queueGetAll();
        }


        $queue_size = \count($queue);
        if ($queue_size === 0) {
            \syslog(LOG_INFO, 'No email operations in queue');
            return;
        }

        $process_queue_errors = $this->processQueue($queue);

        if ($process_queue_errors < 1) {
            \syslog(LOG_INFO, 'Email operations queue processed successfully');
        } else {
            \syslog(LOG_ERR, 'Queued email operations (' . \count($queue) .
                ') completed with errors (' . $process_queue_errors . ')');
        }
    }


    /**
     * @param array<array<string, mixed>> $queue
     */
    private function processQueue(array $queue): int
    {
        $AWSSQS_first = isset($this->app_env['cleandeck']['aws_sqs']['queue_url'],
            $this->app_env['cleandeck']['aws_sqs']['region']);

        $count_errors = 0;

        foreach ($queue as $row) {
            if (isset($row['sent_timestamp'])) {
                // In this case the email was sent, but it could not be deleted from the queue.
                // Try to delete it now.
                try {
                    $this->queueRemoveByRowId($row['rowid']);
                } catch (\Exception $e) {
                    $err_msg = 'Error @ rowid ' . $row['rowid'] .
                        '. Could not delete email from queue: ' .
                        $e->getMessage();
                    \syslog(LOG_ERR, $err_msg);
                    \error_log($err_msg);
                }

                continue;
            }

            // continue with normal cases when the email was not sent yet
            try {
                $op_success = $this->processIndividualEmail($row, $AWSSQS_first);
            } catch (\Exception $e) {
                \error_log($e->getMessage());
                \syslog(LOG_ERR, $e->getMessage());
                $op_success = false;
            }

            if ($op_success) {
                try {
                    $this->queueRemove(
                        $row['sender'],
                        $row['destination'],
                        $row['subject'],
                        $row['body_html'],
                        $row['attachments_paths']
                    );
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

        return $count_errors;
    }


    /**
     * Behavior depends on the settings found in .env.ini file.
     * If the settings for AWS_SQS are found then this function will try to send the email to an AWS SQS queue.
     * In case of errors or when the settings for AWS_SQS are invalid or missing, then this function will try to
     * send the email using AWS SES.
     * @param array<string, mixed> $row
     * @throws \Exception
     */
    private function processIndividualEmail(array $row, bool $use_aws_sqs_first): bool
    {
        // in this version emails containing attachments are always send using AWS SES
        $has_attachment = isset($row['attachments_paths']);

        $op_success = false;

        if (!$has_attachment && $use_aws_sqs_first) {
            $op_success = SendEmail::AwsSqsEmail(
                $row['subject'],
                $row['destination'],
                $row['body_html']
            );
        }

        if (!$op_success) {
            $op_success = $this->processAWSSESOperation($row);
        }

        if ($op_success) {
            try {
                $this->queueRemoveByRowId($row['rowid']);
            } catch (\Exception $e) {
                $err_msg = 'Error @ rowid ' . $row['rowid'] .
                    '. Could not delete email from queue: ' .
                    $e->getMessage();

                // mark this email as send
                try {
                    $this->queueMarkEmailSent($row['rowid']);
                } catch (\Exception $e2) {
                    $err_msg .= '. Cannot mark email as sent: ' . $e2->getMessage();
                }

                \syslog(LOG_ERR, $err_msg);
                \error_log($err_msg);
            }
        }

        return $op_success;
    }


    /**
     * Does not throw. Send error messages to syslog.
     * @param array<string, mixed> $row
     * @throws \Exception
     */
    private function processAWSSESOperation(array $row): bool
    {
        if (isset($row['attachments_paths'])) {
            return AWSSES::sendRawEmail(
                $row['subject'],
                $row['destination'],
                $row['body_html'],
                \explode(',', (string) $row['attachments_paths'])
            );
        }

        return AWSSES::sendEmail(
            $row['subject'],
            $row['destination'],
            $row['body_html']
        );
    }
}
