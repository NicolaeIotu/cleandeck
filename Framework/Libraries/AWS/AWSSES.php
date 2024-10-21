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

require_once __DIR__ . '/../../Config/Constants.php';
require_once CLEANDECK_VENDOR_PATH . '/autoload.php';

require_once __DIR__ . '/../Utils/DotEnv.php';
require_once __DIR__ . '/../AWS/AWSCustomBase.php';

use Aws\Exception\AwsException;
use Aws\Exception\CredentialsException;
use Aws\Ses\SesClient;
use Framework\Libraries\Utils\DotEnv;
use PHPMailer\PHPMailer\PHPMailer;

final class AWSSES extends AWSCustomBase
{
    /**
     * @throws \Exception
     */
    public static function sendEmail(string $subject, string $to, string $html_body): bool|int
    {
        $app_env = DotEnv::read(CLEANDECK_ROOT_PATH . '/.env.ini');
        if ($app_env === false) {
            $error_message = self::class . ': Invalid ini file .env.ini';
            \syslog(LOG_ERR, $error_message);
            return false;
        }

        $SES_sender_email = $app_env['cleandeck']['aws_ses']['sender'];
        $SES_region = $app_env['cleandeck']['aws_ses']['region'];

        $SESClient_options = [
            'region' => $SES_region,
            'version' => '2010-12-01',
        ];
        if (isset($app_env['cleandeck']['AWS_IAM_USER']['key'], $app_env['cleandeck']['AWS_IAM_USER']['secret'])) {
            $SESClient_options['credentials'] = [
                'key' => $app_env['cleandeck']['AWS_IAM_USER']['key'],
                'secret' => $app_env['cleandeck']['AWS_IAM_USER']['secret'],
            ];
        }

        // Create an SesClient.
        $SesClient = new SesClient($SESClient_options);


        // Replace these sample addresses with the addresses of your recipients. If
        // your account is still in the sandbox, these addresses must be verified.
        $recipient_emails = [$to];

        // Specify a configuration set. Comment below if not using a configuration set.
        // $configuration_set = 'ConfigSet';

        $char_set = 'UTF-8';

        try {
            $sendEmailResult = $SesClient->sendEmail([
                'Destination' => [
                    'ToAddresses' => $recipient_emails,
                ],
                // 'ReplyToAddresses' => [$SES_sender_email],
                'Source' => $SES_sender_email,
                // IMPORTANT! If using both Body->Html and Body->Text then 2 emails are sent
                'Message' => [
                    'Body' => [
                        'Html' => [
                            'Charset' => $char_set,
                            'Data' => $html_body,
                        ],
                    ],
                    'Subject' => [
                        'Charset' => $char_set,
                        'Data' => $subject,
                    ],
                ],
                // If you aren't using a configuration set, comment or delete the following line
                // 'ConfigurationSetName' => $configuration_set,
            ]);

                // $messageId = $sendEmailResult['MessageId'];
                // syslog(LOG_INFO, ' AWSSES email sent! Message ID: ' . $messageId);
        } catch (AwsException $e) {
            // error_log -> log errors  to i.e. /var/log/php-fpm/www-error.log
            $error_message = 'AWS SES sendEmail failed with error (AWS): ' .
                ($e->getAwsErrorMessage() || $e->getMessage());
        } catch (CredentialsException $e) {
            $error_message = 'AWS SES credentials error: ' . $e->getMessage();
        } catch (\Exception $e) {
            $error_message = 'AWS SES sendEmail failed with error: ' . $e->getMessage();
        }

        if (isset($sendEmailResult)) {
            return self::checkResult(
                $sendEmailResult,
                'AWS SES sendEmail success',
                'AWS SES sendEmail failed'
            );
        }
        if (isset($error_message)) {
            \syslog(LOG_ERR, $error_message);
        }
        return false;
    }

    /**
     * @param string[] $attachments_paths
     * @throws \Exception
     */
    public static function sendRawEmail(
        string $subject,
        string $to,
        string $html_body,
        array  $attachments_paths
    ): bool|int {
        $app_env = DotEnv::read(CLEANDECK_ROOT_PATH . '/.env.ini');
        if ($app_env === false) {
            $error_message = self::class . ': Invalid ini file .env.ini';
            \syslog(LOG_ERR, $error_message);
            return false;
        }

        $SES_sender_email = $app_env['cleandeck']['aws_ses']['sender'];
        $SES_region = $app_env['cleandeck']['aws_ses']['region'];

        $SESClient_options = [
            'region' => $SES_region,
        ];
        if (isset($app_env['cleandeck']['AWS_IAM_USER']['key'], $app_env['cleandeck']['AWS_IAM_USER']['secret'])) {
            $SESClient_options['credentials'] = [
                'key' => $app_env['cleandeck']['AWS_IAM_USER']['key'],
                'secret' => $app_env['cleandeck']['AWS_IAM_USER']['secret'],
            ];
        }

        // Create an SesClient.
        $SesClient = new SesClient($SESClient_options);


        $phpMailer = new PHPMailer();
        try {
            $phpMailer->setFrom($SES_sender_email);
            $phpMailer->addAddress($to);
            $phpMailer->Subject = $subject;
            $phpMailer->Body = $html_body;
            $phpMailer->AltBody = $html_body;

            foreach ($attachments_paths as $attachment_path) {
                $add_attachment_result = $phpMailer->addAttachment($attachment_path);
                if (!$add_attachment_result) {
                    \syslog(LOG_ERR, 'Could not add attachment: ' . $attachment_path);
                    return false;
                }
            }

            // if using configuration sets
            // $mail->addCustomHeader('X-SES-CONFIGURATION-SET', $configuration_set);
        } catch (\Exception $exception) {
            // error_log -> log errors  to i.e. /var/log/php-fpm/www-error.log
            \syslog(LOG_ERR, 'Could not prepare the email. PHPMailer error: ' . $exception->getMessage());
            return false;
        }


        try {
            if (!$phpMailer->preSend()) {
                \syslog(LOG_ERR, 'Could not prepare the email. PHPMailer error: ' . $phpMailer->ErrorInfo);
                return false;
            }
            $message = $phpMailer->getSentMIMEMessage();
        } catch (\Exception $exception) {
            \syslog(LOG_ERR, 'Could not prepare the email. PHPMailer error: ' . $exception->getMessage());
            return false;
        }


        try {
            $sendRawEmailResult = $SesClient->sendRawEmail([
                'RawMessage' => [
                    'Data' => $message,
                ],
            ]);

            // $messageId = $sendRawEmailResult['MessageId'];
            // syslog(LOG_INFO, ' AWSSES raw email sent! Message ID: ' . $messageId);
        } catch (AwsException $e) {
            \syslog(LOG_ERR, 'AWS SES sendRawEmail failed with AWS SDK error: ' . $e->getAwsErrorMessage());
            return false;
        } catch (CredentialsException $e) {
            \syslog(LOG_ERR, 'AWS SES sendRawEmail failed with error: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            // this happens when i.e. there is no connection to AWS metadata service (during local tests) a.o.
            \syslog(LOG_ERR, 'AWS SES sendRawEmail failed with error: ' . $e->getMessage());
            return false;
        }

        return self::checkResult(
            $sendRawEmailResult,
            'AWS SES sendRawEmail success',
            'AWS SES sendRawEmail failed'
        );
    }
}
