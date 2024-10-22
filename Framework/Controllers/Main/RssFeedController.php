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
use Framework\Libraries\CA\CAResponse;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\TimeUtils;
use Framework\Libraries\Utils\UrlUtils;

final class RssFeedController
{
    public function index(): void
    {
        $site_brand = UrlUtils::getSiteBrand();

        $contact_email = \env('cleandeck.CONTACT_EMAIL', 'Missing CONTACT_EMAIL');
        $base_url = (string)\env('cleandeck.baseURL');
        $rss_contact = $contact_email . ' (Contact ' . $base_url . ')';

        // The entries in the $query_array will be used by CMD-Auth when building the RSS.

        // Entries described below will be included in the <channel></channel> tag of the resulting xml:
        //    - c_.. -> standard XML element
        //    - cx_.. -> self-closing XML element
        $query_array = [
            'c_title' => $site_brand,
            'c_link' => $base_url,
            'c_description' => 'Awesome software',
            'c_generator' => 'CMD-Auth',
            'c_language' => 'en',
            'c_copyright' => 'Copyright ' . TimeUtils::getYearNow() . ', ' . $site_brand,
            'c_category' => 'Software',
            'c_ttl' => '180',
            'c_managingEditor' => $rss_contact,
            'c_webMaster' => $rss_contact,
            'cx_atom:link;href' => $base_url . '/rss.xml',
            'cx_atom:link;rel' => 'self',
            'cx_atom:link;type' => 'application/rss+xml',
        ];

        // A single namespace can be declared
        $query_array['namespace'] = 'atom=http://www.w3.org/2005/Atom';

        // Optionally add channel image URL. CMD-Auth will build channel image tag using this URL.
        $query_array['image_url'] = $base_url . '/template/core/main/images/cmd-auth-rss.png';

        // VERY IMPORTANT! Add the base_url!
        $query_array['base_url'] = UrlUtils::baseUrl();

        // Set output type to 'xml' (default)
        // You may also use 'json' in order to adjust data before converting to xml.
        $query_array['output'] = 'xml';
        // Set url type to 'title'
        // You may also use 'id'
        $query_array['url_type'] = 'title';


        if (isset($_GET['tags'])) {
            $query_array['tags'] = $_GET['tags'];
        }

        if (isset($_GET['content'])) {
            $query_array['content'] = $_GET['content'];
        }

        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setQuery($query_array)
            ->exec('GET', '/rss');

        if ($caResponse->hasError()) {
            $this->sendRssError($caResponse);
        } else {
            echo $caResponse->getBody();
        }
    }

    private function sendRssError(CAResponse $caResponse): void
    {
        $message = '<?xml version="1.0" encoding="utf-8"?>';
        $message .= '<error' . $caResponse->getStatusCode() . '>';
        $message .= $caResponse->getErrorMessage();
        $message .= '</error' . $caResponse->getStatusCode() . '>';
        HttpResponse::send($caResponse->getStatusCode(), $message);
    }
}
