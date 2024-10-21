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

// Handles both login and signup using Google OAuth2.
// https://developers.google.com/identity/protocols/oauth2/web-server

namespace Framework\Controllers\Main;

use Framework\Libraries\CA\CARequest;
use Framework\Libraries\Cookie\CookieMessengerWriter;
use Framework\Libraries\Email\EmailTemplates;
use Framework\Libraries\Email\SignupEmail;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\UrlUtils;

final class Oauth2GoogleController
{
    // Adjust for your own application. Simple custom codes for various operations.
    // These will help identify the type operation.
    private const OP_TYPE_LOGIN = 'HrFFz6';

    private const OP_TYPE_SIGNUP = 'ObpKPV';

    // The calls and responses to/from Google OAuth2 can be simulated in which case all calls will
    //   return successful responses using a fake test account 'cleandeck.oauth2_google.local_development_account' (file .env.ini).
    // In order to trigger this behavior set 'cleandeck.oauth2_google.local_development' to true (file .env.ini).
    private bool $local_development = false;


    public function __construct()
    {
        if (\env('cleandeck.ENVIRONMENT', 'production') === 'production') {
            return;
        }
        if (\env('cleandeck.oauth2_google.local_development') !== true) {
            return;
        }
        $this->local_development = true;
    }

    private function getBaseAuthorizationUrl(string $encoded_operation_type): string
    {
        $base_authorization_url = 'https://accounts.google.com/o/oauth2/v2/auth';
        return $base_authorization_url . ('?' . \http_build_query([
                    'client_id' => \env('cleandeck.oauth2_google.client_id'),
                    'redirect_uri' => \env('cleandeck.oauth2_google.redirect_uri'),
                    'access_type' => 'online',
                    'prompt' => 'select_account',
                    'response_type' => 'code',
                    'scope' => 'openid email', // profile, openid, email
                    'state' => $encoded_operation_type,
                ]));
    }

    public function google_oauth(): void
    {
        if (!isset($_GET['ot']) ||
            ($_GET['ot'] !== self::OP_TYPE_LOGIN &&
                $_GET['ot'] !== self::OP_TYPE_SIGNUP)) {
            CookieMessengerWriter::setMessage(404, true, 'Page not found');
            HttpResponse::redirectTo(UrlUtils::baseUrl());
            return;
        }

        if ($this->local_development) {
            $ldUrl = UrlUtils::baseUrl('/google-oauth/cb' .
                '?state=' . $_GET['ot'] .
                '&code=4%2F0AfJohXmQSWDn43Ht9CP3dzOsGDFZQSpUUvB59ijVFxaABCOT7A7RB8CCPioACp04X4GZgw' .
                '&scope=email+openid+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email&authuser=0&prompt=none');
            HttpResponse::redirectTo($ldUrl);
        } else {
            $baseAuthorizationUrl = $this->getBaseAuthorizationUrl($_GET['ot']);
            HttpResponse::redirectTo($baseAuthorizationUrl);
        }
    }

    /**
     * @throws \Exception
     */
    private function requestOAuthAccessToken(string $authorization_code): bool|string
    {
        $curl_opts = [
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => 'https://oauth2.googleapis.com/token',
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_POSTFIELDS => \http_build_query([
                'client_id' => \env('cleandeck.oauth2_google.client_id'),
                'client_secret' => \env('cleandeck.oauth2_google.client_secret'),
                'code' => $authorization_code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => \env('cleandeck.oauth2_google.redirect_uri'),
            ]),
        ];

        $ch = \curl_init();
        \curl_setopt_array($ch, $curl_opts);
        $result = \curl_exec($ch);
        if ($result === false || $result === '' || $result === '0') {
            throw new \Exception(\curl_error($ch));
        }

        \curl_close($ch);

        return $result;
    }

