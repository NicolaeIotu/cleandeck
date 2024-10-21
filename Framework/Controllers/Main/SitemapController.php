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

namespace Framework\Controllers\Main;

use Framework\Libraries\CA\CARequest;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\SEO\SitemapUtils;
use Framework\Libraries\Utils\UrlUtils;
use DOMDocument;

final class SitemapController
{
    private function getCMDAuthSitemap(): bool|DOMDocument
    {
        $sitemap_fwd_url = '/sitemap';

        $query_array['url_type'] = 'title';
        $query_array = [
            'output' => 'xml',
            'base_url' => UrlUtils::baseUrl(),
            'url_type' => 'title',
        ];

        // CA sitemap
        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setQuery($query_array)
            ->exec('GET', $sitemap_fwd_url);

        if ($caResponse->hasError()) {
            \error_log("Cannot retrieve CMD-Auth's sitemap: " . $caResponse->getErrorMessage());
            return false;
        }

        $domDocument = new DOMDocument();
        if ($domDocument->loadXML($caResponse->getBody())) {
            return $domDocument;
        }

        \error_log("Cannot format CMD-Auth's sitemap");
        return false;
    }

    private function getCustomSitemap(): bool|DOMDocument
    {
        $custom_sitemap_xml_path = CLEANDECK_WRITE_PATH . '/sitemap/custom-sitemap.xml';
        $day_seconds = 24 * 60 * 60;

        if (\file_exists($custom_sitemap_xml_path)) {
            if (\filemtime($custom_sitemap_xml_path) + $day_seconds > \time()) {
                $domDocument = new DOMDocument();
                if ($domDocument->load($custom_sitemap_xml_path)) {
                    return $domDocument;
                }
                \error_log('Cannot format the custom sitemap');
                return false;
            }
            // recreate the file every 1 day
        }

        try {
            return SitemapUtils::generateCustomSitemapFile($custom_sitemap_xml_path);
        } catch (\Exception $exception) {
            \error_log('Cannot generate a custom sitemap: ' . $exception->getMessage());
            return false;
        }
    }

    /**
     * @param false|string $response
     * @return void
     */
    private function sendResponse(bool|string $response): void
    {
        if (\is_string($response)) {
            echo $response;
        } else {
            HttpResponse::send(500, 'Invalid sitemap');
        }
    }

    public function index(): void
    {
        // CMD-Auth generated sitemap which covers articles and FAQs
        $ca_sitemap_xml = $this->getCMDAuthSitemap();
        // own sitemap
        $custom_sitemap_xml = $this->getCustomSitemap();

        if ($ca_sitemap_xml === false) {
            if ($custom_sitemap_xml === false) {
                HttpResponse::send(500, 'Failed to generate a valid sitemap');
                return;
            }

            $this->sendResponse($custom_sitemap_xml->saveXML());
            return;
        }

        if (is_bool($custom_sitemap_xml)) {
            $this->sendResponse($ca_sitemap_xml->saveXML());
            return;
        }

        $merged_sitemap_xml = $ca_sitemap_xml;

        try {
            $custom_sitemap_urlset_elements = $custom_sitemap_xml->getElementsByTagName('urlset');
            if ($custom_sitemap_urlset_elements->count() > 0) {
                $custom_urlset_dom_element = $custom_sitemap_urlset_elements->item(0);

                $merged_sitemap_urlset_elements = $merged_sitemap_xml->getElementsByTagName('urlset');
                $merged_urlset_dom_element = $merged_sitemap_urlset_elements->item(0);

                foreach ($custom_urlset_dom_element->getElementsByTagName('url') as $domNodeList) {
                    try {
                        $merged_url_dom_element = $merged_sitemap_xml->createElement('url');
                        foreach ($domNodeList->childNodes as $child_elem) {
                            try {
                                $merged_child_elem = $merged_sitemap_xml->createElement($child_elem->nodeName,
                                    $child_elem->nodeName === 'loc' ?
                                        \rtrim(UrlUtils::baseUrl($child_elem->textContent), '/') : $child_elem->textContent);
                                $merged_url_dom_element->appendChild($merged_child_elem);
                            } catch (\Exception $e) {
                                \error_log('Sitemap cannot add tag ' . $child_elem->nodeName .
                                    ', with content ' . $child_elem->textContent . ': ' . $e->getMessage());
                            }
                        }

                        $merged_urlset_dom_element->appendChild($merged_url_dom_element);
                    } catch (\Exception $e) {
                        \error_log('Sitemap cannot add tag url: ' . $e->getMessage());
                    }
                }
            }
        } catch (\Error $e) {
            \error_log('Sitemap error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \error_log('Sitemap exception: ' . $e->getMessage());
        }

        $this->sendResponse($merged_sitemap_xml->saveXML());
    }
}
