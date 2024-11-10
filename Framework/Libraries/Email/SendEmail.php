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

namespace Framework\Libraries\Email;

require_once __DIR__ . '/../../Config/constants.php';

require_once __DIR__ . '/../AWS/AWSSES.php';
require_once __DIR__ . '/../AWS/AWSSQSSendMessage.php';
require_once __DIR__ . '/../LocalQueues/EmailsQueue.php';
require_once __DIR__ . '/../Tasks/TaskHandler.php';

use Framework\Libraries\AWS\AWSSES;
use Framework\Libraries\AWS\AWSSQSSendMessage;
use Framework\Libraries\LocalQueues\EmailsQueue;
use Framework\Libraries\Tasks\TaskHandler;

final class SendEmail
{
    /**
     * Adds an email to the local queue and starts background processing of pending emails.
     * @param string[]|null $attachments_paths
     * @throws \Exception
     */
    public static function init(
        string $subject,
        string $to,
        string $html_body,
        array  $attachments_paths = null
    ): bool {
        $emailsQueue = new EmailsQueue();
        try {
            $emailsQueue->queueAdd(
                \env('cleandeck.aws_ses.sender', 'Missing AWS SES email'),
                $to,
                $subject,
                $html_body,
                isset($attachments_paths) ? \implode(',', $attachments_paths) : null
            );
        } catch (\Exception) {
            // When the queue fails try to use one of AWS_SQS or AWS_SES directly. No background processing.
            $op_result = false;

            try {
                if (\env('cleandeck.aws_sqs.queue_url') !== null &&
                    \env('cleandeck.aws_sqs.region') !== null) {
                    // process email using AWS_SQS
                    $op_result = self::AwsSqsEmail($subject, $to, $html_body, $attachments_paths);
                }

                if (!$op_result) {
                    // process email using local queue + AWS SES
                    $op_result = self::AwsSesEmail($subject, $to, $html_body, $attachments_paths);
                }
            } catch (\Exception $e) {
                \error_log($e->getMessage());
                \syslog(LOG_ERR, $e->getMessage());
                $op_result = false;
            }

            return $op_result;
        }

        // The email is now in the local queue. Start background processing of the local emails queue.
        new TaskHandler(TaskHandler::CLEANDECK_TASK_PROCESS_PENDING_EMAILS);

        return true;
    }


    /**
     * Try to send the email to an AWS SQS queue. No background processing.
     * @param string[]|null $attachments_paths Not allowed!
     * @throws \Exception
     */
    public static function AwsSqsEmail(
        string $subject,
        string $to,
        string $html_body,
        array  $attachments_paths = null
    ): bool {
        $email_attributes = [
            'subject' => $subject,
            'to' => $to,
        ];

        // in this version AWS SQS does not handle attachments
        if (isset($attachments_paths)) {
            throw new \Exception('In this version of the application ' .
                'no attachments can be send using AWS SQS');
        }

        return AWSSQSSendMessage::init($html_body, $email_attributes);
    }

    /**
     * Try to send the email to AWS SES. No background processing.
     * @param string[]|null $attachments_paths
     * @throws \Exception
     */
    public static function AwsSesEmail(
        string $subject,
        string $to,
        string $html_body,
        array  $attachments_paths = null
    ): bool {
        if (isset($attachments_paths)) {
            return AWSSES::sendRawEmail($subject, $to, $html_body, $attachments_paths);
        }
        return AWSSES::sendEmail($subject, $to, $html_body);
    }
}
