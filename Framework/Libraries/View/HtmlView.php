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

namespace Framework\Libraries\View;

class HtmlView
{
    protected string $html_view;

    /**
     * Usage: <code>echo new HtmlView('page-content/home', true, $data);</code>
     * @param string $content_file_relative_path Do not include the template name.
     *  Do not include file extension '.php'.<br>
     *  If <strong>$is_framework_file</strong> is <em>true</em>, then the path is relative to
     *   <em>"Framework/Views/env('cleandeck.template')"</em>.<br>
     *  If <strong>$is_framework_file</strong> is <em>false</em>, then the path is relative to
     *   <em>"Application/Instance/Views/env('cleandeck.template')"</em>.
     * @param bool $is_framework_file
     * @param array<string, mixed> $data
     * @throws \Exception
     */
    public function __construct(string $content_file_relative_path,
                                bool   $is_framework_file = true,
                                array  $data = [])
    {
        $html_view_structure = \env('cleandeck.html_view_structure');
        if (!\is_array($html_view_structure) ||
            !isset($html_view_structure['header'], $html_view_structure['footer'])) {
            throw new \Exception('Invalid html_view_structure (see .env.ini).');
        }

        $view_files[] = CLEANDECK_ROOT_PATH . '/' . \ltrim((string)$html_view_structure['header'], '/');
        if ($is_framework_file) {
            $view_files[] = CLEANDECK_FRAMEWORK_VIEWS_PATH . '/' .
                \env('cleandeck.template', 'core') . '/' .
                \ltrim($content_file_relative_path, '/') . '.php';
        } else {
            $view_files[] = CLEANDECK_USER_VIEWS_PATH . '/' .
                \env('cleandeck.template', 'core') . '/' .
                \ltrim($content_file_relative_path, '/') . '.php';
        }
        $view_files[] = CLEANDECK_ROOT_PATH . '/' . \ltrim((string)$html_view_structure['footer'], '/');

        $this->html_view = \view($view_files, $data);
    }

    public function __toString(): string
    {
        return $this->html_view;
    }
}
