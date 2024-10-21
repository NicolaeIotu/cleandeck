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
require_once __DIR__ . '/../Utils/WarningHandler.php';
require_once __DIR__ . '/../AWS/AWSCustomBase.php';

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Framework\Libraries\Utils\DotEnv;
use Framework\Libraries\Utils\WarningHandler;

final class AWSS3Download extends AWSCustomBase
{
    /**
     * Downloads an object from an AWS S3 bucket.
     * Must be able to run as a standalone script.
     * @param string $file_bucket_path The bucket path to the file to be downloaded i.e. 'path/to/file.ext' or 'file.ext'
     * @param string $local_download_directory Absolute path to the local destination directory
     * @throws \Exception
     */
    public static function init(
        string $file_bucket_path,
        string $local_download_directory
    ): bool|int {
        $app_env = DotEnv::read(CLEANDECK_ROOT_PATH . '/.env.ini');
        if ($app_env === false) {
            $error_message = self::class . ': Invalid ini file .env.ini';
            \syslog(LOG_ERR, $error_message);
            \error_log($error_message);
            return false;
        }

        $S3Client_options = [
            'region' => $app_env['cleandeck']['aws_s3']['region'],
            'version' => '2006-03-01',
        ];
        if (isset($app_env['cleandeck']['AWS_IAM_USER']['key'], $app_env['cleandeck']['AWS_IAM_USER']['secret'])) {
            $S3Client_options['credentials'] = [
                'key' => $app_env['cleandeck']['AWS_IAM_USER']['key'],
                'secret' => $app_env['cleandeck']['AWS_IAM_USER']['secret'],
            ];
        }

        $s3Client = new S3Client($S3Client_options);

        $adjusted_AWS_bucket = \preg_replace('/^[sS]3:\/\//', '',
            (string) $app_env['cleandeck']['aws_s3']['bucket']);

        $target_file_name = \basename($file_bucket_path);
        $download_path = $local_download_directory . '/' . $target_file_name;


        try {
            $downloadResult = $s3Client->getObject([
                'Bucket' => $adjusted_AWS_bucket,
                'Key' => \ltrim($file_bucket_path, '/'),
                'SaveAs' => $download_path,
            ]);
        } catch (S3Exception $e) {
            $AwsErrorMessage = $e->getAwsErrorMessage();
            if (isset($AwsErrorMessage) && \strlen($AwsErrorMessage) > 0) {
                $err_msg = $AwsErrorMessage;
            } else {
                $err_msg = $e->getMessage();
            }

            $error_message = 'Cannot download file ' . $target_file_name . ': ' .
                $err_msg . ' [' . $file_bucket_path . ']';

            // cleanup generated status files
            if (\file_exists($download_path)) {
                WarningHandler::run(static fn (): bool => \unlink($download_path), null, false);
            }

            \syslog(LOG_ERR, $error_message);
            \error_log($error_message);

            return false;
        } catch (\Exception $e) {
            $error_message = 'Cannot download file ' . $target_file_name . ': ' .
                $e->getMessage() . ' [' . $file_bucket_path . ']';

            // cleanup generated status files (if any)
            if (\file_exists($download_path)) {
                WarningHandler::run(static fn (): bool => \unlink($download_path), null, false);
            }

            \syslog(LOG_ERR, $error_message);
            \error_log($error_message);

            return false;
        }


        return self::checkResult(
            $downloadResult,
            'Downloaded successfully to ' . $download_path,
            'This download failed: ' . $target_file_name,
            true
        );
    }
}
