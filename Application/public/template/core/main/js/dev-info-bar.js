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

function xtypeof(o) {
    return Object.prototype.toString.call(o).slice(8, -1).toLowerCase()
}

const show_hide_btn = document.getElementById('show-hide-btn')
const dev_info_content = document.getElementById('dev-info-content')

const exp_month_ms = Date.now() + 30 * 24 * 60 * 60 * 1000
const b64_cd = show_hide_btn.getAttribute('data-cd')
const je_cd = atob(b64_cd)
const cookie_details = JSON.parse(je_cd)

const valid_cookie_details = xtypeof(cookie_details) === 'object' &&
    Object.prototype.hasOwnProperty.call(cookie_details, 'full_name') &&
    typeof cookie_details['full_name'] === 'string'

if (valid_cookie_details) {
    const dev_info_bar_cookie_name = cookie_details['full_name']

    // eslint-disable-next-line no-undef
    if (!Cookie.hasCookie(dev_info_bar_cookie_name)) {
        // eslint-disable-next-line no-undef
        Cookie.setCookie(dev_info_bar_cookie_name, '1',
            cookie_details['path'], cookie_details['samesite'],
            cookie_details['domain'], cookie_details['secure'],
            exp_month_ms)
    }

    show_hide_btn.onclick = function () {
        const classList = dev_info_content.classList
        if (classList.contains('d-none')) {
            // eslint-disable-next-line no-undef
            Cookie.setCookie(dev_info_bar_cookie_name, '1',
                cookie_details['path'], cookie_details['samesite'],
                cookie_details['domain'], cookie_details['secure'],
                exp_month_ms)
            show_hide_btn.textContent = show_hide_btn.textContent.replace('Show', 'Hide')
            classList.remove('d-none')
        } else {
            // eslint-disable-next-line no-undef
            Cookie.setCookie(dev_info_bar_cookie_name, '0',
                cookie_details['path'], cookie_details['samesite'],
                cookie_details['domain'], cookie_details['secure'],
                exp_month_ms)
            show_hide_btn.textContent = show_hide_btn.textContent.replace('Hide', 'Show')
            classList.add('d-none')
        }
    }
} else {
    console.error('Invalid dev-info-bar cookie details')
}
