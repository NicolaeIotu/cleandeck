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

import Cookie from './Cookie.js'

function xtypeof (o) {
  return Object.prototype.toString.call(o).slice(8, -1).toLowerCase()
}

const showHideBtn = document.getElementById('show-hide-btn')
const devInfoContent = document.getElementById('dev-info-content')

const expMonthMs = Date.now() + 30 * 24 * 60 * 60 * 1000
const b64Cd = showHideBtn.getAttribute('data-cd')
const jeCd = atob(b64Cd)
const cookieDetails = JSON.parse(jeCd)

const validCookieDetails = xtypeof(cookieDetails) === 'object' &&
  Object.prototype.hasOwnProperty.call(cookieDetails, 'full_name') &&
  typeof cookieDetails.full_name === 'string'

if (validCookieDetails) {
  const devInfoBarCookieName = cookieDetails.full_name

  // eslint-disable-next-line no-undef
  if (!Cookie.hasCookie(devInfoBarCookieName)) {
    // eslint-disable-next-line no-undef
    Cookie.setCookie(devInfoBarCookieName, '1',
      cookieDetails.path, cookieDetails.samesite,
      cookieDetails.domain, cookieDetails.secure,
      expMonthMs)
  }

  showHideBtn.onclick = function () {
    const classList = devInfoContent.classList
    if (classList.contains('d-none')) {
      // eslint-disable-next-line no-undef
      Cookie.setCookie(devInfoBarCookieName, '1',
        cookieDetails.path, cookieDetails.samesite,
        cookieDetails.domain, cookieDetails.secure,
        expMonthMs)
      showHideBtn.textContent = showHideBtn.textContent.replace('Show', 'Hide')
      classList.remove('d-none')
    } else {
      // eslint-disable-next-line no-undef
      Cookie.setCookie(devInfoBarCookieName, '0',
        cookieDetails.path, cookieDetails.samesite,
        cookieDetails.domain, cookieDetails.secure,
        expMonthMs)
      showHideBtn.textContent = showHideBtn.textContent.replace('Hide', 'Show')
      classList.add('d-none')
    }
  }
} else {
  console.error('Invalid dev-info-bar cookie details')
}
