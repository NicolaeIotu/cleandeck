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

final class CAResponse
{
    private readonly int $status_code;

    private readonly string $body;

    /**
     * @var array<string, mixed>
     */
    private array $headers;

    /**
     * @param array<string, mixed> $ca_response_headers
     */
    public function __construct(
        int    $ca_response_status_code = 0,
        string $ca_response_body = '',
        array  $ca_response_headers = []
    ) {
        $this->status_code = $ca_response_status_code;
        $this->body = $ca_response_body;
        $this->headers = $ca_response_headers;
    }

    public function getStatusCode(): int
    {
        return $this->status_code;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return array<string, mixed>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $header_name): mixed
    {
        if (self::hasHeader($header_name)) {
            return $this->headers[$header_name];
        }
        return null;
    }

    public function hasHeader(string $header_name): bool
    {
        return \array_key_exists($header_name, $this->headers);
    }

    public function hasError(): bool
    {
        return $this->status_code > 299;
    }

    public function getErrorMessage(): string
    {
        return $this->body;
    }
}
