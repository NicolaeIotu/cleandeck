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

/*
 * Code generated partly by qwen2.5-coder AI.
 */

namespace Framework\Libraries\Utils;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentUtils::class)]
final class ContentUtilsTests extends TestCase
{
    public function testPathJoin(): void
    {
        $this->assertEquals('path/to/file', ContentUtils::pathJoin('path', 'to', 'file'));
        $this->assertEquals('/path/to/file', ContentUtils::pathJoin('/', 'path', 'to', 'file'));
        $this->assertEquals('http://example.com/path/to/file', ContentUtils::pathJoin('http://example.com', 'path', 'to', 'file'));
        $this->assertEquals('https://example.com/path/to/file', ContentUtils::pathJoin('https://example.com', 'path', 'to', 'file'));
        $this->assertEquals('', ContentUtils::pathJoin());
    }

    public function testAdjustStaticContentSource(): void
    {
        $this->assertEquals('http://example.com/static/file1.js',
            ContentUtils::adjustStaticContentSource(['file1.js'], 'script src="file1.js"',
                'http://example.com/static/'));
        $this->assertFalse(ContentUtils::adjustStaticContentSource(['file2.js'], 'script src="file3.js"', 'http://example.com/static/'));
    }

    public function testAdjustSrc(): void
    {
        $domDocument = new DOMDocument();
        $html = '<img src="image.jpg"><script src="script.js"></script><a href="link.html"></a>';
        $domDocument->loadHTML($html);

        ContentUtils::adjustSrc($domDocument, ['image.jpg', 'script.js'], 'http://example.com/', 'http://example.com/static/');
        $img_item = $domDocument->getElementsByTagName('img')->item(0);
        $this->assertEquals('data-src', $img_item->attributes->item(0)->nodeName);
        $this->assertEquals('http://example.com/static/image.jpg', $img_item->attributes->item(0)->nodeValue);
        $img_item = $domDocument->getElementsByTagName('script')->item(0);
        $this->assertEquals('src', $img_item->attributes->item(0)->nodeName);
        $this->assertEquals('http://example.com/static/script.js', $img_item->attributes->item(0)->nodeValue);

        // Test with 'data-preserve-source' attribute
        $html = '<img src="image.jpg" data-preserve-source/>';
        $domDocument->loadHTML($html);
        ContentUtils::adjustSrc($domDocument, ['image.jpg'], 'http://example.com/', 'http://example.com/static/');
        $img_item = $domDocument->getElementsByTagName('img')->item(0);
        $this->assertEquals('image.jpg', $img_item->attributes->item(1)->nodeValue);
        $this->assertEquals('', $img_item->attributes->item(0)->nodeValue);

        // Test with missing required attributes
        $html = '<img alt="Image Alt" data-preserve-source/>';
        $domDocument->loadHTML($html);
        ContentUtils::adjustSrc($domDocument, ['image.jpg'], 'http://example.com/', 'http://example.com/static/');
        $img_item = $domDocument->getElementsByTagName('img')->item(0);
        $this->assertEquals('Image Alt', $img_item->attributes->item(0)->nodeValue);
    }

    public function testAdjustMainContent(): void
    {
        $content = '<html><head></head><body><p>Hello, World!</p></body></html>';
        $staticFileNames = ['style.css', 'script.js'];
        $baseUrl = 'http://example.com/';
        $staticContentUrl = 'http://example.com/static/';

        $result = ContentUtils::adjustMainContent($content, 'html', $staticFileNames, $baseUrl, $staticContentUrl);
        $this->assertStringStartsWith('<p>Hello, World!</p>', $result);

        // Test with non-HTML content
        $content = "Hello, World!\n\nThis is a test.";
        $result = ContentUtils::adjustMainContent($content, 'txt', $staticFileNames, $baseUrl, $staticContentUrl);
        $this->assertStringStartsWith("<p>Hello, World!", $result);

        // Test with script tag
        $html = '<html><head></head><body><p>Hello, World! <script src="script.js"></script></p></body></html>';
        $content = ContentUtils::adjustMainContent($html, 'html', $staticFileNames, $baseUrl, $staticContentUrl);
        $this->assertStringStartsWith('<p>Hello, World! <script src="http://example.com/static/script.js"></script></p>', $content);

        // Test with link tag
        $html = '<html><head></head><body><p>Hello, <link href="style.css">World!</p></body></html>';
        $content = ContentUtils::adjustMainContent($html, 'html', $staticFileNames, $baseUrl, $staticContentUrl);
        $this->assertStringStartsWith('<p>Hello, <link href="http://example.com/static/style.css">World!</p>', $content);

        // Test with empty content
        $content = '<html></html>';
        $result = ContentUtils::adjustMainContent($content, 'html', $staticFileNames, $baseUrl, $staticContentUrl);
        $this->assertStringContainsString('<html></html>', $result);

        // Test with body tag only
        $html = '<body><p>Hello, World!</p></body>';
        $content = ContentUtils::adjustMainContent($html, 'html', $staticFileNames, $baseUrl, $staticContentUrl);
        $this->assertStringStartsWith('<p>Hello, World!</p>', $content);
    }
}
