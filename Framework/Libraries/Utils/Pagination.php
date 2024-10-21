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

final class Pagination
{
    // this is only correct here in this context
    private static function pagination_compose_url(string $base_url, int $page = null, int $page_entries = null): string
    {
        if (\is_null($page) || \is_null($page_entries)) {
            return $base_url;
        }

        $parsed_url = \parse_url($base_url);
        if (!\is_array($parsed_url)) {
            return '#';
        }

        // initially only add these components in order to easily replace '//' with '/'
        $result = $parsed_url['host'];
        if (isset($parsed_url['path'])) {
            $result .= '/' . $parsed_url['path'];
        }

        if (isset($parsed_url['query'])) {
            \parse_str($parsed_url['query'], $query_array);
        }

        $query_array['page_number'] = $page;
        $query_array['page_entries'] = $page_entries;
        $result .= '?' . \http_build_query($query_array);

        $result = \str_replace('//', '/', $result);
        return $parsed_url['scheme'] . '://' . $result;
    }


    /**
     * @param int $total_entries The total number of pages for this pagination
     * @param int $page_no The number of the page being shown starting from 1
     * @param int $page_entries The maximum number of entries set for all the pages
     * @return array<array<string, mixed>>
     */
    public static function build(int $total_entries, int $page_no, int $page_entries, string $base_url): array
    {
        $pagination = [];
        $total_pages = (int)\ceil($total_entries / $page_entries);

        // * add numbered nav buttons
        if ($page_no > 2) {
            $pagination[] = [
                "symbol" => $page_no - 2,
                "active" => false,
                "disabled" => false,
                "link" => self::pagination_compose_url($base_url, ($page_no - 2), $page_entries),
            ];
        }

        if ($page_no > 1) {
            $pagination[] = [
                "symbol" => $page_no - 1,
                "active" => false,
                "disabled" => false,
                "link" => self::pagination_compose_url($base_url, ($page_no - 1), $page_entries),
            ];
        }

        //      ** active page number
        $pagination[] = [
            "symbol" => $page_no,
            "active" => true,
            "disabled" => false,
            "link" => self::pagination_compose_url($base_url, $page_no, $page_entries),
        ];
        if ($page_no < $total_pages) {
            $pagination[] = [
                "symbol" => $page_no + 1,
                "active" => false,
                "disabled" => false,
                "link" => self::pagination_compose_url($base_url, ($page_no + 1), $page_entries),
            ];
        }

        if ($page_no < $total_pages - 1) {
            $pagination[] = [
                "symbol" => $page_no + 2,
                "active" => false,
                "disabled" => false,
                "link" => self::pagination_compose_url($base_url, ($page_no + 2), $page_entries),
            ];
        }


        // * add single arrows < and >
        // * add double arrows << and >>
        if ($total_pages > 3) {
            // * add forward ...
            if ($page_no - 2 > 1) {
                \array_unshift(
                    $pagination,
                    [
                        "symbol" => "...",
                        "active" => false,
                        "disabled" => true,
                        "link" => "#",
                    ]
                );
            }

            if ($page_no > 1) {
                \array_unshift(
                    $pagination,
                    [
                        "symbol" => "<",
                        "active" => false,
                        "disabled" => false,
                        "link" => self::pagination_compose_url($base_url, ($page_no - 1), $page_entries),
                    ]
                );
            }

            if ($page_no > 2) {
                \array_unshift(
                    $pagination,
                    [
                        "symbol" => "<<",
                        "active" => false,
                        "disabled" => false,
                        "link" => self::pagination_compose_url($base_url, 1, $page_entries),
                    ]
                );
            }

            // * add backward ...
            if ($page_no < $total_pages - 2) {
                $pagination[] = [
                    "symbol" => "...",
                    "active" => false,
                    "disabled" => true,
                    "link" => "#",
                ];
            }

            if ($page_no < $total_pages) {
                $pagination[] = [
                    "symbol" => ">",
                    "active" => false,
                    "disabled" => false,
                    "link" => self::pagination_compose_url($base_url, ($page_no + 1), $page_entries),
                ];
            }

            if ($page_no < $total_pages - 1) {
                $pagination[] = [
                    "symbol" => ">>",
                    "active" => false,
                    "disabled" => false,
                    "link" => self::pagination_compose_url($base_url, $total_pages, $page_entries),
                ];
            }
        }

        return $pagination;
    }
}
