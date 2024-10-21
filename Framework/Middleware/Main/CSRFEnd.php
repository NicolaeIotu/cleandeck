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

namespace Framework\Middleware\Main;

use Framework\Middleware\MiddlewareInterface;
use Framework\Libraries\Cookie\CookieMessengerWriter;
use Framework\Libraries\CSRF\CSRF;
use Framework\Libraries\Http\HttpRequest;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\UrlUtils;

final class CSRFEnd implements MiddlewareInterface
{
    // Add here routes which may skip security checks
    /**
     * @var string[]
     */
    private const PASS_THRU_ROUTES = [
        '/logout',
    ];

    /**
     * @param array<mixed>|null $arguments
     */
    public static function before(array $arguments = null): void
    {
        if (\in_array(UrlUtils::current_path(), self::PASS_THRU_ROUTES, true)) {
            return;
        }

        if (!CSRF::validate()) {
            if (HttpRequest::isAJAX()) {
                HttpResponse::setHeaders([
                    'content-type' => 'application/json; charset=UTF-8',
                ]);
                HttpResponse::send(403, 'CSRF_SECURITY_FAIL');
            } else {
                CookieMessengerWriter::setMessage(
                    null,
                    true,
                    'Standard security check: please retry',
                    HttpRequest::getMethod() === 'POST' ? $_POST : $_GET
                );

                // handle redirections
                $on_error_redirect_to = $arguments[0] ?? '';
                if ($on_error_redirect_to === '#REDIRECT_TO_GET#') {
                    HttpResponse::redirectTo(UrlUtils::baseUrl(UrlUtils::current_path()));
                } elseif ($on_error_redirect_to === '#REDIRECT_BACK#') {
                    if (isset($_SERVER['HTTP_REFERER']) &&
                        \str_starts_with((string)$_SERVER['HTTP_REFERER'], (string)\env('cleandeck.baseURL'))) {
                        HttpResponse::redirectTo($_SERVER['HTTP_REFERER']);
                    } else {
                        HttpResponse::redirectTo(UrlUtils::baseUrl());
                    }
                }

                // catch-all redirection
                HttpResponse::redirectTo(UrlUtils::baseUrl($on_error_redirect_to));
            }
        }
    }

    /**
     * @param string $payload
     * @param array<mixed>|null $arguments
     * @return string
     */
    public static function after(string $payload, array $arguments = null): string
    {
        // must return the payload
        return $payload;
    }
}
