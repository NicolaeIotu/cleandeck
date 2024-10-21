'use strict'

/*
 * CleanDeck for CMD-Auth (https://link133.com) and other similar applications
 *
 * Copyright (c) 2023-2024 Iotu Nicolae, nicolae.g.iotu@link133.com
 * Licensed under the terms of the MIT License (MIT)
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

// eslint-disable-next-line no-unused-vars
export default class Cookie {
    /**
     * @param {string} cookie_name
     * @param {string} cookie_value
     * @param {string|null} cookie_path
     * @param {string|null} cookie_samesite
     * @param {string|null} cookie_domain
     * @param {boolean} secure
     * @param {number|null} expires_timestamp Javascript timestamp in milliseconds i.e. Date.now() + 30 * 24 * 60 * 60 * 1000
     */
    static setCookie(cookie_name, cookie_value,
                     cookie_path = '/', cookie_samesite = 'Lax',
                     cookie_domain = null, secure = true,
                     expires_timestamp = null) {
        let cookie_contents = cookie_name + '=' + cookie_value + ';'
        // cookie_path
        if (cookie_path) {
            cookie_contents += ` Path=${cookie_path};`
        }
        // cookie_samesite
        if (['Strict', 'Lax', 'None'].indexOf(cookie_samesite.toLowerCase()) >= 0) {
            cookie_contents += ` SameSite=${cookie_samesite.toLowerCase()};`
        } else {
            cookie_contents += ' SameSite=lax;'
        }
        // cookie_domain
        if (typeof cookie_domain === 'string' && cookie_domain.length > 0 &&
            !cookie_name.startsWith('__Host-')) {
            cookie_contents += ` Domain=${cookie_domain};`
        }
        //secure
        if (secure) {
            cookie_contents += ' Secure;'
        }
        // expires
        if (expires_timestamp) {
            cookie_contents += ` Expires=${(new Date(expires_timestamp)).toUTCString()};`
        }

        document.cookie = cookie_contents
    }

    static getCookie(cookie_name) {
        return document.cookie
            .split("; ")
            .find((row) => row.startsWith(cookie_name + '='))
            ?.split('=')[1]
    }

    static hasCookie(cookie_name) {
        return document.cookie
            .split("; ")
            .find((row) => row.startsWith(cookie_name + '=')) !== undefined
    }
}

