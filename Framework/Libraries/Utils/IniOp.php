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

class IniOp
{
    private readonly string $ini_path;

    private string|false $ini_string;

    /**
     * @var array<string, mixed>|false
     */
    private array|false $ini;

    /**
     * @throws \Exception
     */
    public function __construct(string $file_path)
    {
        $file_realpath = \realpath($file_path);
        if ($file_realpath === false) {
            throw new \Exception('Missing ini file ' . $file_path);
        }

        $this->ini_path = $file_realpath;

        $read_ini_err_msg = 'Cannot read ini file ' . $file_realpath;
        $this->ini_string = WarningHandler::run(
            static fn (): string|false => \file_get_contents($file_realpath),
            $read_ini_err_msg);
        if ($this->ini_string === false) {
            throw new \Exception($read_ini_err_msg);
        }

        $this->ini_string = \trim((string) $this->ini_string) . PHP_EOL;

        $parse_ini_err_msg = 'Cannot parse the contents of ini file ' . $file_realpath;
        $this->ini = WarningHandler::run(
            fn (): array|false => \parse_ini_string($this->ini_string),
            $parse_ini_err_msg);
        if ($this->ini === false) {
            throw new \Exception($parse_ini_err_msg);
        }
    }

    public function get(string $key): ?string
    {
        return $this->ini[$key] ?? null;
    }

    /**
     * @param array<string, mixed> $settings Key/Value pairs of .ini entries.
     */
    public function set(array $settings): bool
    {
        foreach ($settings as $key => $value) {
            $existing_key = \array_key_exists($key, $this->ini);

            $this->ini[$key] = $value;
            $updated_line = $key . '=' . $value;

            if ($existing_key) {
                $lookup_regexp = '/^' . $key . '[\W]*=.*$/m';
                $this->ini_string = \preg_replace($lookup_regexp, $updated_line, $this->ini_string);
            } else {
                $this->ini_string .= $updated_line . PHP_EOL;
            }
        }

        return WarningHandler::run(
            fn (): int|false => \file_put_contents($this->ini_path, $this->ini_string),
            null, false) !== false;
    }

    public function remove(string $key): bool
    {
        if (\array_key_exists($key, $this->ini)) {
            $lookup_regexp = '/^' . $key . '[\W]*=.*$/m';
            $this->ini_string = \preg_replace($lookup_regexp, '', $this->ini_string);
            return WarningHandler::run(
                    fn (): int|false => \file_put_contents($this->ini_path, $this->ini_string),
                    null, false) !== false;
        }

        return true;
    }
}
