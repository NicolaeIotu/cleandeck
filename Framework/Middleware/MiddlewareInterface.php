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

namespace Framework\Middleware;

interface MiddlewareInterface
{
    /**
     * @param array<mixed>|null $arguments
     * @return string|void
     */
    public static function before(array $arguments = null);

    /**
     * This function will not be able to run in case of direct manipulation
     *  of the response i.e. HttpResponse::send(200, ...).
     * @param string $payload
     * @param array<mixed>|null $arguments
     * @return string
     */
    public static function after(string $payload, array $arguments = null): string;
}
