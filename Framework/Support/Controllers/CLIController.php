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

namespace Framework\Support\Controllers;

use Framework\Libraries\Cache\HttpCacheConfig;
use Framework\Libraries\Cache\ThrottleCacheConfig;
use Framework\Libraries\Http\HttpResponse;
use Phpfastcache\CacheManager;

final class CLIController
{
    /**
     * @throws \Exception
     */
    private function clear_http_cache(): void
    {
        $config_public = new HttpCacheConfig(false);
        $extendedCacheItemPool = CacheManager::getInstance($config_public->getDriver(),
            $config_public->getConfigurationOption());

        if (!$extendedCacheItemPool->clear()) {
            throw new \Exception('Public http cache cleanup error');
        }


        $config_private = new HttpCacheConfig(true);
        $cache_private = CacheManager::getInstance($config_private->getDriver(),
            $config_private->getConfigurationOption());

        if (!$cache_private->clear()) {
            throw new \Exception('Private http cache cleanup error');
        }
    }

    /**
     * @throws \Exception
     */
    private function clear_throttle_cache(): void
    {
        $throttleCacheConfig = new ThrottleCacheConfig();
        $extendedCacheItemPool = CacheManager::getInstance($throttleCacheConfig->getDriver(),
            $throttleCacheConfig->getConfigurationOption());

        if (!$extendedCacheItemPool->clear()) {
            throw new \Exception('Throttle cache cleanup error');
        }
    }

    public function clear_cache(): void
    {
        // throttle cache
        try {
            $this->clear_throttle_cache();
        } catch (\Exception $exception) {
            $err_msg = 'Throttle cache exception: ' . $exception->getMessage();
            \error_log($err_msg);
            HttpResponse::send(500, $err_msg);
            return;
        } catch (\Error $error) {
            $err_msg = 'Throttle cache error: ' . $error->getMessage();
            \error_log($err_msg);
            HttpResponse::send(500, $err_msg);
            return;
        }

        // http cache
        try {
            $this->clear_http_cache();
        } catch (\Exception $exception) {
            $err_msg = 'Http cache exception: ' . $exception->getMessage();
            \error_log($err_msg);
            HttpResponse::send(500, $err_msg);
            return;
        } catch (\Error $error) {
            $err_msg = 'Http cache error: ' . $error->getMessage();
            \error_log($err_msg);
            HttpResponse::send(500, $err_msg);
            return;
        }
    }
}
