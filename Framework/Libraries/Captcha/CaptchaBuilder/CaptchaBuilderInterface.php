<?php

/*
 * CleanDeck for CMD-Auth (https://link133.com) and other similar applications
 *
 * Copyright (c) 2023-2024 Iotu Nicolae, nicolae.g.iotu@link133.com
 * Licensed under the terms of the MIT License (MIT)
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * This MIT library known as Gregwar/Captcha was adjusted by Iotu Nicolae, nicolae.g.iotu@link133.com
 *  in order to fit CleanDeck requirements.
 */

namespace Framework\Libraries\Captcha\CaptchaBuilder;

/**
 * A Captcha builder
 */
interface CaptchaBuilderInterface
{
    /**
     * Builds the code
     * @param int $width
     * @param int $height
     * @param string $font
     * @param int[] $fingerprint
     * @return $this
     */
    public function build(int $width, int $height, string $font, array $fingerprint): static;

    /**
     * Saves the code to a file
     */
    public function save(string $filename, int $quality): bool;

    /**
     * Gets the image contents
     */
    public function get(int $quality): bool|string;

    /**
     * Outputs the image
     */
    public function output(int $quality): bool;
}
