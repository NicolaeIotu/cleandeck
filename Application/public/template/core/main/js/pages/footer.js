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

const exp_year_ms = Date.now() + 365 * 24 * 60 * 60 * 1000

function xtypeof(o) {
    return Object.prototype.toString.call(o).slice(8, -1).toLowerCase()
}

document.getElementById('privacy_cookies_btn').onclick = function () {
    const b64_cd = this.getAttribute('data-cd')
    const je_cd = atob(b64_cd)
    const cookie_details = JSON.parse(je_cd)

    const valid_cookie_details = xtypeof(cookie_details) &&
        Object.prototype.hasOwnProperty.call(cookie_details, 'full_name') &&
        typeof cookie_details['full_name'] === 'string'

    if (valid_cookie_details) {
        Cookie.setCookie(cookie_details['full_name'], 'true',
            cookie_details['path'], cookie_details['samesite'],
            cookie_details['domain'], cookie_details['secure'],
            exp_year_ms)
    } else {
        console.error('Invalid cp_tc cookie details')
    }
}

const cookies_agreed_element = document.getElementById('cookies_agreed')
if (cookies_agreed_element) {
    // Bootstrap js is always loaded in header.php
    // eslint-disable-next-line no-undef
    const cookies_agreed_modal = new bootstrap.Modal('#cookies_agreed', {
        backdrop: 'static',
        keyboard: true,
        focus: true
    })
    cookies_agreed_modal.show(cookies_agreed_element)
}
