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

use Framework\Libraries\CSRF\CSRFConstants;
use Framework\Libraries\Http\HttpRequest;
use Framework\Libraries\Http\HttpResponse;
use Framework\Libraries\Utils\UrlUtils;

final class CARequest
{
    /**
     * @var array<string, string>
     */
    private array $headers = [];

    private string $query;

    /**
     * @var array<string, mixed>
     */
    private array $body = [];

    private string $response_raw;

    private bool $LOCKED = false;

    private CAResponse $caResponse;

    private bool $has_session_no_update = false;

    private bool $cache_no_relay = false;

    private static ?\CurlHandle $curlHandle;

    private readonly bool $is_http2;

    /**
     * @example $ca_request = new CARequest();
     *          $ca_response = $ca_request->exec(...);
     */
    public function __construct()
    {
        $this->is_http2 = \env('cleandeck.http_version') === 'HTTP/2';
    }

    /**
     * Adds the header 'session-no-update' recognized by CMD-Auth.
     * This application uses CURL capabilities in order to handle multiple backend requests.
     * This function is a reminder that CMD-Auth allows the use of header 'session-no-update'
     *  which guarantees that the request will not trigger the update of the session.
     */
    public function sessionNoUpdate(): CARequest
    {
        if (! $this->LOCKED) {
            $this->headers['session-no-update'] = '1';
            $this->has_session_no_update = true;
        }

        return $this;
    }

    /**
     * Prevents relay of header 'cache-control' received from CMD-Auth.
     * @return $this
     */
    public function cacheNoRelay(): CARequest
    {
        if (! $this->LOCKED) {
            $this->cache_no_relay = true;
        }

        return $this;
    }

    /**
     * These fields are not required by, and for the sake of efficiency should not be passed downstream to CMD-Auth.
     * For efficiency, filtering is only applied to body.
     * @var array<string>
     */
    private array $non_ca_fields = [
        CSRFConstants::CSRF_TOKEN,
        'captcha_code',
    ];

    /**
     * Overwrites queries specified through URL.
     * @param array<string, mixed>|string $query
     * @return $this
     */
    public function setQuery(array|string $query): CARequest
    {
        if ($this->LOCKED) {
            return $this;
        }

        if (\is_string($query)) {
            $this->query = \ltrim($query, '?');
        } elseif (\is_array($query)) {
            $this->query = \http_build_query($query);
        }

        // CMD-Auth requires precise field names!
        $this->query = preg_replace('/%5B\d+%5D/', '', $this->query);

        return $this;
    }

    /**
     * @param array<string, mixed>|string $body
     * @return $this
     */
    public function setBody(array|string $body): CARequest
    {
        if ($this->LOCKED) {
            return $this;
        }

        if (\is_string($body)) {
            \parse_str($body, $this->body);
        } else {
            $this->body = $body;
        }

        // Some fields which are introduced by Framework are actually
        // not required downstream. Eliminate these fields.
        $this->body = \array_filter(
            $this->body,
            function ($key): bool {
                return ! \in_array($key, $this->non_ca_fields);
            },
            ARRAY_FILTER_USE_KEY
        );

        return $this;
    }

    /**
     * @param array<string, mixed> $additional_headers
     * @return $this
     */
    public function addHeaders(array $additional_headers): CARequest
    {
        if (! $this->LOCKED) {
            $this->headers = [...$this->headers, ...$this->array_lowercase_keys($additional_headers)];
        }

        return $this;
    }

    private const ORIGIN_METHODS = ['DELETE', 'OPTIONS', 'PATCH', 'POST', 'PUT'];

