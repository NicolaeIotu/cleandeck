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

namespace Framework\Support\Utils;

require_once __DIR__ . '/../../Config/constants.php';
require_once __DIR__ . '/../../Libraries/Utils/DotEnv.php';
require_once __DIR__ . '/../../Libraries/Utils/WarningHandler.php';

use Framework\Libraries\Utils\DotEnv;
use Framework\Libraries\Utils\WarningHandler;

final class CleanDeckDeploy
{
    private const COMMANDS = [
        'php',
        'apache2',
        'nginx',
        'nginx-ex',
    ];

    private readonly string $definitions_file_path;

    /**
     * @return array<string, array<string, string|array<string, string>>>
     * @throws \Exception
     */
    private function getTransfers(): array
    {
        if (!file_exists($this->definitions_file_path)) {
            throw new \Exception('Missing file ' . $this->definitions_file_path);
        }

        $tf_contents = WarningHandler::run(
            static fn (string $transfers_file_path): string => \file_get_contents($transfers_file_path),
            null, true, null, $this->definitions_file_path);

        if ($tf_contents === false) {
            throw new \Exception('Cannot read file ' . $this->definitions_file_path);
        }

        $transfers_array = \json_decode((string)$tf_contents, true);
        if (!isset($transfers_array)) {
            throw new \Exception('Invalid json content in file ' . $this->definitions_file_path);
        }

        foreach (self::COMMANDS as $command) {
            if (!isset($transfers_array[$command])) {
                throw new \Exception('Missing json section "' . $command .
                    '" of file ' . $this->definitions_file_path);
            }
            if (!is_array($transfers_array[$command])) {
                throw new \Exception('Invalid json section "' . $command .
                    '" of file ' . $this->definitions_file_path);
            }
        }

        return $transfers_array;
    }

    /**
     * @param CDMessageFormatter $cdMessageFormatter
     * @param string[] $cmd_args
     * @return void
     * @throws \Exception
     */
    private function deploy(CDMessageFormatter $cdMessageFormatter, array $cmd_args): void
    {
        $valid_commands = \array_filter($cmd_args,
            static function ($cmd) use ($cdMessageFormatter): bool {
                $vc = \is_string($cmd) &&
                    \in_array(\strtolower($cmd), self::COMMANDS, true);
                if (!$vc) {
                    $cdMessageFormatter->error('Invalid instruction "' . $cmd . '"');
                }
                return $vc;
            }, ARRAY_FILTER_USE_BOTH);
        $valid_commands = \array_map(static function ($arg): string {
            return \strtolower($arg);
        }, $valid_commands);

        if ($valid_commands === []) {
            $cdMessageFormatter->error('Acceptable instructions: ' . \implode(', ', self::COMMANDS));
            throw new \Exception('No usable instructions');
        }

        $app_env = DotEnv::read(CLEANDECK_ROOT_PATH . '/.env.ini');
        if ($app_env === false) {
            throw new \Exception('Invalid ini file .env.ini');
        }

        $transfers = $this->getTransfers();

        $i = 1;

        foreach ($valid_commands as $valid_command) {
            if (!isset($transfers[$valid_command])) {
                $cdMessageFormatter->warn('Missing instructions for command "' . $valid_command . '"');
                continue;
            }
            if (!\is_array($transfers[$valid_command])) {
                $cdMessageFormatter->warn('Invalid transfer list for instruction "' . $valid_command . '"');
                continue;
            }

            foreach ($transfers[$valid_command] as $src => $destination) {
                $cdMessageFormatter->content($i . '. Copy ' . $src . ' to ' . $destination);
                $this->transferFile($src, $destination, $app_env);
                ++$i;
            }
        }

        $cdMessageFormatter->success('Transfers completed');
        $cdMessageFormatter->nl();
    }


    /**
     * @param CDMessageFormatter $cdMessageFormatter
     * @param string[] $cmd_args
     * @throws \Exception
     */
    public function __construct(CDMessageFormatter $cdMessageFormatter, array $cmd_args)
    {
        $cdMessageFormatter->important('This utility should run only after checking/adjusting file ".env.ini" and ' .
            'other relevant files in directory "deploy/settings".');
        $cdMessageFormatter->content('Using transfer settings from "deploy/settings/definitions.json".' . PHP_EOL);

        $cdMessageFormatter->subsection('Transfers:');

        $this->definitions_file_path = getcwd() . '/deploy/settings/definitions.json';
        $this->deploy($cdMessageFormatter, $cmd_args);

        $cdMessageFormatter->warn('Please check the contents of the settings at destinations.');
    }

    /**
     * @param string $source
     * @param string $destination
     * @param mixed[] $app_env
     * @return void
     * @throws \Exception
     */
    private function transferFile(string $source, string $destination, array $app_env): void
    {
        if (!\file_exists($source)) {
            throw new \Exception('Missing source file ' . $source);
        }

        if (\file_exists($destination)) {
            $destination_backup = $destination . '.bak';
            // create a backup of $destination (delete previous backup of $destination)
            if (\file_exists($destination_backup)) {
                // create a backup of $destination (delete previous backup of $destination)
                if (!WarningHandler::run(static fn (): bool => \copy($destination, $destination_backup))) {
                    throw new \Exception('Cannot create a backup of the destination ' . $destination_backup);
                }
            }

            if (!WarningHandler::run(static fn (): bool => \copy($destination, $destination . '.bak'))) {
                throw new \Exception('Cannot remove file ' . $destination);
            }
        }

        $source_contents = WarningHandler::run(
            static fn (string $source): string => \file_get_contents($source),
            null, true, null, $source);

        $server_name = str_ireplace(['http://', 'https://'], '', $app_env['cleandeck']['baseURL']);

        // Special strings can be used in configuration files and these strings will be replaced below:
        //  - CLEANDECK_PUBLIC_PATH: replaced by the constant with the same name
        //  - CLEANDECK_SSL_CERTIFICATES_PATH: replaced by the realpath to directory 'deploy/ssl/generated'
        $source_contents = \str_replace(
            [
                'CLEANDECK_PUBLIC_PATH',
                'CLEANDECK_DEPLOY_PATH',
                'CLEANDECK_SERVER_NAME',
            ],
            [
                CLEANDECK_PUBLIC_PATH,
                \realpath('deploy'),
                $server_name,
            ],
            $source_contents);

        if (!\is_string($source_contents)) {
            throw new \Exception('Cannot remove file ' . $destination);
        }


        if (!WarningHandler::run(static fn (): bool => \file_put_contents($destination, $source_contents))) {
            throw new \Exception('Cannot copy file ' . $source . ' to ' . $destination);
        }
    }
}
