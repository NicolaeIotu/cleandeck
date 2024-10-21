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

namespace Framework\Libraries\Cache;

use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Drivers\Files\Config as FilesConfig;
use Phpfastcache\Drivers\Redis\Config as RedisConfig;
use Phpfastcache\Drivers\Sqlite\Config as SqliteConfig;
use Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidTypeException;

class HttpCacheConfig
{
    // Adjust cache settings preferably in file .env.ini.
    private string $driver;

    /**
     * @var array<string, mixed>
     */
    private array $setup;

    private string $default_setup_driver = 'files';
    private string $default_private_setup_path = '/cache/http/private';
    private string $default_public_setup_path = '/cache/http/public';

    /**
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSetup(): array
    {
        return $this->setup;
    }

    private ConfigurationOption $configurationOption;

    /**
     * @return ConfigurationOption
     */
    public function getConfigurationOption(): ConfigurationOption
    {
        return $this->configurationOption;
    }

    /**
     * @throws PhpfastcacheInvalidTypeException
     * @throws PhpfastcacheInvalidConfigurationException
     * @throws \Exception
     */
    public function __construct(bool $is_private_cache = true)
    {
        $default_cache_path =
            $is_private_cache ? $this->default_private_setup_path : $this->default_public_setup_path;
        $cache_setup = \env(
            $is_private_cache ? 'cache_http_private' : 'cache_http_public',
            [
                'path' => $default_cache_path,
            ]);

        if (\is_array($cache_setup)) {
            $this->setup = $cache_setup;
        } else {
            $this->setup = [
                'path' => $default_cache_path,
            ];
        }

        if (isset($this->setup['driver'])) {
            $this->driver = $this->setup['driver'];
            unset($this->setup['driver']);
        } else {
            $this->driver = $this->default_setup_driver;
        }

        $this->setupConfigurationOption();
    }

    /**
     * Override this function to include additional drivers when required.
     * @throws PhpfastcacheInvalidTypeException
     * @throws PhpfastcacheInvalidConfigurationException
     * @throws \InvalidArgumentException
     */
    protected function setupConfigurationOption(): void
    {
        switch (strtolower($this->driver)) {
            case 'files':
                $this->setup['path'] = CLEANDECK_WRITE_PATH .
                    ($this->setup['path'] ?? '/cache/throttle');
                $this->configurationOption = new FilesConfig($this->setup);
                break;
            case 'redis':
                $this->setup['redisClient'] = new \Redis();
                $this->configurationOption = new RedisConfig($this->setup);
                break;
            case 'sqlite':
                $this->setup['path'] = CLEANDECK_WRITE_PATH .
                    ($this->setup['path'] ?? '/cache/throttle.sql');
                $this->configurationOption = new SqliteConfig($this->setup);
                break;
            default:
                throw new \InvalidArgumentException('Driver "' . $this->driver . '" is not yet set up ' .
                    'in class ' . self::class . '. See existing drivers in class ' .
                    self::class . ' and set it up yourself.');
        }
    }
}
