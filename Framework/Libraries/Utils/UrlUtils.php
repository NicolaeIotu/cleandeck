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

final class UrlUtils
{
    public static function baseUrl(string $path = null): string
    {
        $baseURL = $_ENV['cleandeck']['baseURL'];

        if (\is_string($baseURL)) {
            if (\is_string($path)) {
                return $baseURL . '/' . \ltrim($path, '/');
            }
            return $baseURL;
        }
        return 'missing__.env.ini__cleandeck.baseURL';
    }

    public static function getSiteBrand(): string
    {
        $base_url = self::baseUrl();
        $url_host = \parse_url(\trim($base_url), PHP_URL_HOST);

        if (\is_string($url_host)) {
            $site_brand = $url_host;
        } else {
            $a = \explode('://', $base_url);
            if (\count($a) > 1) {
                $site_brand = $a[1];
            } else {
                $site_brand = $base_url;
            }
        }

        return \ucfirst($site_brand);
    }

    public static function urlToPageTitle(string $url): string
    {
        $result = \parse_url($url, PHP_URL_PATH);
        $result = \explode('/', $result);
        $result = $result[\count($result) - 1];
        return \ucwords((string)\preg_replace('/[\-_]/i', ' ', $result));
    }

    public static function url_clean(string $uri = null): string
    {
        $uri ??= \explode('?', self::current_url())[0];
        return \preg_replace('/\/index\.php/i', '', $uri, 1);
    }

    /**
     * @param array<string, mixed>|null $attributes
     */
    public static function anchor(string $uri, string $text, array $attributes = null): string
    {
        $template = '<a href="%s"%s>%s</a>';
        $attributes_string = '';
        if (isset($attributes)) {
            foreach ($attributes as $k => $v) {
                if (\is_string($k)) {
                    $attributes_string .= ' ' . $k . '="' . $v . '"';
                }
            }
        }

        return \sprintf($template, $uri, $attributes_string, $text);
    }

    /**
     * @param array<string, mixed>|null $attributes
     */
    public static function anchor_clean(string $uri, string $text, array $attributes = null): string
    {
        $clean_url = self::baseUrl(self::url_clean($uri));
        return self::anchor($clean_url, $text, $attributes);
    }

    /**
     * @param string $uri
     * @param string $text
     * @param string|null $title
     * @return string
     */
    public static function dropdown_anchor(string $uri, string $text, string $title = null): string
    {
        $template = '<a href="%s" class="dropdown-item nav-link%s px-2" title="%s">%s</a>';
        $is_current_path = $uri === self::current_path();
        return \sprintf($template,
            $is_current_path ? '#' : self::baseUrl($uri),
            $is_current_path ? ' active' : '',
            $title ?? $text,
            $text);
    }

    /**
     * @param string $uri
     * @param array<string, mixed>|null $attributes
     * @param bool $integrity
     * @return string
     */
    public static function link(string $uri, array $attributes = null, bool $integrity = true): string
    {
        return self::html_element('link', $uri, $attributes, $integrity);
    }

    /**
     * @param string $uri
     * @param array<string, mixed>|null $attributes
     * @param bool $integrity
     * @return string
     */
    public static function script(string $uri, array $attributes = null, bool $integrity = true): string
    {
        return self::html_element('script', $uri, $attributes, $integrity);
    }

    /**
     * @param string $tag
     * @param string $uri
     * @param array<string, mixed>|null $attributes
     * @param bool $integrity
     * @return string
     */
    public static function html_element(string $tag, string $uri, array $attributes = null, bool $integrity = true): string
    {
        if (!in_array($tag, ['link', 'script'], true)) {
            return '<span class="p-2 text-bg-danger">Unsupported tag ' . $tag . '</span>';
        }
        $base_url = self::baseUrl();
        if (!\str_starts_with($uri, $base_url)) {
            return '<span class="p-2 text-bg-danger">Invalid ' . $tag . ': ' . $uri . '</span>';
        }
        $resource_relative_path = \explode($base_url, $uri)[1];
        $resource_path = CLEANDECK_PUBLIC_PATH . '/' . ltrim($resource_relative_path, '/');
        if (!\file_exists($resource_path)) {
            return '<span class="p-2 text-bg-danger">No such ' . $tag . ': ' . $uri . '</span>';
        }

        $attributes_string = '';
        if ($integrity) {
            $resource_hash = \hash_file('sha384', $resource_path, true);
            if ($resource_hash) {
                $attributes_string = ' integrity="sha384-' . \base64_encode($resource_hash) . '"';
            } else {
                \syslog(LOG_ERR, 'Failed to calculate the hash of ' . $tag . ' ' . $resource_path);
            }
        }

        if ($tag === 'link') {
            $template = '<' . $tag . ' href="%s"%s />' . PHP_EOL;
        } else {
            // script
            $template = '<' . $tag . ' src="%s"%s></' . $tag . '>' . PHP_EOL;
        }
        if (isset($attributes)) {
            foreach ($attributes as $k => $v) {
                if (\is_string($k)) {
                    $attributes_string .= ' ' . $k . '="' . $v . '"';
                }
            }
        }

        return \sprintf($template, $uri, $attributes_string);
    }

    public static function get_query(): string
    {
        return $_SERVER['QUERY_STRING'];
    }

    public static function url_trim_query(string $url): string
    {
        return explode('?', $url)[0];
    }

    public static function current_url(): string
    {
        $current_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        if (isset($_SERVER['PATH_INFO'])) {
            return $current_url . \rtrim((string)($_SERVER['PATH_INFO']), '/');
        }
        if (isset($_SERVER['REQUEST_URI'])) {
            return $current_url . explode('?', (string)$_SERVER['REQUEST_URI'])[0];
        }
        return $current_url;
    }

    public static function current_path(): string
    {
        return $_SERVER['PATH_INFO'] ??
            isset($_SERVER['REQUEST_URI']) ? self::url_trim_query($_SERVER['REQUEST_URI']) : '/';
    }
}
