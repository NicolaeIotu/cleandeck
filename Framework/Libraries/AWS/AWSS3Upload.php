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


use Aws\Exception\MultipartUploadException;
use Aws\S3\Exception\S3Exception;
use Aws\S3\MultipartUploader;
use Aws\S3\S3Client;
use Framework\Libraries\Utils\DotEnv;

final class AWSS3Upload extends AWSCustomBase
{
    /**
     * Must be able to run as a standalone script.
     * @param string $file_local_path Absolute path to the file to be uploaded
     * @param string|null $bucket_directory An optional path inside $AWS_bucket
     * @throws \Exception
     */
    public static function init(
        string $file_local_path,
        string $bucket_directory = null
    ): bool {
        $rp_result = \realpath($file_local_path);
        if ($rp_result === false) {
            $error_message = 'Cannot find file to upload: ' . $file_local_path;
            \syslog(LOG_ERR, $error_message);
            \error_log($error_message);
            return false;
        }


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

        $basename_file_local_path = \basename($file_local_path);

        $adjusted_AWS_bucket = \preg_replace('/^[sS]3:\/\//', '',
            (string) $app_env['cleandeck']['aws_s3']['bucket']);

        $MAX_RETRIES = 3;
        $i = 0;

        do {
            ++$i;

            try {
                $result = $s3Client->putObject([
                    'Bucket' => $adjusted_AWS_bucket,
                    'Key' => (isset($bucket_directory) ? $bucket_directory . '/' : '') . $basename_file_local_path,
                    'SourceFile' => $file_local_path,
                ]);
            } catch (MultipartUploadException $e) {
                $source = \fopen($file_local_path, 'rb');
                \rewind($source);
                $uploader = new MultipartUploader(
                    $s3Client,
                    $source,
                    [
                        'state' => $e->getState(),
                    ]
                );
            } catch (S3Exception $e) {
                $AwsErrorMessage = $e->getAwsErrorMessage();
                if (isset($AwsErrorMessage) && \strlen($AwsErrorMessage) > 0) {
                    $err_msg = $AwsErrorMessage;
                } else {
                    $err_msg = $e->getMessage();
                }

                $error_message = 'File ' . $file_local_path . ': ' . $err_msg;
                \syslog(LOG_ERR, $error_message);
                \error_log($error_message);

                break;
            } catch (\Exception $e) {
                $error_message = 'File ' . $file_local_path . ': ' . $e->getMessage();
                \syslog(LOG_ERR, $error_message);
                \error_log($error_message);

                break;
            } finally {
                if (isset($result, $source)) {
                    \fclose($source);
                }
            }
        } while ($i < $MAX_RETRIES && !isset($result));

        if (isset($result)) {
            return self::checkResult(
                $result,
                'Upload successful: ' . $basename_file_local_path,
                'This upload failed'
            );
        }
        return false;
    }
}
