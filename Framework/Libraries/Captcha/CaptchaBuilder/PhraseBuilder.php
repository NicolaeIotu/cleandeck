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
 * Generates random phrase
 *
 * @author Gregwar <g.passault@gmail.com>
 */
class PhraseBuilder implements PhraseBuilderInterface
{
    public int $length;

    public string $charset;

    /**
     * Constructs a PhraseBuilder with given parameters
     */
    public function __construct(int $length = 5, string $charset = 'abcdefghijklmnpqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $this->length = $length;
        $this->charset = $charset;
    }

    /**
     * Generates  random phrase of given length with given charset
     */
    public function build(int $length = null, string $charset = null): string
    {
        if ($length !== null) {
            $this->length = $length;
        }
        if ($charset !== null) {
            $this->charset = $charset;
        }

        $phrase = '';
        $chars = str_split($this->charset);

        for ($i = 0; $i < $this->length; $i++) {
            $phrase .= $chars[array_rand($chars)];
        }

        return $phrase;
    }

    /**
     * "Niceize" a code
     */
    public function niceize(string $str): string
    {
        return self::doNiceize($str);
    }

    /**
     * A static helper to niceize
     */
    public static function doNiceize(string $str): string
    {
        return strtr(strtolower($str), '01', 'ol');
    }

    /**
     * A static helper to compare
     */
    public static function comparePhrases(string $str1, string $str2): bool
    {
        return self::doNiceize($str1) === self::doNiceize($str2);
    }
}
