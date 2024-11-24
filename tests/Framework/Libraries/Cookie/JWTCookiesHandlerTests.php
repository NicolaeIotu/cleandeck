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

namespace Framework\Libraries\Cookie;

use PHPUnit\Framework\TestCase;

final class JWTCookiesHandlerTests extends TestCase
{
    /**
     * @coversDefaultClass
     */
    public function testDescribeCookie(): void
    {
        $this->assertTrue(is_null(JWTCookiesHandler::describeCookie('missing-cookie-name')));

        $_COOKIE['mocked-valid-cookie'] = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyIjp7ImFjY291' .
            'bnRfcmFuayI6MTAwMDAwLCJ1c2VybmFtZSI6ImFkbWluMTAwayIsImZpcnN0bmFtZSI6IkFkbWluIiwiZW1wbG9' .
            '5ZWVfdHlwZSI6InN1cGVyLWFkbWluIiwibmV3c2xldHRlcnMiOjAsInByb21vdGlvbnMiOjB9LCJpc3MiOiJjbW' .
            'QtYXV0aCIsImlhdCI6MTczMjQ4NTMzNn0.RNwL5QcqWng6DqRjGpILJI3ZKFzGsbfOypx85kynhPFjRMzwJ6cgq' .
            'pSA1gOYQP7CSlebX7OFF3lhEDEUM5GrLdADqb73qVLIpTWbJzTI2XZgW_-FaDXocG9fuoWbWGHOYEOoW2tr8rq2' .
            'SwYcDISPAy-SN8ruPuNIXw-ajwkUNZm0M1lEegdsGg8a60EBzs3BENL_Z530C92XRjYicpOScfoBgs-eAE8Zba1' .
            'QEiIGYR8pKSCS1tUyXKNuCcvTT3QyyRWZsTaUG8l9yFp-EI6OtfA2V9XGoEPB4_YPLq433gOsrnFaY3hKDRhvyM' .
            'JRXCZWZdQhbgWGGMaMQB40eaN_Wg.qpTEsFF5LFTsvGWaynXSX4a%2FXb8oEKUAT4OvVhKFHRc';
        $describedCookie = JWTCookiesHandler::describeCookie('mocked-valid-cookie');
        $this->assertTrue(is_array($describedCookie));
        $this->assertArrayHasKey('user', $describedCookie);
        unset($_COOKIE['mocked-valid-cookie']);
    }

    /**
     * @coversDefaultClass
     */
    public function testDescribeCookieException1(): void
    {
        $_COOKIE['mocked-cookie-name'] = 'mocked.segment-1.segment-2.segment-3';
        $this->expectException(\DomainException::class);
        JWTCookiesHandler::describeCookie('mocked-cookie-name');
        unset($_COOKIE['mocked-cookie-name']);
    }

    /**
     * @coversDefaultClass
     */
    public function testDescribeCookieException2(): void
    {
        $_COOKIE['mocked-cookie-name'] = 'mocked-value';
        $this->expectException(\UnexpectedValueException::class);
        JWTCookiesHandler::describeCookie('mocked-cookie-name');
        unset($_COOKIE['mocked-cookie-name']);
    }

    /**
     * @coversDefaultClass
     */
    public function testDescribeValue(): void
    {
        $validCookieValue = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyIjp7ImFjY291' .
            'bnRfcmFuayI6MTAwMDAwLCJ1c2VybmFtZSI6ImFkbWluMTAwayIsImZpcnN0bmFtZSI6IkFkbWluIiwiZW1wbG9' .
            '5ZWVfdHlwZSI6InN1cGVyLWFkbWluIiwibmV3c2xldHRlcnMiOjAsInByb21vdGlvbnMiOjB9LCJpc3MiOiJjbW' .
            'QtYXV0aCIsImlhdCI6MTczMjQ4NTMzNn0.RNwL5QcqWng6DqRjGpILJI3ZKFzGsbfOypx85kynhPFjRMzwJ6cgq' .
            'pSA1gOYQP7CSlebX7OFF3lhEDEUM5GrLdADqb73qVLIpTWbJzTI2XZgW_-FaDXocG9fuoWbWGHOYEOoW2tr8rq2' .
            'SwYcDISPAy-SN8ruPuNIXw-ajwkUNZm0M1lEegdsGg8a60EBzs3BENL_Z530C92XRjYicpOScfoBgs-eAE8Zba1' .
            'QEiIGYR8pKSCS1tUyXKNuCcvTT3QyyRWZsTaUG8l9yFp-EI6OtfA2V9XGoEPB4_YPLq433gOsrnFaY3hKDRhvyM' .
            'JRXCZWZdQhbgWGGMaMQB40eaN_Wg.qpTEsFF5LFTsvGWaynXSX4a%2FXb8oEKUAT4OvVhKFHRc';
        $describedCookie = JWTCookiesHandler::describeValue($validCookieValue);
        $this->assertTrue(is_array($describedCookie));
        $this->assertArrayHasKey('user', $describedCookie);

        $this->assertTrue(is_null(JWTCookiesHandler::describeValue(null)));
    }
}
