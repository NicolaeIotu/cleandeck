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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UrlUtils::class)]
final class UrlUtilsTests extends TestCase
{
    public function testBaseUrl(): void
    {
        $baseUrl = UrlUtils::baseUrl();
        $this->assertStringContainsString('https://w-e-b-s-i-t-e.c-o-m', $baseUrl);

        $baseUrlEndpoint = UrlUtils::baseUrl('endpoint');
        $this->assertStringContainsString('endpoint', $baseUrlEndpoint);

        $_ENV['cleandeck']['baseURL'] = null;
        $this->assertStringContainsString('missing__', UrlUtils::baseUrl());
        $_ENV['cleandeck']['baseURL'] = $baseUrl;
    }

    public function testGetSiteBrand(): void
    {
        $baseUrl = UrlUtils::baseUrl();

        $siteBrand = UrlUtils::getSiteBrand();
        $this->assertEquals('W-e-b-s-i-t-e.c-o-m', $siteBrand);

        $_ENV['cleandeck']['baseURL'] = ';;1+1://site';
        $siteBrand = UrlUtils::getSiteBrand();
        $this->assertEquals('Site', $siteBrand);

        $_ENV['cleandeck']['baseURL'] = ';;1+1//site';
        $siteBrand = UrlUtils::getSiteBrand();
        $this->assertEquals(';;1+1//site', $siteBrand);
        $_ENV['cleandeck']['baseURL'] = $baseUrl;
    }

    public function testUrlToPageTitle(): void
    {
        $pageTitle = UrlUtils::urlToPageTitle('contact-details');
        $this->assertEquals('Contact Details', $pageTitle);
    }

    public function testUrl_clean(): void
    {
        $cleanUrl = UrlUtils::url_clean('contact-details/index.php');
        $this->assertEquals('contact-details', $cleanUrl);
    }

    public function testAnchor(): void
    {
        $anchor = UrlUtils::anchor('#contact', 'Contact', ['title' => 'Contact']);
        $this->assertEquals('<a href="#contact" title="Contact">Contact</a>', $anchor);
    }

    public function testAnchor_clean(): void
    {
        $anchor_clean = UrlUtils::anchor_clean('contact/index.php', 'Contact', ['title' => 'Contact']);
        $this->assertEquals('<a href="https://w-e-b-s-i-t-e.c-o-m/contact" title="Contact">Contact</a>', $anchor_clean);
    }

    public function testDropdown_anchor(): void
    {
        $dropdown_anchor = UrlUtils::dropdown_anchor('contact', 'Contact', 'Contact');
        $this->assertEquals(
            '<a href="https://w-e-b-s-i-t-e.c-o-m/contact" class="dropdown-item nav-link px-2" title="Contact">Contact</a>', $dropdown_anchor);
    }

    public function testLink(): void
    {
        $baseUrl = UrlUtils::baseUrl();
        $_ENV['cleandeck']['baseURL'] = 'https://example.com';
        $link = UrlUtils::link(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/css/main.css'),
            ['type' => 'text/css', 'rel' => 'stylesheet'], false);
        $this->assertStringContainsString(
            '<link href="https://example.com/template/core/main/css/main.css" type="text/css" rel="stylesheet" />',
            $link);
        $_ENV['cleandeck']['baseURL'] = $baseUrl;
    }

    public function testScript(): void
    {
        $baseUrl = UrlUtils::baseUrl();
        $_ENV['cleandeck']['baseURL'] = 'https://example.com';
        $script = UrlUtils::script(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/captcha.js'),
            ['defer' => 'true', 'referrerpolicy' => 'no-referrer'], false);
        $this->assertStringContainsString(
            '<script src="https://example.com/template/core/main/js/captcha.js" defer="true" referrerpolicy="no-referrer"></script>',
            $script);
        $_ENV['cleandeck']['baseURL'] = $baseUrl;
    }

    public function testHtml_element(): void
    {
        $baseUrl = UrlUtils::baseUrl();

        $html_element = UrlUtils::html_element('hr', 'contact', null, false);
        $this->assertStringContainsString('Unsupported tag', $html_element);

        $html_element = UrlUtils::html_element('link', 'contact', null, false);
        $this->assertStringContainsString('Invalid link', $html_element);

        $html_element = UrlUtils::html_element('link',
            UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/missing.css'), null, false);
        $this->assertStringContainsString('No such link', $html_element);

        $_ENV['cleandeck']['baseURL'] = 'https://example.com';

        $html_element = UrlUtils::html_element('script',
            UrlUtils::baseUrl(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/captcha.js')),
        null, true);
        $this->assertStringContainsString(
            UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/captcha.js'),
            $html_element);

        $html_element = UrlUtils::html_element('script',
            UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/captcha.js'));
        $this->assertStringContainsString(
            'integrity="sha384-',
            $html_element);

        $_ENV['cleandeck']['baseURL'] = $baseUrl;
    }

    public function testGet_query(): void
    {
        $_SERVER['QUERY_STRING'] = 'test';
        $query = UrlUtils::get_query();
        $this->assertEquals('test', $query);
    }

    public function testUrl_trim_query(): void
    {
        $query = UrlUtils::url_trim_query('https://example.com?q=tree');
        $this->assertEquals('https://example.com', $query);
    }

    public function testCurrent_url(): void
    {
        $_SERVER['REQUEST_SCHEME'] = 'https';
        $_SERVER['HTTP_HOST'] = 'example.com';

        $_SERVER['PATH_INFO'] = '/path';
        $current_url = UrlUtils::current_url();
        $this->assertEquals('https://example.com/path', $current_url);

        unset($_SERVER['PATH_INFO']);
        $_SERVER['REQUEST_URI'] = '/path?q=tree';
        $current_url = UrlUtils::current_url();
        $this->assertEquals('https://example.com/path', $current_url);

        unset($_SERVER['REQUEST_URI']);
        $current_url = UrlUtils::current_url();
        $this->assertEquals('https://example.com', $current_url);
    }
}
