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

import Cookie from '../Cookie.js'

const expYearMs = Date.now() + 365 * 24 * 60 * 60 * 1000

function xtypeof (o) {
  return Object.prototype.toString.call(o).slice(8, -1).toLowerCase()
}

document.getElementById('privacy_cookies_btn').onclick = function () {
  const b64Cd = this.getAttribute('data-cd')
  const jeCd = atob(b64Cd)
  const cookieDetails = JSON.parse(jeCd)

  const validCookieDetails = xtypeof(cookieDetails) &&
    Object.prototype.hasOwnProperty.call(cookieDetails, 'full_name') &&
    typeof cookieDetails.full_name === 'string'

  if (validCookieDetails) {
    Cookie.setCookie(cookieDetails.full_name, 'true',
      cookieDetails.path, cookieDetails.samesite,
      cookieDetails.domain, cookieDetails.secure,
      expYearMs)
  } else {
    console.error('Invalid cp_tc cookie details')
  }
}

const cookiesAgreedElement = document.getElementById('cookies_agreed')
if (cookiesAgreedElement) {
  // Bootstrap js is always loaded in header.php
  // eslint-disable-next-line no-undef
  const cookiesAgreedModal = new bootstrap.Modal('#cookies_agreed', {
    backdrop: 'static',
    keyboard: true,
    focus: true
  })
  cookiesAgreedModal.show(cookiesAgreedElement)
}
