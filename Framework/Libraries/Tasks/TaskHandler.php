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

namespace Framework\Libraries\Tasks;

class TaskHandler
{
    public const CLEANDECK_TASK_PROCESS_PENDING_EMAILS = 1;
    public const CLEANDECK_TASK_PROCESS_PENDING_FILE_OPS = 2;
    public const CLEANDECK_TASK_CLEANUP_DYNAMIC_DIRECTORY = 3;

    /**
     * @param int $task
     * @param array<string,mixed>|null $task_arguments
     */
    public function __construct(int $task, ?array $task_arguments = null)
    {
        $cmd = "require_once ('" . CLEANDECK_FRAMEWORK_PATH . "/Libraries/Tasks/UniqueCommand.php'); ";

        switch ($task) {
            case self::CLEANDECK_TASK_PROCESS_PENDING_EMAILS:
                $cmd .= "new Framework\Libraries\Tasks\UniqueCommand('Task_Process_Pending_Emails', '" .
                    CLEANDECK_WRITE_PATH . "', false); " .
                    "require_once ('" . CLEANDECK_FRAMEWORK_PATH . "/Libraries/LocalQueues/ProcessPendingEmails.php'); " .
                    "new Framework\Libraries\LocalQueues\ProcessPendingEmails();";
                break;
            case self::CLEANDECK_TASK_PROCESS_PENDING_FILE_OPS:
                $cmd .= "new Framework\Libraries\Tasks\UniqueCommand('Task_Process_Pending_File_Ops', '" .
                    CLEANDECK_WRITE_PATH . "', false); " .
                    "require_once ('" . CLEANDECK_FRAMEWORK_PATH . "/Libraries/LocalQueues/ProcessPendingFileOps.php'); " .
                    "new Framework\Libraries\LocalQueues\ProcessPendingFileOps();";
                break;
            case self::CLEANDECK_TASK_CLEANUP_DYNAMIC_DIRECTORY:
                try {
                    $cmd .= $this->getCleanupDynamicDirCommand($task_arguments);
                } catch (\Exception $exception) {
                    \syslog(LOG_ERR, $exception->getMessage());
                    \error_log($exception->getMessage());
                    return;
                }
                break;
            default:
                $err_msg = 'TaskHandler error: Unknown task: ' . $task;
                \syslog(LOG_ERR, $err_msg);
                \error_log($err_msg);
                return;
        }

        // You can use 'eval' here in order to debug during development.
        // eval($cmd);
        // , or get the command and run it separately:
        // syslog(LOG_INFO, $cmd);

        try {
            \exec('/bin/php -r "' . $cmd . '" >/dev/null 2>&1 &');
        } catch (\Exception $exception) {
            $err_msg = 'TaskHandler task failed: ' . $exception->getMessage();
            \syslog(LOG_ERR, $err_msg);
            \error_log($err_msg);
            return;
        }
    }

    /**
     * @param array<string,mixed> $task_arguments
     * @return string
     * @throws \Exception
     */
    private function getCleanupDynamicDirCommand(array $task_arguments = null): string
    {
        if (!isset($task_arguments,
            $task_arguments['target_directory'])) {
            throw new \Exception('TaskHandler has invalid arguments for task "cleanup directory"');
        }

        $task_basename = \md5((string)$task_arguments['target_directory']);
        $realpath_target_directory = \realpath($task_arguments['target_directory']);
        if ($realpath_target_directory === false) {
            throw new \Exception('TaskHandler error: cleanup - no such directory: ' .
                $task_arguments['target_directory']);
        }
        return "new Framework\Libraries\Tasks\UniqueCommand('Task_CleanupDir_" . $task_basename . "', '" .
            CLEANDECK_WRITE_PATH . "', false, '" . $realpath_target_directory . "'); " .
            "require_once ('" . CLEANDECK_FRAMEWORK_PATH . "/Libraries/Tasks/DynamicDirectoryCleanup.php'); " .
            "new Framework\Libraries\Tasks\DynamicDirectoryCleanup(" .
            "'" . $task_arguments['target_directory'] . "', " .
            \env('cleandeck.io_files_retention.max_count', 10000) . ", " .
            \env('cleandeck.io_files_retention.max_age_days', 90) . ", " .
            (($task_arguments['recursive'] ?? true) ? 'true' : 'false') . ", " .
            (($task_arguments['remove_linked_content'] ?? false) ? 'true' : 'false') .
            ");";
    }
}
