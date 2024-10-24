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
   * @param {string} cookieName
   * @param {string} cookieValue
   * @param {string|null} cookiePath
   * @param {string|null} cookieSamesite
   * @param {string|null} cookieDomain
   * @param {boolean} secure
   * @param {number|null} expiresTimestamp Javascript timestamp in milliseconds i.e. Date.now() + 30 * 24 * 60 * 60 * 1000
   */
  static setCookie (cookieName, cookieValue, cookiePath = '/',
    cookieSamesite = 'Lax', cookieDomain = null, secure = true,
    expiresTimestamp = null) {
    let cookieContents = cookieName + '=' + cookieValue + ';'
    // cookie_path
    if (cookiePath) {
      cookieContents += ` Path=${cookiePath};`
    }
    // cookieSamesite
    if (['Strict', 'Lax', 'None'].indexOf(cookieSamesite.toLowerCase()) >= 0) {
      cookieContents += ` SameSite=${cookieSamesite.toLowerCase()};`
    } else {
      cookieContents += ' SameSite=lax;'
    }
    // cookieDomain
    if (typeof cookieDomain === 'string' && cookieDomain.length > 0 && !cookieName.startsWith('__Host-')) {
      cookieContents += ` Domain=${cookieDomain};`
    }
    // secure
    if (secure) {
      cookieContents += ' Secure;'
    }
    // expires
    if (expiresTimestamp) {
      cookieContents += ` Expires=${(new Date(expiresTimestamp)).toUTCString()};`
    }

    document.cookie = cookieContents
  }

  static getCookie (cookieName) {
    return document.cookie
      .split('; ')
      .find((row) => row.startsWith(cookieName + '='))
      ?.split('=')[1]
  }

  static hasCookie (cookieName) {
    return document.cookie
      .split('; ')
      .find((row) => row.startsWith(cookieName + '=')) !== undefined
  }
}
