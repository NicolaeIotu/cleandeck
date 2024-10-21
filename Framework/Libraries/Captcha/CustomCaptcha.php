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

namespace Framework\Libraries\Captcha;

use Framework\Libraries\Cookie\CookieUtils;
use Framework\Libraries\Captcha\CaptchaBuilder\CaptchaBuilder;
use Framework\Libraries\Captcha\CaptchaBuilder\PhraseBuilder;

final class CustomCaptcha extends CaptchaBuilder
{
    private readonly int $image_width;

    private readonly int $image_height;

    private ?string $captcha_cookie_suffix;


    /**
     * @param int $width
     * @param int $height
     * @param string|null $captcha_cookie_suffix Used to recreate Captcha using the same cookie suffix.
     * @param int $characters_count
     * @param string|null $phrase
     * @throws \Exception
     */
    public function __construct(
        int    $width = 240,
        int    $height = 60,
        string $captcha_cookie_suffix = null,
        int    $characters_count = 6,
        string $phrase = null)
    {
        if (isset($phrase)) {
            parent::__construct($phrase);
        } else {
            $phraseBuilder = new PhraseBuilder(\max(4, \min(36, $characters_count)));
            parent::__construct(null, $phraseBuilder);
        }

        $this->setMaxBehindLines(1);
        $this->setMaxFrontLines(1);
        $this->setMaxAngle(15);

        $this->image_width = \max(50, \min(1000, $width));
        $this->image_height = \max(12, \min(250, $height));

        $this->build($this->image_width, $this->image_height);

        $this->captcha_cookie_suffix = $captcha_cookie_suffix;

        $hashed_phrase = self::hash($this->getPhrase());

        CookieUtils::setCookie(
            $this->getCaptchaCookieName($captcha_cookie_suffix),
            $hashed_phrase,
            \time() + CustomCaptchaConstants::CAPTCHA_COOKIE_LIFETIME
        );
    }


    /**
     * @param string|null $captcha_cookie_suffix
     * @return string
     */
    private function getCaptchaCookieName(string $captcha_cookie_suffix = null): string
    {
        if (isset($captcha_cookie_suffix)) {
            if ($captcha_cookie_suffix === '') {
                return \env('cleandeck.cookie.prefix', '') . CustomCaptchaConstants::CAPTCHA_COOKIE_BASE_NAME;
            }

            $this->captcha_cookie_suffix = $captcha_cookie_suffix;
            return \env('cleandeck.cookie.prefix', '') . CustomCaptchaConstants::CAPTCHA_COOKIE_BASE_NAME .
                '-' . $captcha_cookie_suffix;
        }


        $captcha_cookie_name = \env('cleandeck.cookie.prefix', '') . CustomCaptchaConstants::CAPTCHA_COOKIE_BASE_NAME;

        $retry_counter = 0;
        while (isset($_COOKIE[$captcha_cookie_name]) && $retry_counter <= 10) {
            ++$retry_counter;
            try {
                $captcha_cookie_suffix = \bin2hex(\random_bytes(4));
            } catch (\Exception $e) {
                \syslog(LOG_ERR, 'Error with PHP random_bytes: ' . $e->getMessage());
                $captcha_cookie_suffix = '';
            }
            $captcha_cookie_name = \env('cleandeck.cookie.prefix', '') . CustomCaptchaConstants::CAPTCHA_COOKIE_BASE_NAME .
                '-' . $captcha_cookie_suffix;
        }
        $this->captcha_cookie_suffix = $captcha_cookie_suffix;

        return $captcha_cookie_name;
    }

    /**
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function getImageData(): array
    {
        return [
            'cc_suffix' => $this->captcha_cookie_suffix,
            'image_inline' => $this->inline(),
            'image_width' => $this->image_width,
            'image_height' => $this->image_height,
        ];
    }

    private static function hash(string $data): string
    {
        return \hash('sha256', \env('cleandeck.app_key', '') . \strtolower($data));
    }


    /**
     * Matches the user input against the corresponding value stored as cookie, and deletes the cookie.
     * @param string|null $user_input
     * @param string|null $captcha_cookie_suffix
     * @return bool
     */
    public static function endCaptcha(string $user_input = null, string $captcha_cookie_suffix = null): bool
    {
        self::cleanup();

        if (\is_string($user_input)) {
            $captcha_cookie_name = \env('cleandeck.cookie.prefix', '') . CustomCaptchaConstants::CAPTCHA_COOKIE_BASE_NAME;
            if (isset($captcha_cookie_suffix) && $captcha_cookie_suffix !== '') {
                $captcha_cookie_name .= '-' . $captcha_cookie_suffix;
            }
            if (isset($_COOKIE[$captcha_cookie_name])) {
                return self::hash($user_input) === $_COOKIE[$captcha_cookie_name];
            }
        }

        return false;
    }

    public static function cleanup(string $captcha_cookie_suffix = null): void
    {
        $captcha_cookie_name = \env('cleandeck.cookie.prefix', '') . CustomCaptchaConstants::CAPTCHA_COOKIE_BASE_NAME;
        if (isset($captcha_cookie_suffix) && $captcha_cookie_suffix !== '') {
            $captcha_cookie_name .= '-' . $captcha_cookie_suffix;
        }
        CookieUtils::deleteCookie($captcha_cookie_name);
    }
}
