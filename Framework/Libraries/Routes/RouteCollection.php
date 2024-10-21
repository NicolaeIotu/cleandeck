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

namespace Framework\Libraries\Routes;

use Framework\Interfaces\RouteCollectionInterface;
use Framework\Libraries\Utils\UrlUtils;

class RouteCollection implements RouteCollectionInterface
{
    private readonly string $active_path;

    /**
     * @return string
     */
    public function getActivePath(): string
    {
        return $this->active_path;
    }

    public function __construct()
    {
        $this->active_path = UrlUtils::current_path();
    }

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
        array  $middleware,
        array  $sitemap_settings = null
    ): void {
        if ($this->active_path === $uri) {
            // standard non-parametric routes
            new RouteResponse(
                $controller,
                $function,
                null,
                $middleware
            );
            exit(0);
        }
        if (\str_contains($uri, '/(:segment)')) {
            // parametric routes
            $uri_regexp = '/^' .
                \str_replace(
                    ['/(:segment)', '/'],
                    ['/([^/]+)', '\/'],
                    $uri) .
                '$/';

            $match_result = \preg_match($uri_regexp, $this->active_path, $matches);
            if ($match_result === 1) {
                \array_shift($matches);
                new RouteResponse(
                    $controller,
                    $function,
                    $matches,
                    $middleware
                );
                exit(0);
            }
        }
    }

    /**
     * This method is normally used to override main routes and/or addon routes and send a 404 response instead.
     * @param string[] $uris
     * @return void
     */
    public function add404(array $uris): void
    {
        if (\in_array($this->active_path, $uris)) {
            new Response404();
            exit(1);
        }
    }

    /**
     * Used by CLI executables such as `cleandeck-clear-cache`.
     * @param string $uri
     * @param string $controller
     * @param string $function
     * @return void
     */
    public function cli(string $uri, string $controller, string $function): void
    {
        if ($this->active_path === $uri) {
            (new $controller())->$function();
            exit(0);
        }
    }
}
