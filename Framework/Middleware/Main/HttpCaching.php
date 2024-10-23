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

use Framework\Libraries\Cache\HttpCacheConfig;
use Framework\Libraries\CleanDeckStatics;
use Framework\Libraries\Cookie\AppCookies;
use Framework\Libraries\Cookie\CookieUtils;
use Framework\Libraries\Http\HttpRequest;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\UrlUtils;
use Framework\Middleware\MiddlewareInterface;
use Phpfastcache\CacheManager;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\Exceptions\PhpfastcacheDriverCheckException;
use Phpfastcache\Exceptions\PhpfastcacheDriverException;
use Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException;
use Phpfastcache\Exceptions\PhpfastcacheExtensionNotInstalledException;
use Phpfastcache\Exceptions\PhpfastcacheLogicException;
use Psr\Cache\InvalidArgumentException;

/**
 * Cache is implemented using library 'phpfastcache'.
 * Handles http cache.
 */
final class HttpCaching implements MiddlewareInterface
{
    private static ?ExtendedCacheItemPoolInterface $extendedCacheItemPool = null;
    private static ?string $cache_key = null;
    private static ?int $cache_interval;

    private static bool $is_private_cache = false;
    private static string $private_cache_cookie_name = '';
    private static string $private_cache_cookie_value = '';
    private static ?string $username = null;
    private static string $request_method = 'GET';

