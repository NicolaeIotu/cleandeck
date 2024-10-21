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


use Framework\Libraries\Utils\ErrorUtils;

if (!function_exists('env')) {
    /**
     * @param string|array<string, mixed> $key
     * @param mixed|null $default
     * @return mixed
     */
    function env(string|array $key, mixed $default = null): mixed
    {
        if (\is_string($key)) {
            $path_components = \explode('.', $key);
        } else {
            $path_components = $key;
        }
        $result = null;

        foreach ($path_components as $path_component) {
            if (isset($result)) {
                if (isset($result[$path_component])) {
                    $result = $result[$path_component];
                } else {
                    return $default;
                }
            } else {
                if (isset($_ENV[$path_component])) {
                    $result = $_ENV[$path_component];
                } else {
                    return $default;
                }
            }
        }

        return $result;
    }
}

if (!function_exists('view')) {
    /**
     * @param string[] $view_files Absolute paths
     * @param array<mixed> $data
     * @return string
     */
    function view(array $view_files, array $data = []): string
    {
        // add data to views
        foreach ($data as $key => $value) {
            // validate the name of the variable
            if (\preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $key) === 1) {
                $$key = $value;
            }
        }

        \ob_start();
        try {
            // Important!
            // Some files such as php components (i.e. csrf.php) can be required multiple times on the same page
            //  so don't use 'require_once'.
            foreach ($view_files as $view_file) {
                require $view_file;
            }
        } catch (Error|Exception $e) {
            \ob_end_clean();
            echo ErrorUtils::prettify($e);
        }
        $view = \ob_get_contents();
        \ob_end_clean();

        return $view;
    }
}

if (!function_exists('view_user')) {
    /**
     * @param string $view_file Path relative to Application/Instance/Views/env('cleandeck.template').
     *  Do not include the template name. Do not include file(s) extension '.php'.
     * @param array<string, mixed> $data
     * @return string
     */
    function view_user(string $view_file, array $data = []): string
    {
        return view(
            [CLEANDECK_USER_VIEWS_PATH . '/' .
                \env('cleandeck.template', 'core') .
                '/' .
                \ltrim($view_file, '/') . '.php'],
            $data);
    }
}

if (!function_exists('view_main')) {
    /**
     * @param string $view_file Path relative to Framework/Views/env('cleandeck.template')/main.
     *  Do not include the template name. Do not include file extension '.php'.
     * @param array<string, mixed> $data
     * @return string
     */
    function view_main(string $view_file, array $data = []): string
    {
        return view(
            [CLEANDECK_FRAMEWORK_VIEWS_PATH . '/' .
                \env('cleandeck.template', 'core') .
                '/main/' .
                \ltrim($view_file, '/') . '.php'],
            $data);
    }
}

if (!function_exists('view_addon')) {
    /**
     * @param string $view_file Path relative to Framework/Views/env('cleandeck.template')/addon.
     *  Do not include the template name or the addon name. Do not include file(s) extension '.php'.
     * @param string $addon
     * @param array<string, mixed> $data
     * @return string
     */
    function view_addon(string $view_file, string $addon, array $data = []): string
    {
        return view(
            [CLEANDECK_FRAMEWORK_VIEWS_PATH . '/' .
                \env('cleandeck.template', 'core') .
                '/addon/' . $addon . '/' .
                \ltrim($view_file, '/') . '.php'],
            $data);
    }
}