    /**
     * @param string $ca_url Method 'setQuery' overwrites queries specified through URL.
     */
    public function exec(string $ca_method, string $ca_url): CAResponse
    {
        if ($this->LOCKED) {
            return $this->caResponse;
        }

        try {
            $this->populateHeaders(HttpRequest::getHeaders(), HttpRequest::getMethod(), UrlUtils::current_url());
        } catch (\Exception $exception) {
            $this->LOCKED = true;
            $this->caResponse = new CAResponse(
                $exception->getCode(),
                $exception->getMessage()
            );
            return $this->caResponse;
        }

        $ca_method = \strtoupper($ca_method);

        if (\in_array($ca_method, self::ORIGIN_METHODS)) {
            if (! isset($this->headers['origin'])) {
                $this->headers['origin'] = (string)\env('cleandeck.baseURL');
            }
        }

        $curl_options = $this->buildCurlOptions($ca_url, $ca_method);

        if (isset(self::$curlHandle)) {
            \curl_reset(self::$curlHandle);
        } else {
            self::$curlHandle = \curl_init();
        }
        \curl_setopt_array(self::$curlHandle, $curl_options);
        $curl_response = \curl_exec(self::$curlHandle);
        // When CURLOPT_RETURNTRANSFER is set to 'true', \curl_exec() will return bool(true) for empty responses

        $this->LOCKED = true;

        if (\curl_errno(self::$curlHandle) !== 0) {
            $curl_error_description = \curl_strerror(\curl_errno(self::$curlHandle)) .
                ' (' . \curl_error(self::$curlHandle) . ')';
            if (\env('cleandeck.ENVIRONMENT') === 'development') {
                $this->response_raw = 'Backend error <strong>' .
                    \env('cleandeck.authURL', 'Missing authURL') . '</strong>: ' . $curl_error_description . PHP_EOL . PHP_EOL .
                    'For connectivity issues check/adjust setting <strong>cleandeck.authURL</strong> in file <strong>.env.ini</strong> ' .
                    'and test the availability of the backend server ' . PHP_EOL .
                    '(i.e. <strong>curl -k ' . \env('cleandeck.authURL', 'missing__authURL') . '/machine/status</strong>) a.o.';
            } else {
                $this->response_raw = '[downstream error] ' . $curl_error_description;
            }

            $this->caResponse = new CAResponse(502, $this->response_raw);
        } else {
            if (\is_string($curl_response)) {
                $this->response_raw = $curl_response;
            }

            $this->buildResponse();
        }

        // IMPORTANT!
        $response_headers = $this->caResponse->getHeaders();
        if (! $this->has_session_no_update) {
            HttpResponse::addRemoteSetCookieHeaders($response_headers);
        }

        if (! $this->cache_no_relay) {
            if (isset($response_headers['cache-control'])) {
                \header('cache-control: ' . $response_headers['cache-control'][0], true);
            }
        }

        if (isset($response_headers['x-cdc-engine'])) {
            \header('X-CDC-Engine: ' . $response_headers['x-cdc-engine'][0], true);
        }

        return $this->caResponse;
    }

    public function completed(): bool
    {
        return $this->LOCKED;
    }

    public function getResponse(): CAResponse
    {
        return $this->caResponse;
    }

    public function __toString(): string
    {
        return $this->response_raw;
    }

    private function buildResponse(): void
    {
        $ca_response_status_code = 0;
        $ca_response_body = '';
        $ca_response_headers = [];

        if (! isset($this->response_raw)) {
            $this->caResponse = new CAResponse();
            return;
        }

        $headers_end = false;

        $response_raw_array = \explode(PHP_EOL, $this->response_raw);
        $rra_length = \count($response_raw_array);
        for ($i = 0; $i < $rra_length; ++$i) {
            $raw_line = \trim($response_raw_array[$i]);

            if ($i === 0) {
                $sc_array = \explode(' ', $raw_line);
                if (\count($sc_array) > 1) {
                    $ca_response_status_code = (int)$sc_array[1];
                }

                continue;
            }

            if ($raw_line === '') {
                $headers_end = true;
            } else {
                if ($headers_end) {
                    // Append to body
                    if (\strlen($ca_response_body) > 0) {
                        $ca_response_body .= PHP_EOL;
                    }

                    $ca_response_body .= $raw_line;
                } else {
                    // Filter and append to headers
                    $raw_line_header_array = \explode(':', $raw_line, 2);
                    $header_name = \trim($raw_line_header_array[0]);
                    if (\strlen($header_name) > 0) {
                        $header_value = \trim($raw_line_header_array[1]);
                        if (\strlen($header_value) > 0) {
                            // provide for multiple headers with the same name
                            if (! isset($ca_response_headers[$header_name])) {
                                $ca_response_headers[$header_name] = [];
                            }

                            $ca_response_headers[$header_name][] = $header_value;
                        }
                    }
                }
            }
        }

        $this->caResponse = new CAResponse(
            $ca_response_status_code,
            $ca_response_body,
            $ca_response_headers
        );
    }

    public static function authUrl(string $path = null): string
    {
        $env_authURL = \env('cleandeck.authURL');
        if (\is_string($env_authURL)) {
            $result = \rtrim($env_authURL, '/');
            if (isset($path)) {
                $result .= '/' . \ltrim($path, '/');
            }

            return $result;
        }
        return 'missing__.env.ini__cleandeck.authURL';
    }

