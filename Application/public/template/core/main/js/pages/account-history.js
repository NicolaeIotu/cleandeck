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

/* global HTMLElement */

const enableEmailHistory = document.getElementById('enable-email-history')
if (enableEmailHistory instanceof HTMLElement) {
  enableEmailHistory.onchange = function () {
    const enable = enableEmailHistory.checked
    for (const elem of document.getElementsByClassName('emails-entry')) {
      elem.classList.add(enable ? 'd-inline-block' : 'd-none')
      elem.classList.remove(enable ? 'd-none' : 'd-inline-block')
    }
    for (const elem of document.getElementsByClassName('emails-data')) {
      if (enable) {
        elem.classList.remove('d-none')
      } else {
        elem.classList.add('d-none')
      }
    }
  }
}
const enableUserDetailsHistory = document.getElementById('enable-user-details-history')
if (enableUserDetailsHistory instanceof HTMLElement) {
  enableUserDetailsHistory.onchange = function () {
    const enable = enableUserDetailsHistory.checked
    for (const elem of document.getElementsByClassName('user-details-entry')) {
      elem.classList.add(enable ? 'd-inline-block' : 'd-none')
      elem.classList.remove(enable ? 'd-none' : 'd-inline-block')
    }
    for (const elem of document.getElementsByClassName('user-details-data')) {
      if (enable) {
        elem.classList.remove('d-none')
      } else {
        elem.classList.add('d-none')
      }
    }
  }
}
