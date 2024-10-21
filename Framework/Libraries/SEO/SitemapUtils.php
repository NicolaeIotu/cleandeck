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

namespace Framework\Libraries\SEO;

use Framework\Libraries\Utils\WarningHandler;
use Framework\Support\Scripts\ActiveRoutes;
use DOMDocument;

final class SitemapUtils
{
    /**
     * @throws \Exception
     */
    public static function generateCustomSitemapFile(string $custom_sitemap_xml_path): DOMDocument
    {
        $domDocument = new DOMDocument('1.0', 'utf-8');
        $domElement = $domDocument->createElement('urlset');
        $domElement->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $domDocument->appendChild($domElement);

        \defined('CLEANDECK_LIST_ROUTES') || \define('CLEANDECK_LIST_ROUTES', 1);
        $active_routes = ActiveRoutes::list('get');

        if (\is_array($active_routes['GET'])) {
            foreach ($active_routes['GET'] as $route_uri => $route_details) {
                if (isset($route_details['sitemap']) && \is_array($route_details['sitemap'])) {
                    if (!isset($route_details['sitemap']['changefreq'], $route_details['sitemap']['priority'])) {
                        continue;
                    }

                    $url_elem = $domDocument->createElement('url');
                    $domElement->appendChild($url_elem);

                    $loc_elem = $domDocument->createElement('loc', $route_uri);
                    $url_elem->appendChild($loc_elem);

                    foreach ($route_details['sitemap'] as $seo_key => $seo_value) {
                        $elem = $domDocument->createElement($seo_key, $seo_value);
                        $url_elem->appendChild($elem);
                    }
                }
            }
        }

        $directory_custom_sitemap_xml_path = \dirname($custom_sitemap_xml_path);
        if (!\file_exists($directory_custom_sitemap_xml_path)) {
            $base_err_msg = 'Cannot create custom sitemap directory';
            if (!WarningHandler::run(
                static fn (): bool => \mkdir($directory_custom_sitemap_xml_path, 0o775, true),
                $base_err_msg)) {
                throw new \Exception($base_err_msg);
            }
        }

        $domDocument->save($custom_sitemap_xml_path);

        return $domDocument;
    }
}