    /**
     * @return array<int, mixed>
     */
    private function buildCurlOptions(string $ca_url, string $ca_method): array
    {
        $curl_options = [
            CURLOPT_HEADER => true,
            CURLOPT_FRESH_CONNECT => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS => 0,
            CURLOPT_FAILONERROR => false,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_ENCODING => '',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            // Critical!
            // In memory CURLOPT_COOKIEFILE
            CURLOPT_COOKIEFILE => '',
            // Critical!
            // In memory CURLOPT_COOKIEJAR
            CURLOPT_COOKIEJAR => '',
        ];

        if ($this->is_http2) {
            // CMD-Auth supports http2 over a secure connection only.
            $curl_options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_2TLS;
        } else {
            $curl_options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
        }

        $curl_options[CURLOPT_CUSTOMREQUEST] = $ca_method;
        if ($ca_method === 'HEAD') {
            $curl_options[CURLOPT_NOBODY] = true;
        }

        // Create a valid auth URL if not valid already.
        $parsed_ca_url = \parse_url($ca_url);
        if (\is_array($parsed_ca_url)) {
            if (isset($parsed_ca_url['path'])) {
                if (! isset($parsed_ca_url['scheme'], $parsed_ca_url['host'], $parsed_ca_url['port'])) {
                    if (isset($parsed_ca_url['query']) && ! isset($this->query)) {
                        $this->query = $parsed_ca_url['query'];
                    }

                    // build auth url
                    $real_url = self::authUrl($parsed_ca_url['path']);
                }
            }
        }

        if (! isset($real_url)) {
            // try to use the URL as provided
            $real_url = $ca_url;
        }


        if (isset($this->query)) {
            $curl_options[CURLOPT_URL] = $real_url . '?' . $this->query;
        } else {
            $curl_options[CURLOPT_URL] = $real_url;
        }

        if (isset($this->body)) {
            // CMD-Auth requires precise field names!
            $conv_body = \http_build_query($this->body);
            $conv_body = (string)\preg_replace('/%5B\d+%5D/', '', $conv_body);
            $curl_options[CURLOPT_POSTFIELDS] = $conv_body;
            $this->headers['content-length'] = \strlen($conv_body);
        }

        $curl_options[CURLOPT_HTTPHEADER] = $this->formatCurloptHeaders();

        return $curl_options;
    }

    /**
     * @return string[]
     */
    private function formatCurloptHeaders(): array
    {
        $formatted = [];
        foreach ($this->headers as $k => $v) {
            $formatted[] = $k . ': ' . $v;
        }

        return $formatted;
    }

    /**
     * @param array<string, string> $upstream_request_headers
     */
    private function populateHeaders(array $upstream_request_headers, string $method, string $url): void
    {
        // Basic checks

        // Populate
        // Use only lower case header names.
        foreach ($upstream_request_headers as $hdr_name => $hdr_value) {
            $hdr_name_lc = \strtolower($hdr_name);
            if (! isset($this->headers[$hdr_name_lc])) {
                $this->headers[$hdr_name_lc] = \ltrim($hdr_value);
            }
        }

        // Important! CMD-Auth does not handle multipart requests.
        // Notify the developer.
        if (isset($this->headers['content-type']) &&
            \stripos($this->headers['content-type'], 'multipart') !== false) {
            $err_msg = '[CMD-Auth pre-request error - ' . $method . ' ' . $url .
                '] No multipart requests allowed downstream (' .
                $method . ' ' . $url . '). Action: unset "content-type" header.';
            error_log($err_msg);
            syslog(LOG_ERR, $err_msg);
            unset($this->headers['content-type']);
        }

        // Remove headers which may be used by CMD-Auth to define a request as coming from external IPs.
        // By default, CMD-Auth blocks requests from external IPs!
        unset($this->headers['x-forwarded-for']);


        // this can be improved
        $this->headers['x-client-ip'] = HttpRequest::getIP();


        // CMD-Auth proxy headers are not used by this application.
        // Do not include them in downstream requests:
        //   'proxy-pass' or 'x-proxy-pass' - used to proxy a request
        //   'proxy-pass-if-authenticated' or 'x-proxy-pass-if-authenticated' - used to proxy a request only if the user is authenticated
        // Adjust as required by your application.
        $excluded_headers = [
            // not used (forbidden) by CleanDeck
            'proxy-pass',
            'x-proxy-pass',
            'proxy-pass-if-authenticated',
            'x-proxy-pass-if-authenticated',
        ];
        // Not allowed by HTTP2
        if ($this->is_http2) {
            $excluded_headers[] = 'connection';
            $excluded_headers[] = 'keep-alive';
            $excluded_headers[] = 'proxy-connection';
            $excluded_headers[] = 'transfer-encoding';
            $excluded_headers[] = 'upgrade';
        }

        $this->headers = \array_filter($this->headers, static function ($k) use ($excluded_headers): bool {
            return (! \in_array($k, $excluded_headers, true));
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function array_lowercase_keys(array $data): array
    {
        $data_keys = [];
        $data_values = [];

        foreach ($data as $key => $value) {
            $data_keys[] = \strtolower($key);
            $data_values[] = $value;
        }

        return \array_combine($data_keys, $data_values);
    }
}
