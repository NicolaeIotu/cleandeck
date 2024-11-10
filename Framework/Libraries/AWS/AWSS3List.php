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


use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Framework\Libraries\Utils\DotEnv;

final class AWSS3List extends AWSCustomBase
{
    /**
     * List an AWS S3 bucket or path within bucket.
     * This is not a complete implementation because it only retrieves a maximum of 1000 entries which is more than
     * enough for the requirements of this application.
     * Must be able to run as a standalone script.
     * @param string|null $bucket_path A path within bucket.
     * @return bool|array<string>
     * @throws \Exception
     */
    public static function init(
        string $bucket_path = null
    ): bool|array {
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


        $params = [
            'Bucket' => $adjusted_AWS_bucket,
            'EncodingType' => 'url',
            'MaxKeys' => 1000,
        ];

        if (isset($bucket_path)) {
            $bucket_path = \ltrim($bucket_path, '/');
            $full_path = $adjusted_AWS_bucket . '/' . $bucket_path . '/';

            $params['Prefix'] = $bucket_path . '/';
        } else {
            $full_path = $adjusted_AWS_bucket;
        }


        try {
            $listResult = $s3Client->listObjectsV2($params);
        } catch (S3Exception $e) {
            $AwsErrorMessage = $e->getAwsErrorMessage();
            if (isset($AwsErrorMessage) && \strlen($AwsErrorMessage) > 0) {
                $err_msg = $AwsErrorMessage;
            } else {
                $err_msg = $e->getMessage();
            }

            $error_message = 'Cannot list path ' . $full_path . ': ' .
                $err_msg . ' (' . $bucket_path . ')';
            \syslog(LOG_ERR, $error_message);
            \error_log($error_message);

            return false;
        } catch (\Exception $e) {
            $error_message = 'Cannot list path ' . $full_path . ': ' .
                $e->getMessage() . ' (' . $bucket_path . ')';
            \syslog(LOG_ERR, $error_message);
            \error_log($error_message);

            return false;
        }


        $checkListResult = self::checkResult(
            $listResult,
            null,
            'AWS S3 listing error'
        );


        if ($checkListResult) {
            if (\is_array($listResult['Contents'])) {
                $response = [];
                foreach ($listResult['Contents'] as $item) {
                    if (!isset($item['Key'], $item['Size'])) {
                        continue;
                    }
                    if (!\is_string($item['Key'])) {
                        continue;
                    }
                    if (!\is_numeric($item['Size'])) {
                        continue;
                    }
                    if ($item['Size'] <= 0) {
                        continue;
                    }
                    $response[] = \basename($item['Key']);
                }

                return $response;
            }
            return [];
        }
        // just warn and continue with an empty result set
        $warn_message = 'Invalid response when listing ' . $bucket_path;
        \syslog(LOG_ERR, $warn_message);
        \error_log($warn_message);
        return [];
    }
}
