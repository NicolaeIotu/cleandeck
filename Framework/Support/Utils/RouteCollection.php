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

namespace Framework\Support\Utils;

use Framework\Interfaces\RouteCollectionInterface;

class RouteCollection implements RouteCollectionInterface
{
    /**
     * @var array<string, array<mixed>>
     */
    private array $routes = [];

    /**
     * @param string|null $uri_filter
     * @return array<string, mixed>
     */
    public function getRoutes(string $uri_filter = null): array
    {
        if (isset($uri_filter)) {
            return \array_filter($this->routes, static function ($uri) use ($uri_filter): bool {
                return \stripos($uri, $uri_filter) !== false;
            }, ARRAY_FILTER_USE_KEY);
        }
        return $this->routes;
    }

    /**
     * @var array<string, array<mixed>>
     */
    private array $cli_routes = [];

    /**
     * @param string|null $uri_filter
     * @return array<string, mixed>
     */
    public function getCliRoutes(string $uri_filter = null): array
    {
        if (isset($uri_filter)) {
            return \array_filter($this->cli_routes, static function ($uri) use ($uri_filter): bool {
                return \stripos($uri, $uri_filter) !== false;
            }, ARRAY_FILTER_USE_KEY);
        }
        return $this->cli_routes;
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
        array $middleware,
        array  $sitemap_settings = null
    ): void {
        $this->routes[$uri] = [
            'controller' => $controller,
            'method' => $function,
        ];

        // Sitemap
        if (isset($sitemap_settings) &&
            !\str_contains($uri, '(:')) {
            $this->routes[$uri]['sitemap'] = \array_merge($this->sitemap_defaults, $sitemap_settings);
            if (!isset($this->routes[$uri]['sitemap']['lastmod'])) {
                \date_default_timezone_set('UTC');
                $this->routes[$uri]['sitemap']['lastmod'] = \gmdate('Y-m-d\TH:i:s\.v\Z');
            }
        }
    }

    /**
     * @var array<string, mixed>
     */
    private array $sitemap_defaults = [
        'changefreq' => 'weekly',
        'priority' => 0.5,
    ];

    /**
     * Used by CLI executables such as `cleandeck-clear-cache`.
     * @param string $uri
     * @param string $controller
     * @param string $function
     * @return void
     */
    public function cli(string $uri, string $controller, string $function): void
    {
        $this->cli_routes[$uri] = [
            'controller' => $controller,
            'method' => $function,
        ];
    }
}
