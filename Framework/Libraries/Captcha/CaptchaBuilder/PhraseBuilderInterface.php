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
 * Interface for the PhraseBuilder
 *
 * @author Gregwar <g.passault@gmail.com>
 */
interface PhraseBuilderInterface
{
    /**
     * Generates random phrase of given length with given charset
     */
    public function build(int $length = null, string $charset = null): string;

    /**
     * "Niceize" a code
     */
    public function niceize(string $str): string;
}
