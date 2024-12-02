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

use DOMDocument;

final class ContentUtils
{
    public static function pathJoin(string ...$path_elements): ?string
    {
        $path = '';
        $is_first = true;
        foreach ($path_elements as $path_element) {
            if ($is_first) {
                $is_first = false;
            } else {
                $path .= '/';
            }

            $path .= $path_element;
        }

        return \preg_replace(
            ['/[\/]+/', '/(http[s]?:)\//'],
            ['/', '$1//'],
            $path
        );
    }


    /**
     * @param string[] $static_file_names An array of file names to look for.
     * @param string $src_value The string to be modified.
     */
    public static function adjustStaticContentSource(
        array  $static_file_names,
        string $src_value,
        string $static_content_url
    ): bool|string|null {
        foreach ($static_file_names as $static_file_name) {
            $pm = \preg_match('/[\S]*' . $static_file_name . '/', $src_value);
            if ($pm === 1) {
                return self::pathJoin($static_content_url, $static_file_name);
            }
        }

        return false;
    }

    /**
     * @param string[] $static_file_names An array containing the names of static files.
     */
    public static function adjustSrc(
        DOMDocument $domDocument,
        array $static_file_names,
        string $base_url,
        string $static_content_url
    ): void {
        $map_tag_attr = [
            'img' => 'src',
            'script' => 'src',
            'a' => 'href',
            'link' => 'href',
        ];

        foreach ($map_tag_attr as $tag => $attribute) {
            $arr_elements = $domDocument->getElementsByTagName($tag);

            foreach ($arr_elements as $element) {
                if (!$element->hasAttribute($attribute)) {
                    // do not act on elements which are missing target attribute i.e. empty <script>
                    continue;
                }

                // We are only acting on elements which do not have the attribute 'data-preserve-source'.
                // Use 'data-preserve-source' attribute to preserve sources.
                if (!$element->hasAttribute('data-preserve-source')) {
                    $src_value = $element->getAttribute($attribute);
                    $adjust_static_content_result =
                        self::adjustStaticContentSource($static_file_names, $src_value, $static_content_url);

                    if ($adjust_static_content_result === false) {
                        // if it is not static content, then just prepend the $base_url
                        $element->setAttribute(
                            $attribute,
                            self::pathJoin($base_url, $src_value)
                        );
                    } else {
                        $element->setAttribute($attribute, $adjust_static_content_result);
                    }
                }
            }


            // We are using improved loading for images, so for tag 'img' additional preparations are required.
            // See file 'images-autoload.js'.
            if ($tag === 'img') {
                foreach ($arr_elements as $arr_element) {
                    if ($arr_element->hasAttribute('src')) {
                        $arr_element->setAttribute('data-src', $arr_element->getAttribute('src'));
                        $arr_element->removeAttribute('src');
                    }
                }
            }
        }
    }

    /**
     * @param string[] $static_file_names
     */
    public static function adjustMainContent(
        string $content,
        string $format,
        array  $static_file_names,
        string $base_url,
        string $static_content_url
    ): string {

        if ($format !== 'html' && $format !== 'xml') {
            $result = \nl2br($content);
        } else {
            $result = $content;
        }

        // Important! Make sure the content can fit gracefully in page
        // no matter the errors done by the administrator.
        $domDocument = new DOMDocument();
        // LIBXML_NOERROR -> allows use of html5 tags
        $domDocument->loadHTML($result, LIBXML_NONET | LIBXML_NOENT | LIBXML_NOERROR);

        self::adjustSrc($domDocument, $static_file_names, $base_url, $static_content_url);

        $raw_html = $domDocument->saveHTML();
        // extract the contents of the body
        $result = \preg_replace(
            [
                '/^.*?<body>/is',
                '/<\/body>.*?$/is',
            ],
            '',
            $raw_html
        );

        return $result;
    }
}
