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

namespace Framework\Support\Scripts;

class ComposerScripts
{
    public static function postCreateProjectFileSystem(): void
    {
        \copy("./env.ini", "./.env.ini");
    }

    public static function postCreateProjectText(): void
    {
        \printf("\033[1;36m%s\033[0m\n\033[3;37m%s\033[0m\n\033[3;37m%s\033[0m\n\033[3;37m%s\033[0m\n" .
            "\033[3;37m%s\033[0m\n\033[1;29m%s\033[0m\n",
            'Reminders:',
            ' > adjust file .env.ini',
            ' > adjust file Application/public/robots.txt',
            ' > read short and concise content in directory /documentation',
            ' > adjust contents in directory deploy/settings and run "sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy ..."',
            'Project created successfully!'
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function keygen(): void
    {
        $env_ini_path = __DIR__ . '/../../../.env.ini';
        if (!\file_exists($env_ini_path)) {
            throw new \Exception('Missing file .env.ini. Copy file env.ini to .env.ini and adjust contents.');
        }
        $env_ini = \file_get_contents($env_ini_path);
        if (!\is_string($env_ini)) {
            throw new \Exception('Cannot read file .env.ini');
        }

        try {
            $app_key_source = \bin2hex(\random_bytes(60));
            $app_key = \password_hash($app_key_source, PASSWORD_DEFAULT);
        } catch (\Exception $exception) {
            throw new \Exception('Failed to generate a new app_key: ' . $exception->getMessage(),
                $exception->getCode(), $exception);
        }

        $replacement = 'app_key = ' . $app_key;
        $env_ini = \preg_replace('/^app_key[ ]*=.*$/m', $replacement, $env_ini, 1, $count_replacements);

        if (!\is_string($env_ini) || $count_replacements !== 1) {
            // look for a commented entry i.e. "; app_key ..."
            $env_ini = \preg_replace('/^([ ]*;[ ]*app_key[ ]*=.*)$/m', '$1' . PHP_EOL . $replacement, (string) $env_ini,
                1, $count_replacements);
            if (!\is_string($env_ini) || $count_replacements !== 1) {
                // finally, try to place it immediately after main ini section [cleandeck]
                $env_ini = \preg_replace('/^\[cleandeck]$/m', '[cleandeck]' . PHP_EOL . $replacement, (string) $env_ini,
                    1, $count_replacements);
                if (!\is_string($env_ini) || $count_replacements !== 1) {
                    throw new \Exception('Cannot find a suitable position for app_key because .env.ini structure is malformed');
                }
            }
        }

        $put_result = \file_put_contents($env_ini_path, $env_ini);
        if ($put_result === false) {
            throw new \Exception('Cannot overwrite file .env.ini');
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function populateSslSettingsFiles(): void
    {
        $ssl_settings_ini_template_path = __DIR__ . '/../../../deploy/ssl/template/ssl-settings.ini.template';
        $ssl_settings_ini_path = __DIR__ . '/../../../deploy/ssl/ssl-settings.ini';
        $copy_template_result = \copy($ssl_settings_ini_template_path, $ssl_settings_ini_path);
        if ($copy_template_result === false) {
            throw new \Exception('Cannot copy SSL settings template file from ' .
                $ssl_settings_ini_template_path . ' to ' . $ssl_settings_ini_path);
        }

        $ssl_password_file_path = __DIR__ . '/../../../deploy/ssl/generated/cleandeck-ssl-password.txt';
        $create_ssl_password_file_result = \touch($ssl_password_file_path);
        if ($create_ssl_password_file_result === false) {
            throw new \Exception('Cannot create SSL password settings file ' . $ssl_password_file_path);
        }
    }

    /**
     * @param mixed|null $op_type Values 'no-password' for no SSL password, 'own-password' for own SSL password.
     *   For any other values a new SSL password will be generated.
     * @return void
     * @throws \Exception
     */
    public static function generateSslPassword(mixed $op_type = null): void
    {
        $ssl_settings_ini_basename = 'ssl-settings.ini';
        $ssl_settings_ini_relative_path = 'deploy/ssl/' . $ssl_settings_ini_basename;
        $ssl_settings_ini_path = __DIR__ . '/../../../' . $ssl_settings_ini_relative_path;
        if (!\file_exists($ssl_settings_ini_path)) {
            throw new \Exception('Missing file ' . $ssl_settings_ini_relative_path);
        }
        $ssl_settings = \file_get_contents($ssl_settings_ini_path);
        if (!\is_string($ssl_settings)) {
            throw new \Exception('Cannot read file ' . $ssl_settings_ini_relative_path);
        }

        if ($op_type === 'no-password') {
            $ssl_password = '';
        } elseif ($op_type === 'own-password') {
            $ssl_settings_ini = \parse_ini_file($ssl_settings_ini_path, true, INI_SCANNER_TYPED);
            if (!\is_array($ssl_settings_ini)) {
                throw new \Exception('Cannot parse file ' . $ssl_settings_ini_path);
            }
            if (!isset($ssl_settings_ini['certificate-settings']) ||
                !\is_array($ssl_settings_ini['certificate-settings']) ||
                !isset($ssl_settings_ini['certificate-settings']['password'])) {
                throw new \Exception('Invalid or missing "password" value in file  ' . $ssl_settings_ini_path);
            }
            $ssl_password = $ssl_settings_ini['certificate-settings']['password'];
        } else {
            $ssl_password_source = \bin2hex(\random_bytes(18));
            $ssl_password = \password_hash($ssl_password_source, PASSWORD_DEFAULT);
            $ssl_password = \preg_replace('/[^a-zA-Z0-9]/', '', $ssl_password);
            if (!is_string($ssl_password)) {
                throw new \Exception('Error when replacing invalid password characters. Try again.');
            }
            $ssl_password = \substr($ssl_password, 4);
        }

        $replacement = 'password = ' . $ssl_password;
        $ssl_settings = \preg_replace('/^password[ ]*=.*$/m', $replacement,
            $ssl_settings, 1, $count_replacements);

        if (!\is_string($ssl_settings) || $count_replacements !== 1) {
            // look for a commented entry i.e. "; password ..."
            $ssl_settings = \preg_replace('/^([ ]*;[ ]*password[ ]*=.*)$/m', '$1' . PHP_EOL . $replacement, (string) $ssl_settings,
                1, $count_replacements);
            if (!\is_string($ssl_settings) || $count_replacements !== 1) {
                // finally, try to place it immediately after main ini section [ssl-settings]
                $ssl_settings = \preg_replace('/^\[ssl-settings]$/m', '[ssl-settings]' . PHP_EOL . $replacement, (string) $ssl_settings,
                    1, $count_replacements);
                if (!\is_string($ssl_settings) || $count_replacements !== 1) {
                    throw new \Exception('Cannot find a suitable position to insert new password in file ' .
                        $ssl_settings_ini_relative_path);
                }
            }
        }

        // update file $ssl_settings_ini_basename
        $put_result = \file_put_contents($ssl_settings_ini_path, $ssl_settings);
        if ($put_result === false) {
            throw new \Exception('Cannot overwrite file ' . $ssl_settings_ini_relative_path);
        }

        // update file **deploy/ssl/generated/cleandeck-ssl-password.txt**
        $ssl_password_raw_file_path = 'deploy/ssl/generated/cleandeck-ssl-password.txt';
        if (!\chmod($ssl_password_raw_file_path, 0o600)) {
            throw new \Exception('Cannot set permissions 0600 for file ' . $ssl_password_raw_file_path);
        }
        $put_result = \file_put_contents($ssl_password_raw_file_path, $ssl_password);
        if ($put_result === false) {
            throw new \Exception('Cannot overwrite file ' . $ssl_password_raw_file_path);
        }
        if (!\chmod($ssl_password_raw_file_path, 0o400)) {
            throw new \Exception('Cannot set permissions 0400 for file ' . $ssl_password_raw_file_path);
        }
    }

    /**
     * Used by script 'analyze'.
     * @return void
     */
    public static function applicationAnalyze(): void
    {
        // IMPORTANT! Since file tools/Application/phpstan/phpstan-baseline.php does not modify its content
        //  we have to modify its access time manually in order to trigger phpstan reevaluation.
        \touch("tools/Application/phpstan/phpstan-baseline.php");
    }
}
