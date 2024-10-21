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

use Framework\Middleware\MiddlewareInterface;
use Framework\Libraries\Cookie\AppCookies;
use Framework\Libraries\Cookie\CookieUtils;
use Framework\Libraries\Cookie\JWTCookiesHandler;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\UrlUtils;

final class ApplicationStatusJWT implements MiddlewareInterface
{
    // Important!
    // (sync with Routes.php if changing these routes)
    public const MFA_LOGIN_STEP_2_ENTRY_ROUTE_PATH = '/login-mfa-step-2';

    public const MFA_LOGIN_STEP_2_ROUTE_PATHS = [
        '/login-mfa-step-2',
        '/login-mfa-step-2/request',
        '/login-mfa-cancel/request',
    ];

    /**
     * @param array<mixed>|null $arguments
     */
    public static function before(array $arguments = null): void
    {
        try {
            $status_cookie_name = AppCookies::STATUS_COOKIE_NAME();
            try {
                $app_status = JWTCookiesHandler::describeCookie($status_cookie_name);
            } catch (\Exception $e) {
                \error_log('Failed to process status @ JWT cookie: ' . $e->getMessage());
            }

            if (isset($app_status, $app_status['mfa']) && $app_status['mfa'] === true) {
                $exp_timestamp = (int)$app_status['exp'];

                $dateTimeImmutable = new \DateTimeImmutable();
                $ts = $dateTimeImmutable->getTimestamp() * 1000;

                if ($exp_timestamp > $ts) {
                    if (!\in_array(UrlUtils::current_path(), self::MFA_LOGIN_STEP_2_ROUTE_PATHS)) {
                        // Sensitive format
                        HttpResponse::redirectTo(self::MFA_LOGIN_STEP_2_ENTRY_ROUTE_PATH);
                    }
                } else {
                    CookieUtils::deleteCookie($status_cookie_name);
                }
            }
        } catch (\LogicException $e) {
            // errors having to do with environmental setup or malformed JWT Keys
            \error_log('ApplicationStatusJWT error: ' . $e->getMessage());
        } catch (\UnexpectedValueException $e) {
            // errors having to do with JWT signature and claims
            \error_log('ApplicationStatusJWT error: ' . $e->getMessage());
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
