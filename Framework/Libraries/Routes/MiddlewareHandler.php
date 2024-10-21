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

use Framework\Middleware\Main\AAAInit;
use Framework\Middleware\Main\Admin;
use Framework\Middleware\Main\ApplicationStatusJWT;
use Framework\Middleware\Main\CaptchaEnd;
use Framework\Middleware\Main\CSP;
use Framework\Middleware\Main\CSRFEnd;
use Framework\Middleware\Main\HttpCaching;
use Framework\Middleware\Main\SEO;
use Framework\Middleware\Main\Throttle;
use Framework\Middleware\Main\UserDetails;

class MiddlewareHandler
{
    private const framework_middleware = [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class,
        Admin::class,
        HttpCaching::class,
        CSRFEnd::class,
        CaptchaEnd::class,
        CSP::class,
        SEO::class,
    ];

    // The order of framework middleware is very important!
    // Order of method 'before'.
    private const ordered_framework_middleware__before = [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class,
        Admin::class,
        HttpCaching::class,
        CSRFEnd::class,
        CaptchaEnd::class,
        CSP::class,
        SEO::class,
    ];
    // The order of framework middleware is very important!
    // Order of method 'after'.
    private const ordered_framework_middleware__after = [
        SEO::class,
        HttpCaching::class,
    ];

    // All middleware declared for this route
    /**
     * @var string[]
     */
    private array $route_middleware = [];
    /**
     * User middleware - middleware which are not framework middleware
     * @var string[]
     */
    private array $route_user_middleware = [];
    protected ?string $response_body;

    /**
     * @param string[]|null $middleware
     */
    public function __construct(array $middleware = null)
    {
        if (!\is_null($middleware)) {
            $this->route_middleware = $middleware;
            $this->processUserMiddleware($middleware);
            // $this->runCompatibilityAdjustments();
        }
    }


    /**
     * @return string|void
     * @throws \Error
     * @throws \Exception
     */
    protected function runBeforeMiddleware()
    {
        // First run framework middleware as ordered
        foreach (self::ordered_framework_middleware__before as $framework_middleware_element) {
            if (\in_array($framework_middleware_element, $this->route_middleware)) {
                // framework middleware called with no parameters
                $fh_result = $framework_middleware_element::before();
                if (\is_string($fh_result)) {
                    return $fh_result;
                }
            } elseif (\array_key_exists($framework_middleware_element, $this->route_middleware)) {
                // framework middleware called with parameters
                $fh_result = $framework_middleware_element::before($this->route_middleware[$framework_middleware_element]);
                if (\is_string($fh_result)) {
                    return $fh_result;
                }
            }
        }

        // User middleware
        foreach ($this->route_user_middleware as $key => $value) {
            if (\is_int($key)) {
                // user middleware with no parameters
                $fh_result = $value::before();
            } else {
                // user middleware with parameters
                $fh_result = $key::before($value);
            }
            if (\is_string($fh_result)) {
                return $fh_result;
            }
        }
    }

    /**
     * @return void
     * @throws \Error
     * @throws \Exception
     */
    protected function runAfterMiddleware()
    {
        foreach (self::ordered_framework_middleware__after as $framework_middleware_element) {
            if (\in_array($framework_middleware_element, $this->route_middleware)) {
                // framework middleware called with no parameters
                $this->response_body = $framework_middleware_element::after($this->response_body);
            } elseif (\array_key_exists($framework_middleware_element, $this->route_middleware)) {
                // framework middleware called with parameters
                $this->response_body = $framework_middleware_element::after($this->response_body,
                    $this->route_middleware[$framework_middleware_element]);
            }
        }

        // User middleware
        foreach ($this->route_user_middleware as $key => $value) {
            if (\is_int($key)) {
                // user middleware with no parameters
                $this->response_body = $value::after($this->response_body);
            } else {
                // user middleware with parameters
                $this->response_body = $key::after($this->response_body, $value);
            }
        }
    }

    /**
     * Extract user middleware and perform other actions.
     * @param string[] $middleware
     * @return void
     */
    private function processUserMiddleware(array $middleware): void
    {
        foreach ($middleware as $key => $value) {
            if (\is_int($key)) {
                if (!\in_array($value, self::framework_middleware)) {
                    $this->route_user_middleware[] = $value;
                }
            } else {
                if (!\in_array($key, self::framework_middleware)) {
                    $this->route_user_middleware[$key] = $value;
                }
            }
        }
    }

//    private function runCompatibilityAdjustments(): void
//    {
//        // no adjustments at the moment
//    }
}
