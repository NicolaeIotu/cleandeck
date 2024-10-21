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

use Framework\Libraries\Captcha\CustomCaptcha;
use Framework\Libraries\Http\HttpRequest;
use Framework\Libraries\Http\HttpResponse;

final class CaptchaController
{
    public function ajax_request_refresh_captcha(): void
    {
        if (HttpRequest::isAJAX()) {
            HttpResponse::setHeaders([
                'content-type' => 'application/json; charset=UTF-8',
            ]);
        } else {
            HttpResponse::send(400, 'Expecting an AJAX request');
            return;
        }
        try {
            $customCaptcha = new CustomCaptcha(
                $_GET['width'] ?? null,
                $_GET['height'] ?? null,
                $_GET['cc_suffix'] ?? null);
        } catch (\Exception $exception) {
            // no usable captcha data to send
            HttpResponse::send(500, $exception->getMessage());
            return;
        }

        try {
            $image_data = $customCaptcha->getImageData();
            HttpResponse::send(200, \json_encode($image_data));
        } catch (\Exception $exception) {
            HttpResponse::send(500, $exception->getMessage());
            return;
        }
    }
}