    private function performQuickCAAuthOperation(string $email, string $redirect_on_error_url): void
    {
        if (!isset($_GET['state'])) {
            return;
        }

        $state = $_GET['state'];
        switch ($state) {
            case self::OP_TYPE_SIGNUP:
                $ca_call_method = 'PUT';
                $ca_call_url = '/signup/quick';
                $ca_call_body = [
                    'email' => $email,
                    'agree_tc' => 1,
                    'agree_cookies' => 1,
                    'agree_privacy' => 1,
                ];
                $redirect_on_success_url = UrlUtils::baseUrl('/login');
                break;
            case self::OP_TYPE_LOGIN:
                $ca_call_method = 'PUT';
                $ca_call_url = '/login/quick';
                $ca_call_body = ['email' => $email];
                $redirect_on_success_url = UrlUtils::baseUrl('/user');
                break;
            default:
                return;
        }

        $caRequest = new CARequest();
        $caResponse = $caRequest
            ->setBody($ca_call_body)
            ->exec($ca_call_method, $ca_call_url);
        if ($caResponse->hasError()) {
            CookieMessengerWriter::setMessage(
                $caResponse->getStatusCode(),
                true,
                $caResponse->getBody()
            );
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        if (\env('cleandeck.oauth2_google.local_development') === true) {
            CookieMessengerWriter::setMessage(
                null,
                false,
                '<span class="underline">Google OAuth2 Local development mode is active!</span>' . PHP_EOL .
                ($_GET['state'] === self::OP_TYPE_LOGIN ? 'Log in' : 'Sign up') . ' performed automatically using account ' .
                '<em>' . \env('cleandeck.oauth2_google.local_development_account') . '</em>.' . PHP_EOL .
                'See file <em>.env.ini</em> and related settings in section <em>cleandeck.oauth2_google.local_development...</em>.'
            );
        } else {
            // live Google Oauth2 applications
            if ($state === self::OP_TYPE_SIGNUP) {
                // self::OP_TYPE_SIGNUP
                $response_body = $caResponse->getBody();
                $response_body_array = \json_decode($response_body, true, 2);
                if (!isset($response_body_array, $response_body_array['generated_password']) ||
                    !\is_string($response_body_array['generated_password']) ||
                    \strlen($response_body_array['generated_password']) < 6) {
                    // invalid response body
                    CookieMessengerWriter::setMessage(
                        null,
                        true,
                        'Invalid signup/response',
                        $_POST
                    );
                    HttpResponse::redirectTo(UrlUtils::baseUrl());
                    return;
                }


                $email_content = EmailTemplates::buildEmail(
                    EmailTemplates::AUTO_SIGNUP,
                    $response_body_array['generated_password']
                );

                // send AWS SES email
                try {
                    $info_message = SignupEmail::send(
                        $email,
                        'Fresh Account Details',
                        $email_content
                    );
                    $sendEmailResult = true;
                } catch (\Exception $exception) {
                    $info_message = $exception->getMessage();
                    $sendEmailResult = false;
                }

                CookieMessengerWriter::setMessage(
                    null,
                    $sendEmailResult === false,
                    nl2br($info_message),
                    $sendEmailResult === false ? $_POST : null
                );

                if (!$sendEmailResult) {
                    HttpResponse::redirectTo(UrlUtils::baseUrl('/contact'));
                    return;
                }
            }
            // For the other case self::OP_TYPE_LOGIN, there is nothing else
            //  remaining to be done even if MFA is enabled.
        }

        HttpResponse::redirectTo($redirect_on_success_url);
    }


    private function accessTokenHandler(string $authorization_code, string $redirect_on_error_url): void
    {
        if ($this->local_development) {
            $this->performQuickCAAuthOperation(\env('cleandeck.oauth2_google.local_development_account'), $redirect_on_error_url);
            return;
        }

        // ////////////////// GET ACCESS TOKEN ////////////////////
        try {
            $access_token_request_result = $this->requestOAuthAccessToken($authorization_code);
        } catch (\Exception $exception) {
            CookieMessengerWriter::setMessage(null, true, $exception->getMessage());
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        if (!\is_string($access_token_request_result)) {
            CookieMessengerWriter::setMessage(null, true, 'Token request error');
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }


        $token_array = \json_decode($access_token_request_result, true);
        if (!isset($token_array,
            $token_array['access_token'],
            $token_array['id_token'])) {
            // $token errors
            CookieMessengerWriter::setMessage(403, true, 'Invalid token (1). Please try again.');
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        // ////////////////// GET OPENID DETAILS ////////////////////
        $encoded_jwt_array = \explode('.', (string)$token_array['id_token']);
        if (\count($encoded_jwt_array) < 2) {
            CookieMessengerWriter::setMessage(403, true, 'Invalid token (2). Please try again.');
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        $payload = \base64_decode($encoded_jwt_array[1]);
        if (!\is_string($payload)) {
            CookieMessengerWriter::setMessage(403, true, 'Invalid token (3). Please try again.');
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        $payload_array = \json_decode($payload, true);
        if (!isset($payload_array, $payload_array['email'])) {
            CookieMessengerWriter::setMessage(403, true, 'Cannot retrieve a valid email. Please try again.');
            HttpResponse::redirectTo($redirect_on_error_url);
            return;
        }

        $this->performQuickCAAuthOperation($payload_array['email'], $redirect_on_error_url);
    }


    public function google_oauth_callback(): void
    {
        if (isset($_GET['error'])) {
            // all Google errors go here
            CookieMessengerWriter::setMessage(null, true, $_GET['error']);
            HttpResponse::redirectTo(UrlUtils::baseUrl());
        } else {
            // This is a response to a request for authorization code
            if (isset($_GET['state'])) {
                $state = $_GET['state'];

                if ($state !== self::OP_TYPE_LOGIN &&
                    $state !== self::OP_TYPE_SIGNUP) {
                    CookieMessengerWriter::setMessage(404, true, 'Page not found');
                    HttpResponse::redirectTo(UrlUtils::baseUrl());
                    return;
                }

                $redirect_on_error_url = UrlUtils::baseUrl($state === self::OP_TYPE_LOGIN ? '/login' : '/signup');

                if (!isset($_GET['code'])) {
                    CookieMessengerWriter::setMessage(
                        null,
                        true,
                        'Invalid Google authentication response. Please try again.'
                    );
                    HttpResponse::redirectTo($redirect_on_error_url);
                    return;
                }

                $this->accessTokenHandler($_GET['code'], $redirect_on_error_url);
            } else {
                CookieMessengerWriter::setMessage(404, true, 'Page not found');
                HttpResponse::redirectTo(UrlUtils::baseUrl());
            }
        }
    }
}