    /**
     * @param array<mixed>|null $arguments
     * @return string|void
     */
    public static function before(array $arguments = null)
    {
        if (!\env('cleandeck.cache_enable', false)) {
            return;
        }

        \header_remove('cache-control');

        if (CleanDeckStatics::skipCache()) {
            return;
        }

        self::$request_method = HttpRequest::getMethod();

        $user_details = CleanDeckStatics::getUserDetails();
        if (isset($user_details['username'])) {
            self::$username = $user_details['username'];
        } else {
            \error_log('Http private cache error: cannot retrieve user details');
            return;
        }

        if (self::$request_method === 'GET') {
            self::$is_private_cache = (isset($arguments, $arguments['private']) &&
                    $arguments['private'] === true) &&
                CleanDeckStatics::isAuthenticated();
            if (isset($arguments, $arguments['interval']) &&
                is_int($arguments['interval']) &&
                $arguments['interval'] > 10) {
                self::$cache_interval = $arguments['interval'];
            }

            try {
                $cache = self::getCache(self::$is_private_cache);
            } catch (\Exception $exception) {
                \error_log('Cache instance exception (http get): ' . $exception->getMessage());
                return;
            }

            $cache_key = self::generateCacheKey(self::$is_private_cache);
            if ($cache_key === null) {
                \error_log('Run UserDetails::class before HttpCaching::class');
                return;
            }

            try {
                $entry = $cache->getItem($cache_key);
            } catch (\Exception $exception) {
                \error_log('Cache cannot get item (http get:before): ' . $exception->getMessage());
                return;
            }

            // adjust headers
            if (self::$is_private_cache) {
                // default 30 min (no stale data)
                if (!isset(self::$cache_interval)) {
                    self::$cache_interval = 1800;
                }

                HttpResponse::setHeaders([
                    'cache-control' => 'private, max-age=' . self::$cache_interval . ', must-revalidate',
                ]);
                // set private cache cookie if missing
                if (isset(self::$private_cache_cookie_name,
                    self::$private_cache_cookie_value)) {
                    CookieUtils::setCookie(
                        self::$private_cache_cookie_name,
                        self::$private_cache_cookie_value,
                        \time() + self::$cache_interval
                    );
                }
            } else {
                // default 3 hours (use stale data if available)
                if (!isset(self::$cache_interval)) {
                    self::$cache_interval = 10800;
                }
                HttpResponse::setHeaders([
                    'cache-control' => 'public, max-age=' . self::$cache_interval,
                ]);
            }

            if ($entry->isHit()) {
                return $entry->get();
            }

            // prepare the entries required by method self::after
            self::$extendedCacheItemPool = $cache;
            self::$cache_key = $cache_key;
        } else {
            // POST
            HttpResponse::noCache();


            // Delete any cache entries declared as being affected by the outcome of this request.
            if (isset($arguments)) {
                // private cache deletions
                $clear_private_urls = isset($arguments['clear-private-urls']) &&
                is_array($arguments['clear-private-urls']) &&
                $arguments['clear-private-urls'] !== [] ? $arguments['clear-private-urls'] : null;
                $clear_private_tags = isset($arguments['clear-private-tags']) &&
                is_array($arguments['clear-private-tags']) &&
                $arguments['clear-private-tags'] !== [] ? $arguments['clear-private-tags'] : null;
                if (isset($clear_private_urls) || isset($clear_private_tags)) {
                    self::deleteCacheItems(true, $clear_private_urls, $clear_private_tags);
                }

                // public cache deletions
                $clear_public_urls = isset($arguments['clear-public-urls']) &&
                is_array($arguments['clear-public-urls']) &&
                $arguments['clear-public-urls'] !== [] ? $arguments['clear-public-urls'] : null;
                $clear_public_tags = isset($arguments['clear-public-tags']) &&
                is_array($arguments['clear-public-tags']) &&
                $arguments['clear-public-tags'] !== [] ? $arguments['clear-public-tags'] : null;
                if (isset($clear_public_urls) || isset($clear_public_tags)) {
                    self::deleteCacheItems(false, $clear_public_urls, $clear_public_tags);
                }
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
        if (self::$request_method === 'GET') {
            if (isset(self::$extendedCacheItemPool, self::$cache_key)) {
                try {
                    $cache_entry = self::$extendedCacheItemPool->getItem(self::$cache_key);
                    $cache_entry->set($payload)->expiresAfter(self::$cache_interval);

                    // Start add tags
                    // Framework tags
                    $cache_entry->addTag(
                        \preg_replace('/[^a-zA-Z0-9_-]/', '__', UrlUtils::current_path()));
                    if (self::$is_private_cache) {
                        $cache_entry->addTag(self::$private_cache_cookie_value);
                    }
                    // Application tags
                    if (isset($arguments, $arguments['tags']) &&
                        is_array($arguments['tags']) &&
                        $arguments['tags'] !== []) {
                        $cache_entry->addTags($arguments['tags']);
                    }
                    // End add tags

                    self::$extendedCacheItemPool->save($cache_entry);
                } catch (\Exception $exception) {
                    \error_log('Cache cannot get item (http:after): ' . $exception->getMessage());
                }
            }
        }

        // must return the payload
        return $payload;
    }

    /**
     * @throws PhpfastcacheExtensionNotInstalledException
     * @throws PhpfastcacheDriverCheckException
     * @throws PhpfastcacheLogicException
     * @throws PhpfastcacheDriverException
     * @throws PhpfastcacheDriverNotFoundException
     */
    private static function getCache(bool $is_private_cache): ExtendedCacheItemPoolInterface
    {
        $httpCacheConfig = new HttpCacheConfig($is_private_cache);
        return CacheManager::getInstance($httpCacheConfig->getDriver(),
            $httpCacheConfig->getConfigurationOption());
    }


    /**
     * @param bool $is_private_cache
     * @param string[]|null $urls
     * @param string[]|null $tags
     * @return void
     */
    private static function deleteCacheItems(bool $is_private_cache, array $urls = null, array $tags = null): void
    {
        try {
            $cache = self::getCache($is_private_cache);
        } catch (\Exception $exception) {
            \error_log('Cache instance exception (http post ' .
                ($is_private_cache ? 'private' : 'public') . ' cache): ' . $exception->getMessage());
            return;
        }

        if (isset($urls)) {
            $cache_keys = [];
            foreach ($urls as $url) {
                $cache_key = self::generateCacheKey(true, $url);
                if ($cache_key === null) {
                    \error_log('Run UserDetails::class before HttpCaching::class (deleteCacheItems)');
                    return;
                }
                $cache_keys[] = $cache_key;
            }

            try {
                $cache->deleteItems($cache_keys);
            } catch (InvalidArgumentException $invalidArgumentException) {
                \error_log('Http cache (post private) invalid argument exception: ' .
                    $invalidArgumentException->getMessage());
                return;
            }
        }

        if (isset($tags)) {
            $cache->deleteItemsByTags(
                \array_map('self::translateTags', $tags)
            );
        }
    }

    private static function translateTags(string $tag): string
    {
        if ($tag === '$UID') {
            self::setCacheCookieDetails();
            return self::$private_cache_cookie_value;
        }

        if (self::$request_method === 'POST' &&
            str_contains($tag, '$_POST')) {
            $url = preg_replace_callback(
                '/\$_POST:([^\/]+)/',
                static function (array $match) {
                    return $_POST[$match[1]] ?? '?';
                },
                $tag);
            return \preg_replace('/[^a-zA-Z0-9_-]/', '__', (string) $url);
        }

        if (self::$request_method === 'GET' &&
            str_contains($tag, '$_GET')) {
            $url = preg_replace_callback(
                '/\$_GET:([^\/]+)/',
                static function (array $match) {
                    return $_GET[$match[1]] ?? '?';
                },
                $tag);
            return \preg_replace('/[^a-zA-Z0-9_-]/', '__', (string) $url);
        }

        return $tag;
    }

    private static function setCacheCookieDetails(): void
    {
        self::$private_cache_cookie_name = AppCookies::PRIVATE_CACHE_COOKIE_NAME();
        self::$private_cache_cookie_value =
            $_COOKIE[self::$private_cache_cookie_name] ?? \hash('sha256',
                \env('cleandeck.app_key', '') . self::$username);
    }

    private static function generateCacheKey(bool $is_private_cache, string $uri = null): ?string
    {
        if ($is_private_cache) {
            // Private cache.
            // The presentation depends on URL (including query!).
            // Furthermore, it is specific for each user identified by an individual
            //   private cache cookie.
            if (!isset($url)) {
                if (!isset(self::$username)) {
                    return null;
                }
                // $url is set only by some POST requests (see 'after').
                self::setCacheCookieDetails();
            }
            $cache_key = ($uri ?? $_SERVER['REQUEST_URI']) . self::$private_cache_cookie_value;
        } else {
            // Public cache.
            // The presentation depends on URL (including query!), authentication status and account rank.
            $cache_key = ($uri ?? $_SERVER['REQUEST_URI']) . CleanDeckStatics::isAuthenticated();
            $account_rank = CleanDeckStatics::getAccountRank();
            if ($account_rank >= 1000) {
                $cache_key .= 'admin2';
            } elseif ($account_rank >= 50) {
                $cache_key .= 'admin1';
            } else {
                $cache_key .= 'user';
            }
        }

        return \preg_replace('/[^a-zA-Z0-9_. &?-]/', ' ', $cache_key);
    }
}
