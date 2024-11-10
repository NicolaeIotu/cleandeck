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

require_once __DIR__ . '/../Utils/DotEnv.php';
require_once __DIR__ . '/../AWS/AWSCustomBase.php';
require_once __DIR__ . '/../AWS/AWSS3List.php';

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Framework\Libraries\Utils\DotEnv;

final class AWSS3Delete extends AWSCustomBase
{
    // All AWSS3Delete methods must return a boolean value. If the target object is missing from S3, the operation
    // should be considered successful and return TRUE.
    /**
     * Delete an object from an AWS S3 bucket. Can empty and delete directories, but not recursively.
     * Must be able to run as a standalone script.
     * @param string $bucket_path The bucket path i.e. 'path/to/file/or/directory'.
     *  Do not add the prefix 's3://bucket-name'. Do not add the prefix '/'.
     * @param bool $is_directory Indicate if the $bucket_path is a directory.
     * @param bool $delete_directory If the $bucket_path is a directory and this parameter is set to true then delete
     *  the directory as well at the completion of the operation.
     * @throws \Exception
     */
    public static function init(
        string $bucket_path,
        bool   $is_directory = false,
        bool   $delete_directory = true
    ): bool {
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
            (string)$app_env['cleandeck']['aws_s3']['bucket']);

        try {
            if ($is_directory) {
                // get directory contents first
                try {
                    $directory_contents = AWSS3List::init(
                        $bucket_path
                    );
                } catch (\Exception $e) {
                    \error_log($e->getMessage());
                    \syslog(LOG_ERR, $e->getMessage());
                    // continue local logic
                    $directory_contents = null;
                }

                if (\is_array($directory_contents) && $directory_contents !== []) {
                    $delete_objects_array = self::fileNamesToKeys($directory_contents, $bucket_path);
                    $count_delete_ops = \count($delete_objects_array);

                    $params = [
                        'Bucket' => $adjusted_AWS_bucket,
                        'Delete' => [
                            'Objects' => $delete_objects_array,
                            'Quiet' => true,
                        ],
                    ];
                    $result = $s3Client->deleteObjects($params);

                    if (isset($result['Errors'])) {
                        if (\is_array($result['Errors'])) {
                            $count_delete_errors = \count($result['Errors']);
                            if ($count_delete_errors > 0) {
                                if ($count_delete_errors >= $count_delete_ops) {
                                    $error_message = 'All delete operations failed -> ' . $count_delete_ops;
                                } else {
                                    $error_message = 'Some delete operations failed -> ' .
                                        $count_delete_errors . ' out of ' . $count_delete_ops;
                                }

                                \syslog(LOG_ERR, $error_message);
                                \error_log($error_message);

                                return false;
                            }
                        }
                    }

                } else {
                    // at this point we should have an error already
                    // just in case
                    $error_message = 'Invalid listing of key: ' . $bucket_path;
                    \syslog(LOG_ERR, $error_message);
                    \error_log($error_message);

                    return false;
                }

                if ($delete_directory) {
                    // remove the directory as well
                    $s3Client->deleteObject([
                        'Bucket' => $adjusted_AWS_bucket,
                        'Key' => $bucket_path,
                    ]);
                }
            } else {
                $s3Client->deleteObject([
                    'Bucket' => $adjusted_AWS_bucket,
                    'Key' => $bucket_path,
                ]);
            }
        } catch (S3Exception $e) {
            if (!$delete_directory) {
                // If an object is missing, then the deletion should be successful!
                $aws_error_code = \strtolower((string)$e->getAwsErrorCode());
                if ($aws_error_code === 'nosuchkey') {
                    $error_message = 'No such key: ' . $bucket_path;
                    \syslog(LOG_ERR, $error_message);
                    \error_log($error_message);

                    return true;
                }
            }

            $AwsErrorMessage = $e->getAwsErrorMessage();
            if (isset($AwsErrorMessage) && \strlen($AwsErrorMessage) > 0) {
                $err_msg = $AwsErrorMessage;
            } else {
                $err_msg = $e->getMessage();
            }

            $error_message = 'Cannot delete key: ' .
                $err_msg . ' (' . $bucket_path . ')';
            \syslog(LOG_ERR, $error_message);
            \error_log($error_message);

            return false;
        } catch (\Exception $e) {
            $error_message = 'Cannot delete key: ' .
                $e->getMessage() . ' (' . $bucket_path . ')';
            \syslog(LOG_ERR, $error_message);
            \error_log($error_message);

            return false;
        }

        return true;
    }

    /**
     * @param string[] $file_names_array
     * @return array<array<string, string>>
     */
    public static function fileNamesToKeys(array $file_names_array, string $prefix = ''): array
    {
        $result = [];
        if (\strlen($prefix) > 0) {
            $adjusted_prefix = \preg_replace(
                ['/^\//', '/\/$/'],
                '',
                $prefix
            );
            $adjusted_prefix .= '/';
        } else {
            $adjusted_prefix = $prefix;
        }

        foreach ($file_names_array as $file_name_array) {
            if (!\is_string($file_name_array)) {
                continue;
            }
            if (\strlen($file_name_array) <= 0) {
                continue;
            }
            $result[] = [
                'Key' => $adjusted_prefix . $file_name_array,
            ];
        }

        return $result;
    }
}
