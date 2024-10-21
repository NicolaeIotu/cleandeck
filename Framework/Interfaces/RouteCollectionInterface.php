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

namespace Framework\Interfaces;

interface RouteCollectionInterface
{
    /**
     * @param string|null $uri_filter
     * @return array<string, mixed>
     */

    /**
     * @param string|null $uri_filter
     * @return array<string, mixed>
     */

    /**
     * @param string $uri
     * @param string $controller
     * @param string $function
     * @param string[]|array<string, mixed> $middleware
     * @param array<string, mixed>|null $sitemap_settings
     * @return void
     */
    public function add(
        string $uri,
        string $controller,
        string $function,
        array $middleware,
        array  $sitemap_settings = null
    ): void;

    /**
     * Used by CLI executables such as `cleandeck-clear-cache`.
     * @param string $uri
     * @param string $controller
     * @param string $function
     * @return void
     */
    public function cli(string $uri, string $controller, string $function): void;
}
