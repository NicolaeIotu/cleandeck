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

namespace Framework\Libraries\CA;

use Framework\Libraries\Cookie\AppCookies;
use Framework\Libraries\Cookie\CookieMessengerWriter;
use Framework\Libraries\Cookie\JWTCookiesHandler;
use Framework\Libraries\Cookie\CookieUtils;
use Framework\Libraries\Http\HttpRequest;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\UrlUtils;

final class CMDAuth
{
    private readonly ?bool $required_auth;

    private ?string $on_error_redirect_to = '';

    /**
     * A list of paths which are publicly available without authentication when
     *   \env('cleandeck.ENVIRONMENT') is set to 'staging'.
     * Update these paths when altering Routes.php.
     * @var string[]
     */
    private array $staging_allowed_paths = [
        '/',
        '/login',
        '/login/request',
        '/login-mfa-step-2',
        '/login-mfa-step-2/request',
        '/login-mfa-cancel/request',
        '/google-oauth',
        '/google-oauth/cb',
    ];

    private bool $authenticated = false;

    public bool $mfa_in_progress = false;

    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }


    /**
     * @param bool|null $required_auth Use 'true' to require authenticated user,
     *   use 'false' to require non-authenticated user,
     *   use 'null' to allow both authenticated and non-authenticated users.
     */
    public function __construct(bool $required_auth = null, string $on_error_redirect_to = '')
    {
        // Detect and mark MFA authentication in progress
        $status_cookie_name = AppCookies::STATUS_COOKIE_NAME();
        if (isset($_COOKIE[$status_cookie_name])) {
            try {
                $app_status = JWTCookiesHandler::describeCookie($status_cookie_name);
            } catch (\Exception $e) {
                \error_log('Failed to extract MFA status(CA) @ JWT cookie: ' . $e->getMessage());
            }

            if (isset($app_status, $app_status['mfa']) && $app_status['mfa'] === true) {
                $this->mfa_in_progress = true;
            }
        }

        $this->required_auth = $required_auth;
        $this->on_error_redirect_to = $on_error_redirect_to;
    }

    private function staging_auth(): bool
    {
        $req_path = UrlUtils::current_path();
        if (!\in_array($req_path, $this->staging_allowed_paths)) {
            return $this->handleStaging();
        }

        return $this->authenticated;
    }

    private function production_auth(): bool
    {
        if (isset($this->required_auth)) {
            return $this->handleStandardAuth();
        }
        // else it means that the route is generally available to both authenticated and
        // not authenticated users
        // we still need to get authentication status
        $caResponse = $this->minimalAuth();
        $this->authenticated = !$caResponse->hasError();

        return true;
    }

    public function auth(): bool
    {
        if (\env('cleandeck.ENVIRONMENT') === 'staging') {
            return $this->staging_auth();
        }
        return $this->production_auth();
    }

    private function handleStaging(): bool
    {
        $requestIsAJAX = HttpRequest::isAJAX();

        $caResponse = $this->minimalAuth();
        $this->authenticated = !$caResponse->hasError();

        if ($this->authenticated) {
            if (isset($this->required_auth) && !$this->required_auth) {
                if ($requestIsAJAX) {
                    HttpResponse::send(401, 'Forbidden to authenticated users');
                } else {
                    $redirect_to_url = UrlUtils::baseUrl($this->on_error_redirect_to);
                    HttpResponse::redirectTo($redirect_to_url);
                }
            }
        } else {
            if ($requestIsAJAX) {
                HttpResponse::send(401, 'Authentication required');
            } else {
                CookieUtils::deleteAllCookiesExcept((\env('cleandeck.cookie.prefix', '')) . 'cp_tc_agreed');

                $redirect_to_url = UrlUtils::baseUrl();
                HttpResponse::redirectTo($redirect_to_url);
            }
        }

        return true;
    }

    private function handleStandardAuth(): bool
    {
        $requestIsAJAX = HttpRequest::isAJAX();

        $caResponse = $this->minimalAuth();
        $this->authenticated = !$caResponse->hasError();

        if (isset($this->required_auth) && !$this->required_auth) {
            // 'false' -> available to non-authenticated users only

            if ($this->authenticated) {
                if ($requestIsAJAX) {
                    HttpResponse::send(401, 'Forbidden to authenticated users');
                } else {
                    $error_message = $caResponse->getErrorMessage();
                    $has_error_message = $error_message !== '';
                    CookieMessengerWriter::setMessage(
                        null,
                        $has_error_message,
                        $has_error_message ? 'Non-Authenticated users only' : $error_message
                    );
                    $redirect_to_url = UrlUtils::baseUrl($this->on_error_redirect_to);
                    HttpResponse::redirectTo($redirect_to_url);
                }
            }
        } else {
            // assume 'true'
            // 'true' -> available to authenticated users only
            if (!$this->authenticated) {
                if ($requestIsAJAX) {
                    HttpResponse::send(401, 'Authentication required');
                } else {
                    CookieUtils::deleteAllCookiesExcept(
                        (\env('cleandeck.cookie.prefix', '')) . 'cp_tc_agreed');

                    $error_message = $caResponse->getErrorMessage();
                    CookieMessengerWriter::setMessage(
                        $caResponse->getStatusCode(),
                        true,
                        $error_message === '' ? 'Authenticated users only' : $error_message
                    );
                    $redirect_to_url = UrlUtils::baseUrl($this->on_error_redirect_to);
                    HttpResponse::redirectTo($redirect_to_url);
                }
            }
        }

        return true;
    }

    private function minimalAuth(): CAResponse
    {
        $caRequest = new CARequest();
        return $caRequest
            // no multipart downstream
            ->addHeaders([
                'content-type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            ])
            // Critical!
            ->cacheNoRelay()
            ->exec('GET', '/auth');
    }
}
