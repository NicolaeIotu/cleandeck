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

namespace Framework\Libraries\AWS;

require_once __DIR__ . '/../../Config/constants.php';
require_once CLEANDECK_VENDOR_PATH . '/autoload.php';

require_once __DIR__ . '/../Utils/DotEnv.php';
require_once __DIR__ . '/../AWS/AWSCustomBase.php';


use Aws\Sqs\Exception\SqsException;
use Aws\Sqs\SqsClient;
use Framework\Libraries\Utils\DotEnv;

final class AWSSQSSendMessage extends AWSCustomBase
{
    /**
     * Post a message to an AWS SQS queue.
     * Must be able to run as a standalone script.
     * @param array<string, string>|null $messageAttributes
     * @throws \Exception
     */
    public static function init(
        string $messageBody,
        array  $messageAttributes = null
    ): bool {
        $app_env = DotEnv::read(CLEANDECK_ROOT_PATH . '/.env.ini');
        if ($app_env === false) {
            $error_message = self::class . ': Invalid ini file .env.ini';
            \syslog(LOG_ERR, $error_message);
            \error_log($error_message);
            return false;
        }

        $SQSClient_options = [
            'region' => $app_env['cleandeck']['aws_sqs']['region'],
            'version' => '2012-11-05',
        ];

        if (isset($app_env['cleandeck']['AWS_IAM_USER']['key'], $app_env['cleandeck']['AWS_IAM_USER']['secret'])) {
            $SQSClient_options['credentials'] = [
                'key' => $app_env['cleandeck']['AWS_IAM_USER']['key'],
                'secret' => $app_env['cleandeck']['AWS_IAM_USER']['secret'],
            ];
        }

        $sqsClient = new SqsClient($SQSClient_options);


        $messageParams = [
            // The url of the queue i.e. 'https://sqs.us-west-2.amazonaws.com/012345678901/myqueue'.
            'QueueUrl' => $app_env['cleandeck']['aws_sqs']['queue_url'],
            'DelaySeconds' => 0,
            'MessageBody' => $messageBody,
        ];

        if (isset($messageAttributes)) {
            $messageAttributesBuild = self::buildMessageAttributes($messageAttributes);
            if ($messageAttributesBuild !== []) {
                $messageParams['MessageAttributes'] = $messageAttributesBuild;
            }
        }


        try {
            $sqs_result = $sqsClient->sendMessage($messageParams);
        } catch (SqsException $e) {
            $AwsErrorMessage = $e->getAwsErrorMessage();
            if (isset($AwsErrorMessage) && \strlen($AwsErrorMessage) > 0) {
                $err_msg = $AwsErrorMessage;
            } else {
                $err_msg = $e->getMessage();
            }

            $error_message = "Cannot send SQS message '" .
                \substr($messageBody, 0, 20) . "...' : " .
                $err_msg;
        } catch (\Exception $e) {
            $error_message = "Cannot send SQS message '" .
                \substr($messageBody, 0, 20) . "...' : " .
                $e->getMessage();
        }

        if (isset($sqs_result)) {
            return self::checkResult(
                $sqs_result,
                null,
                'AWS SQS send message failed'
            );
        }
        if (isset($error_message)) {
            \syslog(LOG_ERR, $error_message);
            \error_log($error_message);
        }
        return false;
    }


    /**
     * Only handling strings and numbers.
     * @param array<string, string> $attributes
     * @return array<string, array<string, string>>
     */
    public static function buildMessageAttributes(array $attributes): array
    {
        $result = [];
        foreach ($attributes as $name => $value) {
            $attribute = [];

            if (\is_numeric($value)) {
                $attribute['DataType'] = 'Number';
            } else {
                $attribute['DataType'] = 'String';
            }

            $attribute['StringValue'] = (string)$value;


            $result[$name] = $attribute;
        }

        return $result;
    }
}
