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

use Framework\Libraries\Http\HttpRequest;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\UrlUtils;
use Framework\Middleware\MiddlewareInterface;
use Framework\Libraries\CleanDeckStatics;
use Framework\Libraries\Cookie\CookieMessengerReader;

final class AAAInit implements MiddlewareInterface
{
    /**
     * @param array<mixed>|null $arguments
     */
    public static function before(array $arguments = null): void
    {
        if (HttpRequest::isAJAX()) {
            // No external AJAX calls
            if (!\str_starts_with((string)$_SERVER['HTTP_REFERER'], UrlUtils::baseUrl() . '/')) {
                HttpResponse::send(403);
                return;
            }
            // Responses to AJAX requests must set their own Content-Type
        } else {
            // Content-Type
            $content_type = 'text/html; charset=UTF-8';
            if (\str_ends_with(UrlUtils::url_clean(), '.xml')) {
                $content_type = 'application/xml; charset=UTF-8';
            }
            HttpResponse::setHeaders([
                'Content-Type' => $content_type,
            ]);
        }

        // CRITICAL! make sure 'session-no-update' is only used when required by your application
        \header_remove('session-no-update');

        // retrieve cookie based messages
        $cmf = CookieMessengerReader::parse();
        if (\is_array($cmf)) {
            CleanDeckStatics::setCookieMessage($cmf);

            // Setup skip cache
            CleanDeckStatics::setSkipCache(true);
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
