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

use const CLEANDECK_WRITE_PATH;

class ThrottleCacheConfig
{
    // Adjust cache settings preferably in file .env.ini.
    private string $driver;

    /**
     * @var array<string, mixed>
     */
    private array $setup;

    private string $default_setup_driver = 'files';
    private string $default_setup_path = '/cache/throttle';

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
    public function __construct()
    {

        $throttle_db_setup = \env('throttle_db',
            [
                'path' => $this->default_setup_path,
            ]);
        if (\is_array($throttle_db_setup)) {
            $this->setup = $throttle_db_setup;
        } else {
            $this->setup = [
                'path' => $this->default_setup_path,
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
     * @throws \InvalidArgumentException|\Exception
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
