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

use Framework\Libraries\Utils\WarningHandler;

require_once __DIR__ . '/../Utils/WarningHandler.php';

/**
 * This class must be extended. Must be able to run as a standalone script.
 * Records the PID of the process started to a PID file which is used to detect
 * another active instance of this class.
 */
final class UniqueCommand
{
    private int $pid;

    public function getPid(): ?int
    {
        return $this->pid;
    }

    public function setPid(int $pid): void
    {
        $this->pid = $pid;
    }

    private ?string $pidFile;

    private function setPidFile(string $raw_name): void
    {
        $name = \preg_replace('/[^a-zA-Z0-9_-]/', '', \substr($raw_name, 0, 63));

        $this->pidFile = $this->write_path . '/tasks/' . 'unique-command-' . $name . '.pid';
    }

    public function getPidFile(): ?string
    {
        return $this->pidFile;
    }

    private readonly ?string $write_path;

    /**
     * @return string|null
     */
    public function getWritePath(): ?string
    {
        return $this->write_path;
    }

    private bool $runWithErrors = false;

    public function getRunWithErrors(): bool
    {
        return $this->runWithErrors;
    }

    private readonly ?string $additional_record_info;

    /**
     * @return string|null
     */
    public function getAdditionalRecordInfo(): ?string
    {
        return $this->additional_record_info;
    }


    /**
     * @param string $context A random string used to identify the temporary file which will hold the PID of this process.
     * @param string $write_path
     * @param bool $runWithErrors
     * @param string|null $additional_record_info This information is recorded starting with the next line
     *  after the process PID in the .pid file.
     * @throws \Exception
     */
    public function __construct(
        string $context,
        string $write_path,
        bool   $runWithErrors = false,
        string $additional_record_info = null
    ) {
        if (\strlen(\trim($context)) < 2) {
            throw new \InvalidArgumentException('The context must be at least 2 characters long');
        }

        $this->write_path = $write_path;
        $this->setPidFile($context);
        $this->runWithErrors = $runWithErrors;
        $this->additional_record_info = $additional_record_info;


        $pid = \getmypid();
        // some commands are very important and should continue running even if the PID cannot be obtained
        if ($pid === false) {
            if (!$this->runWithErrors) {
                throw new \Exception('Failed to get own PID');
            }
        } else {
            $this->setPid($pid);
            // record pid
            if ($this->isRunning()) {
                // concurrency - somebody else started this task
                throw new \Exception('Found task in progress.');
            }
            $this->writePidFile();
        }
    }

    private function file_exists_safe(string $file_path): bool
    {
        $fd = @\fopen($file_path, 'r');
        if ($fd === false) {
            return false;
        }
        WarningHandler::run(static fn (): bool => \fclose($fd), null, false);
        return true;
    }

    /**
     * @throws \Exception
     */
    private function writePidFile(): void
    {
        $pid_file_dir = \dirname((string)$this->pidFile);
        if (\realpath($pid_file_dir) === false) {
            $base_err_msg = 'Failed to create a directory for pid file';
            if (!WarningHandler::run(
                static fn (): bool => \mkdir($pid_file_dir, 0o775, true),
                $base_err_msg)) {
                throw new \Exception($base_err_msg);
            }
        }

        $record_data = $this->pid . PHP_EOL;
        if (isset($this->additional_record_info)) {
            $record_data .= $this->additional_record_info . PHP_EOL;
        }

        if (\file_put_contents($this->pidFile, $record_data) === false) {
            // concurrency - somebody else is writing the pid file
            throw new \Exception('Found task in progress (writing pid file).');
        }
    }


    private function removePidFile(): void
    {
        $pid_file = $this->pidFile;

        if (!isset($pid_file)) {
            return;
        }
        if (!$this->file_exists_safe($pid_file)) {
            return;
        }
        WarningHandler::run(static fn (): bool => \unlink($pid_file), null, false);
    }


    private function isRunning(): bool
    {
        if ($this->file_exists_safe($this->pidFile)) {
            // verify the process is actually running
            $pid_file_contents = \file_get_contents($this->pidFile);
            if ($pid_file_contents === false) {
                $this->removePidFile();
                return false;
            }

            if (trim($pid_file_contents) === (string)getmypid()) {
                return false;
            }

            // a basic check: is this process still running?
            \exec('/bin/ps -p ' . \explode(PHP_EOL, $pid_file_contents)[0] . ' >/dev/null 2>&1',
                $a_null, $exit_code);
            if ($exit_code === 0) {
                return true;
            }
            $this->removePidFile();

            return false;
        }

        return false;
    }
}
