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

namespace Framework\Support\Scripts;

final class ActiveRoutes
{
    /**
     * @param string|null $filter
     * @return array<string, array<string, mixed>>
     */
    public static function list(string $filter = null): array
    {
        $lc_filter = null;
        if (isset($filter)) {
            $lc_filter = \strtolower($filter);
        }
        if ($lc_filter === 'get') {
            return self::listGetRoutes();
        }
        if ($lc_filter === 'post') {
            return self::listPostRoutes();
        }

        self::extractAddons();

        if ($lc_filter === 'cli') {
            $get_routes = self::listGetRoutes();
            $post_routes = self::listPostRoutes();
            $get_routes['GET'] = [];
            $post_routes['POST'] = [];
            return \array_merge($get_routes, $post_routes);
        }


        $get_routes = self::listGetRoutes($filter);
        $post_routes = self::listPostRoutes($filter);
        return \array_merge($get_routes, $post_routes);
    }

    /**
     * @var string[]
     */
    private static array $addons = [];

    private static function extractAddons(): void
    {
        $ini = \parse_ini_file(CLEANDECK_ROOT_PATH . '/.env.ini',
            true, INI_SCANNER_TYPED);
        if (\is_array($ini)) {
            if (isset($ini['cleandeck']['addons']) && $ini['cleandeck']['addons'] !== '') {
                $addons_raw_array = \explode(',', (string)$ini['cleandeck']['addons']);
                self::$addons = \array_map(static function ($addon): string {
                    return \trim(\ucfirst(\strtolower($addon)));
                }, $addons_raw_array);
            }
        }
    }


    /**
     * Load order is:
     *   - main routes
     *   - addons' routes (.env.ini -> addons) - overwriting identical endpoints in main routes
     *   - user routes - overwriting identical endpoints in both main routes and addons' routes
     * DO NOT USE 'include_once' and 'require_once' because this method may be called
     *  after the initial routing ends (see sitemap).
     * @return array<string, array<string, mixed>>
     */
    private static function listGetRoutes(string $filter = null): array
    {

        $result = [
            'GET' => null,
            'GET_CLI' => null,
        ];

        // MAIN ROUTES
        $main_get = __DIR__ . '/../../../Framework/Routes/Main/GETRoutes.php';
        if (\file_exists($main_get)) {
            require $main_get;
        }

        // ADDONS' ROUTES
        foreach (self::$addons as $addon) {
            $agr = CLEANDECK_ADDON_ROUTES_PATH . '/' . $addon . '/GETRoutes.php';
            if (\file_exists($agr)) {
                include $agr;
            }
        }

        // USER ROUTES
        require CLEANDECK_USER_ROUTES_PATH . '/GETRoutes.php';
        if (\file_exists(CLEANDECK_USER_ROUTES_PATH . '/GETRoutes_1.php')) {
            include CLEANDECK_USER_ROUTES_PATH . '/GETRoutes_1.php';
        }

        if (isset($routes) && \method_exists($routes, 'getRoutes')) {
            $result['GET'] = $routes->getRoutes($filter);
        }
        if (isset($routes) && \method_exists($routes, 'getCliRoutes')) {
            $result['GET_CLI'] = $routes->getCliRoutes($filter);
        }


        return $result;
    }

    /**
     * Load order is:
     *   - main routes
     *   - addons' routes (.env.ini -> addons) - overwriting identical endpoints in main routes
     *   - user routes - overwriting identical endpoints in both main routes and addons' routes
     * @return array<string, array<string, mixed>>
     */
    private static function listPostRoutes(string $filter = null): array
    {
        $result = [];

        // MAIN ROUTES
        $main_post = __DIR__ . '/../../../Framework/Routes/Main/POSTRoutes.php';
        if (\file_exists($main_post)) {
            require_once $main_post;
        }

        // ADDONS' ROUTES
        foreach (self::$addons as $addon) {
            $apr = CLEANDECK_ADDON_ROUTES_PATH . '/' . $addon . '/POSTRoutes.php';
            if (\file_exists($apr)) {
                include_once $apr;
            }
        }

        // USER ROUTES
        require_once CLEANDECK_USER_ROUTES_PATH . '/POSTRoutes.php';
        if (\file_exists(CLEANDECK_USER_ROUTES_PATH . '/POSTRoutes_1.php')) {
            include_once CLEANDECK_USER_ROUTES_PATH . '/POSTRoutes_1.php';
        }

        if (isset($routes) && \method_exists($routes, 'getRoutes')) {
            $result['POST'] = $routes->getRoutes($filter);
        }
        if (isset($routes) && \method_exists($routes, 'getCliRoutes')) {
            $result['POST_CLI'] = $routes->getCliRoutes($filter);
        }

        return $result;
    }
}
