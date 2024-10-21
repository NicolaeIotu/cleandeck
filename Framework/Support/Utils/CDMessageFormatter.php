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

final class CDMessageFormatter
{
    public const LINE_LENGTH = 60;

    private const SIDE_PADDING = '  ';

    private readonly string $on_success;

    private readonly string $on_fail;

    public function __construct(string $header, string $on_success, string $on_fail)
    {
        $this->header($header);

        $this->on_success = $on_success;
        $this->on_fail = $on_fail;
    }

    private function contentAsLines(string $message, bool $center = false): string
    {
        $line_available_length = self::LINE_LENGTH - 4;
        $content_arr = \explode(PHP_EOL, \wordwrap(\ltrim($message), $line_available_length));

        $result = '';
        foreach ($content_arr as $line_content) {
            if ($result !== '') {
                $result .= PHP_EOL;
            }

            if (\strlen(\trim($line_content)) > 0) {
                if ($center) {
                    $space_length = $line_available_length - \strlen($line_content);
                    $space_prefix = \str_repeat(' ', (int)\ceil($space_length / 2));
                    $space_suffix = \str_repeat(' ', (int)\floor($space_length / 2));

                    $result .= self::SIDE_PADDING . $space_prefix . $line_content . $space_suffix .
                        self::SIDE_PADDING;
                } else {
                    $result .= self::SIDE_PADDING . $line_content;
                }
            }
        }

        return $result;
    }

    public function header(string $message): void
    {
        \printf("\033[47;1;33m%s\033[0m\n", \str_repeat(' ', self::LINE_LENGTH));
        \printf("\033[47;1;32m%s\033[0m\n", $this->contentAsLines(\strtoupper($message), true));
        \printf("\033[47;1;33m%s\033[0m\n\n", \str_repeat(' ', self::LINE_LENGTH));
    }

    /**
     * @param string $route
     * @param array<string, mixed> $route_details
     * @return void
     */
    public function route_details(string $method, string $route, array $route_details): void
    {
        $cp = \explode('\\', (string)$route_details['controller']);
        $class = \array_pop($cp);
        \printf($method . " \033[1;32m%s \033[0m - %s \033[0;37m[%s]\033[0m\n",
            $route,
            $class . '->' . $route_details['method'],
            \implode('\\', $cp),
        );
    }

    public function subsection(string $message): void
    {
        \printf("\033[47;1;30m%s  \033[0m", $this->contentAsLines(\strtoupper($message)));
        echo PHP_EOL;
    }

    public function bold(string $message): void
    {
        \printf("\033[1m%s\033[0m", $this->contentAsLines($message));
        echo PHP_EOL;
    }

    public function code(string $message): void
    {
        \printf("%s\033[3;7m%s\033[0m",
            self::SIDE_PADDING . self::SIDE_PADDING, $message);
        echo PHP_EOL;
    }

    public function content(string $message): void
    {
        echo $this->contentAsLines($message) . PHP_EOL;
    }

    public function critical(string $message): void
    {
        \printf("\033[1;31m%s\033[0m", $this->contentAsLines($message));
        echo PHP_EOL;
    }

    public function error(string $message): void
    {
        \printf("\033[1;31m%s\033[0m", $this->contentAsLines($message));
        echo PHP_EOL;
    }

    public function fail(string $message = null): void
    {
        \printf("\n\033[47;5;31m%s\033[0m",
            $this->contentAsLines($message ?? $this->on_fail, true));
        echo PHP_EOL;
    }

    public function important(string $message): void
    {
        \printf("\033[1;36m%s\033[0m", $this->contentAsLines($message));
        echo PHP_EOL;
    }

    public function prompt(string $message): void
    {
        \printf("\033[1m%s\033[0m", $this->contentAsLines($message));
        echo PHP_EOL;
    }

    public function remark(string $message): void
    {
        \printf("\033[3;37m%s\033[0m", $this->contentAsLines($message));
        echo PHP_EOL;
    }

    public function success(string $message = null): void
    {
        \printf("\n\033[1;47;7;32m%s\033[0m",
            $this->contentAsLines($message ?? $this->on_success, true));
        echo PHP_EOL;
    }

    public function warn(string $message): void
    {
        \printf("\033[0;33m%s\033[0m", $this->contentAsLines($message));
        echo PHP_EOL;
    }

    public function nl(): void
    {

        echo PHP_EOL;
    }
}
