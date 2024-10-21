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

use GdImage;

final class ImageResizer
{
    public const SUPPORTED_EXTENSIONS = ['gif', 'jpg', 'jpeg', 'png'];

    private readonly bool $gd_can_create_gif;

    private readonly bool $gd_can_create_jpg;

    private readonly bool $gd_can_create_png;

    public function GDCanCreateGif(): bool
    {
        return $this->gd_can_create_gif;
    }

    public function GDCanCreateJpg(): bool
    {
        return $this->gd_can_create_jpg;
    }

    public function GDCanCreatePng(): bool
    {
        return $this->gd_can_create_png;
    }

    /**
     * @return array<string, int>|bool
     */
    private function getDownscaleDimensions(GdImage $gdImage, int $max_width, int $max_height): array|bool
    {
        $initial_width = \imagesx($gdImage);
        $initial_height = \imagesy($gdImage);
        if ($initial_width < 2 || $initial_height < 2) {
            return false;
        }

        $sfx = $initial_width / $max_width;
        $sfy = $initial_height / $max_height;
        if ($sfx < 1 && $sfy < 1) {
            return true;
        }

        $scale_factor = \max($sfx, $sfy);

        return [
            "width" => (int)\round($initial_width / $scale_factor),
            "height" => (int)\round($initial_height / $scale_factor),
        ];
    }


    public function __construct()
    {
        $gd_info = \gd_info();

        $this->gd_can_create_gif = isset($gd_info['GIF Create Support']) && $gd_info['GIF Create Support'];
        $this->gd_can_create_jpg = isset($gd_info['JPEG Support']) && $gd_info['JPEG Support'];
        $this->gd_can_create_png = isset($gd_info['PNG Support']) && $gd_info['PNG Support'];
    }

    /**
     * All types of images are allowed. Identification is done by mime type.
     * You should impose further restrictions if required.
     */
    public static function getImageExtension(string $file): bool|string
    {
        $extension = \strtolower(\pathinfo($file, PATHINFO_EXTENSION));

        $mime_type = \mime_content_type($file);
        if ($mime_type === false) {
            return false;
        }

        $mime_type = \strtolower($mime_type);
        $mime_type_parts = \explode('/', $mime_type);
        $mime0 = \strtolower($mime_type_parts[0]);
        $allowed_mime0 = ['image', 'img'];
        if (!\in_array($mime0, $allowed_mime0)) {
            return false;
        }

        // match mime type with the extension
        $extension_mime = $mime_type_parts[1];
        if ($extension_mime !== $extension) {
            if (($extension === 'jpg' && $extension_mime === 'jpeg') ||
                ($extension === 'jpeg' && $extension_mime === 'jpg')) {
                return $extension;
            }
            return false;
        }

        return $extension;
    }

    /**
     * @throws \Exception
     */
    public function run(string $source_image, string $destination_image, int $max_width, int $max_height): void
    {
        // check parameters
        $real_source = \realpath($source_image);
        if ($real_source === '0' || $real_source === false) {
            throw new \InvalidArgumentException('No such file');
        }

        $destination_directory = \realpath(\dirname($destination_image));
        if ($destination_directory === '0' || $destination_directory === false) {
            throw new \InvalidArgumentException('Invalid destination');
        }

        if ($max_width < 1) {
            throw new \InvalidArgumentException('Invalid width');
        }

        if ($max_height < 1) {
            throw new \InvalidArgumentException('Invalid height');
        }


        $image_extension = self::getImageExtension($real_source);
        if ($image_extension === false) {
            throw new \InvalidArgumentException('Invalid type of file');
        }

        if (!\in_array($image_extension, self::SUPPORTED_EXTENSIONS)) {
            throw new \InvalidArgumentException('Unsupported type of image');
        }

        // end check parameters


        switch ($image_extension) {
            case 'gif':
                if (!$this->gd_can_create_gif) {
                    throw new \Exception('GD cannot process gif');
                }

                $img = \imagecreatefromgif($real_source);
                break;
            case 'jpg':
            case 'jpeg':
                if (!$this->gd_can_create_jpg) {
                    throw new \Exception('GD cannot process jpg');
                }

                $img = \imagecreatefromjpeg($real_source);
                break;
            case 'png':
                if (!$this->gd_can_create_png) {
                    throw new \Exception('GD cannot process png');
                }

                $img = \imagecreatefrompng($real_source);
                break;
            default:
                throw new \InvalidArgumentException('Unsupported type of image');
        }

        if (!$img) {
            throw new \Exception('GD cannot process image');
        }

        $downscale_dimensions = $this->getDownscaleDimensions($img, $max_width, $max_height);

        if ($downscale_dimensions === false) {
            throw new \Exception('GD cannot retrieve image dimensions.');
        }

        if ($downscale_dimensions === true) {
            $scaled_img = $img;
        } else {
            // resize the image
            $scaled_img = \imagescale(
                $img,
                $downscale_dimensions['width'],
                $downscale_dimensions['height'],
                IMG_NEAREST_NEIGHBOUR
            );
        }

        if (!$scaled_img) {
            throw new \Exception('GD cannot scale image');
        }


        // save resized image
        $save_result = match ($image_extension) {
            'gif' => \imagegif(
                $scaled_img,
                $destination_directory . '/' . \basename($destination_image)
            ),
            'jpg', 'jpeg' => \imagejpeg(
                $scaled_img,
                $destination_directory . '/' . \basename($destination_image),
                95
            ),
            'png' => \imagepng(
                $scaled_img,
                $destination_directory . '/' . \basename($destination_image),
                2
            ),
            default => throw new \InvalidArgumentException('Unsupported type of image'),
        };

        \imagedestroy($img);
        \imagedestroy($scaled_img);

        if (!$save_result) {
            throw new \Exception('GD cannot save processed image');
        }
    }
}
