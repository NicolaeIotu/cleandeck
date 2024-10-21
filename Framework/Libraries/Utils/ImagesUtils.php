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

namespace Framework\Libraries\Utils;

use Framework\Libraries\LocalQueues\FileOpsQueue;
use Framework\Libraries\LocalQueues\ProcessPendingFileOps;
use Framework\Libraries\Tasks\TaskHandler;

final class ImagesUtils
{
    /**
     * @return array<string, mixed>
     */
    public static function profilePictureHandler(string $pictures): array
    {
        $result = [
            'picture' => null,
        ];

        $picture = \explode(',', $pictures)[0];

        $static_images_dir = CLEANDECK_DYNAMIC_PATH . '/user-pics';
        // download the picture if missing from the local directory $static_images_dir
        // this can be improved by using async download
        $static_images_dir_path = \realpath($static_images_dir);
        if ($static_images_dir_path === false) {
            $static_images_dir_base_err_msg = 'Cannot create static directory for images: ' . $static_images_dir;
            try {
                if (!WarningHandler::run(
                    static fn (): bool => \mkdir($static_images_dir, 0o775, true),
                    $static_images_dir_base_err_msg)) {
                    $result['picture_download_error'] = $static_images_dir_base_err_msg;
                    return $result;
                }
            } catch (\Exception $exception) {
                $result['picture_download_error'] = $exception->getMessage();
                return $result;
            }

            $static_images_dir_path = \realpath($static_images_dir);
        } else {
            if (!\is_dir($static_images_dir)) {
                $result['picture_download_error'] = 'Path is not a directory: ' . $static_images_dir;
                return $result;
            }
        }

        $static_images_dir = $static_images_dir_path;

        if (\is_dir($static_images_dir)) {
            $pic_path = \realpath($static_images_dir . '/' . $picture);
            $fileOpsQueue = new FileOpsQueue();

            if ($pic_path === false) {
                try {
                    // Download the picture from S3 (and process any other pending file operations).
                    // This is a synchronous operation.
                    new ProcessPendingFileOps(
                        null,
                        [
                            'op_type' => 'download',
                            'destination' => $static_images_dir,
                            'source' => 'user-pics/' . $picture,
                        ]
                    );
                    // END download the picture from S3

                    $result['picture'] = $picture;
                } catch (\Exception $e) {
                    $result['picture'] = null;
                    if (\env('cleandeck.ENVIRONMENT') === 'development') {
                        $result['picture_download_error'] = $e->getMessage();
                    } else {
                        $result['picture_download_error'] = 'Cannot set download. For more info see logs.';
                    }

                    return $result;
                }
            } else {
                $result['picture'] = $picture;

                // Important! Make sure this file is not in queue (only for a local queue)
                try {
                    $fileOpsQueue->queueRemove(
                        'download',
                        $static_images_dir,
                        'user-pics/' . $picture
                    );
                } catch (\Exception $e) {
                    $remove_failure_message = 'Could not remove user picture download from queue: ' . $e->getMessage();
                    \error_log($remove_failure_message);
                }

                // the picture might need upload, so it is better to try and process the queue
                new TaskHandler(TaskHandler::CLEANDECK_TASK_PROCESS_PENDING_FILE_OPS);
            }
        } else {
            $result['picture_download_error'] = 'Images static path for images is not a directory.';
        }

        // If a picture was downloaded then perform a cleanup of images in the background.
        // This seems to be the most appropriate time to do the cleanup of images.
        if (isset($result['picture'])) {
            new TaskHandler(TaskHandler::CLEANDECK_TASK_CLEANUP_DYNAMIC_DIRECTORY,
                [
                    'target_directory' => $static_images_dir_path,
                    'remove_linked_content' => true,
                ]);
        }

        return $result;
    }
}
