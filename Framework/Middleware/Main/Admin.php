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

use Framework\Libraries\Cookie\CookieMessengerWriter;
use Framework\Middleware\MiddlewareInterface;
use Framework\Libraries\CleanDeckStatics;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\UrlUtils;

final class Admin implements MiddlewareInterface
{
    /**
     * @param array<mixed>|null $arguments
     */
    public static function before(array $arguments = null): void
    {
        // Restrict the access to /admin routes by enforcing minimum_admin_account_rank.
        //  (see cleandeck-routing-definitions.json)

        $account_rank = CleanDeckStatics::getAccountRank();
        // If required adjust the value of the absolute minimum admin account rank (100).
        $minimum_admin_account_rank = (int)($arguments[0] ?? 100);
        if ($account_rank < $minimum_admin_account_rank) {
            CookieMessengerWriter::setMessage(
                null,
                true,
                'Insufficient privileges'
            );
            HttpResponse::redirectTo(UrlUtils::baseUrl());
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
