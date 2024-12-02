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

#[CoversClass(ImageResizer::class)]
final class ImageResizerTests extends TestCase
{
    private ?ImageResizer $imageResizer;

    private ?\GdImage $testSourceImage;

    private ?\GdImage $testSourceImage1x1;

    private ?string $testSourceImageGifPath;

    private ?string $testSourceImageGif1x1Path;

    private ?string $testSourceImageJpgPath;

    private ?string $testSourceImagePngPath;

    private ?string $testSourceImageWebpPath;

    private ?string $testSourceImageFakePngPath;

    private ?string $testSourceTextFilePath;

    protected function setUp(): void
    {
        if (!isset($this->imageResizer)) {
            $this->imageResizer = new ImageResizer();
            $this->createTestFiles();
        }
    }

    protected function tearDown(): void
    {
        $this->destroyTestFiles();
    }

    private function createTestFiles(): void
    {
        $this->testSourceImage = \imagecreatetruecolor(100, 50);
        \imagesetpixel($this->testSourceImage, 50, 25, 255);

        $this->testSourceImageGifPath = CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/cleandeck-test-image.gif';
        \imagegif($this->testSourceImage, $this->testSourceImageGifPath);
        $this->testSourceImageFakePngPath = CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/cleandeck-test-image-fake-png.png';
        \copy($this->testSourceImageGifPath, $this->testSourceImageFakePngPath);

        $this->testSourceImagePngPath = CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/cleandeck-test-image.png';
        \imagepng($this->testSourceImage, $this->testSourceImagePngPath);

        $this->testSourceImageJpgPath = CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/cleandeck-test-image.jpg';
        \imagejpeg($this->testSourceImage, $this->testSourceImageJpgPath);

        $this->testSourceImageWebpPath = CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/cleandeck-test-image.webp';
        \imagewebp($this->testSourceImage, $this->testSourceImageWebpPath);

        $this->testSourceTextFilePath = CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/cleandeck-test-text.txt';
        \file_put_contents($this->testSourceTextFilePath, 'text file contents');

        $this->testSourceImage1x1 = \imagecreatetruecolor(1, 1);
        $this->testSourceImageGif1x1Path = CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/cleandeck-test-image-1x1.gif';
        \imagegif($this->testSourceImage1x1, $this->testSourceImageGif1x1Path);
    }

    private function destroyTestFiles(): void
    {
        \imagedestroy($this->testSourceImage);
        \imagedestroy($this->testSourceImage1x1);
        FileSystemUtils::emptydir(CLEANDECK_TESTS_PATH . '/_support-Framework/tmp');
        \touch(CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/.gitkeep');
    }

    public function testSupportedExtensions(): void
    {
        $extensions = ImageResizer::SUPPORTED_EXTENSIONS;
        $this->assertIsArray($extensions);
        $this->assertContains('gif', $extensions);
        $this->assertContains('jpg', $extensions);
        $this->assertContains('jpeg', $extensions);
        $this->assertContains('png', $extensions);
    }

    public function testGDCanCreate(): void
    {
        $this->assertIsBool($this->imageResizer->GDCanCreateGif());
        $this->assertIsBool($this->imageResizer->GDCanCreateJpg());
        $this->assertIsBool($this->imageResizer->GDCanCreatePng());
    }

    public function testGetImageExtension(): void
    {
        // valid image file
        $extension = ImageResizer::getImageExtension($this->testSourceImageGifPath);
        $this->assertEquals('gif', $extension);

        // missing file
        $file = CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/missing-file.jpg';
        $extension = @ImageResizer::getImageExtension($file);
        $this->assertFalse($extension);

        // unsupported extension
        $extension = ImageResizer::getImageExtension($this->testSourceTextFilePath);
        $this->assertFalse($extension);

        // jpg-jpeg
        $extension = ImageResizer::getImageExtension($this->testSourceImageJpgPath);
        $this->assertEquals('jpg', $extension);

        // png-gif
        $extension = ImageResizer::getImageExtension($this->testSourceImageFakePngPath);
        $this->assertFalse($extension);

    }

    public function testRunInvalidSource(): void
    {
        $file = CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/missing-file.jpg';
        $this->expectException(\InvalidArgumentException::class);
        $this->imageResizer->run($file,
            CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/scaled.jpg',
            50, 50);
    }
    public function testRunInvalidDestination(): void
    {
        $dir = CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/missing/directory';
        $this->expectException(\InvalidArgumentException::class);
        $this->imageResizer->run($this->testSourceImageGifPath,
            $dir, 50, 50);
    }
    public function testRunInvalidWidth(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->imageResizer->run($this->testSourceImageGifPath,
            CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/scaled.gif',
            0, 50);
    }
    public function testRunInvalidHeight(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->imageResizer->run($this->testSourceImageGifPath,
            CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/scaled.gif',
            50, 0);
    }
    public function testRunInvalidImageExtension(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->imageResizer->run($this->testSourceTextFilePath,
            CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/scaled.txt',
            50, 50);
    }
    public function testRunUnsupportedImageExtension(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->imageResizer->run($this->testSourceImageWebpPath,
            CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/scaled.webp',
            50, 50);
    }
    public function testRunCannotSaveImageGif(): void
    {
        $this->expectException(\Exception::class);
        $this->imageResizer->run($this->testSourceImageGifPath,
            CLEANDECK_TESTS_PATH . '/_support-Framework/tmp',
            50, 50);
    }
    public function testRunCannotSaveImageJpg(): void
    {
        $this->expectException(\Exception::class);
        $this->imageResizer->run($this->testSourceImageJpgPath,
            CLEANDECK_TESTS_PATH . '/_support-Framework/tmp',
            50, 50);
    }
    public function testRunCannotSaveImagePng(): void
    {
        $this->expectException(\Exception::class);
        $this->imageResizer->run($this->testSourceImagePngPath,
            CLEANDECK_TESTS_PATH . '/_support-Framework/tmp',
            50, 50);
    }
    public function testRunTargetDownscaleUpscale(): void
    {
        $this->imageResizer->run($this->testSourceImageGifPath,
            CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/scaled.gif',
            300, 300);
        $this->assertFileExists(CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/scaled.gif');
    }
    public function testRunTargetDownscaleError(): void
    {
        $this->expectException(\Exception::class);
        $this->imageResizer->run($this->testSourceImageGif1x1Path,
            CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/scaled.gif',
            50, 50);
        $this->assertFileExists(CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/scaled.gif');
    }
    public function testRunOk(): void
    {
        $this->imageResizer->run($this->testSourceImageGifPath,
            CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/scaled.gif',
            50, 50);
        $this->assertFileExists(CLEANDECK_TESTS_PATH . '/_support-Framework/tmp/scaled.gif');
    }
}
