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

use Framework\Libraries\Cache\ThrottleCacheConfig;
use Framework\Libraries\Http\HttpRequest;
use Framework\Libraries\Http\HttpResponse;
use Framework\Middleware\MiddlewareInterface;
use Phpfastcache\CacheManager;

/**
 * Throttle is implemented using library 'phpfastcache'.
 */
final class Throttle implements MiddlewareInterface
{
    /**
     * A route can set its own throttle hits weight by using a number x (0 &lt; x &lt; 1) in the corresponding route file.<br>
     * In the following example the route can be used 2 times maximum in the interval set for throttle:<br>
     * <strong>Throttle::class => [0.6], </strong> ...
     * @param array<mixed>|null $arguments
     * @throws \Exception
     */
    public static function before(array $arguments = null): void
    {
        $throttleCacheConfig = new ThrottleCacheConfig();

        $max_hits = \env('throttle.hits', 30);
        if (isset($arguments, $arguments[0]) &&
            is_numeric($arguments[0]) &&
            $arguments[0] > 0 && $arguments[0] < 1) {
            $hit_weight = max(1, round($max_hits * $arguments[0]));
        } else {
            $hit_weight = 1;
        }

        try {
            $cache = CacheManager::getInstance($throttleCacheConfig->getDriver(),
                $throttleCacheConfig->getConfigurationOption());
        } catch (\Exception $exception) {
            \error_log('Cache instance exception (throttle): ' . $exception->getMessage());
            return;
        }

        $client_key = \preg_replace('/[^a-zA-Z0-9_. -]/', ' ',
            HttpRequest::getIP() . HttpRequest::getHeader('user-agent'));
        try {
            $entry = $cache->getItem($client_key);
        } catch (\Exception $exception) {
            \error_log('Cache cannot get item (throttle): ' . $exception->getMessage());
            return;
        }


        // Adjust as required. Default 30 hits / 60 seconds.
        if ($entry->isHit()) {
            $recorded_hits = $entry->get();
            if ($recorded_hits > $max_hits) {
                HttpResponse::send(429, 'Too many requests');
            } else {
                $entry->set($recorded_hits + $hit_weight);
                $cache->save($entry);
            }
        } else {
            $max_interval = \env('throttle.interval', 60);
            $entry->set(1)->expiresAfter($max_interval);
            $cache->save($entry);
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
