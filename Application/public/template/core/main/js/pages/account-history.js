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

const enable_email_history = document.getElementById('enable-email-history')
if (enable_email_history instanceof HTMLElement) {
    enable_email_history.onchange = function () {
        const enable = enable_email_history.checked
        for (const elem of document.getElementsByClassName('emails-entry')) {
            elem.classList.add(enable ? 'd-inline-block' : 'd-none')
            elem.classList.remove(enable ? 'd-none' : 'd-inline-block')
        }
        for (const elem of document.getElementsByClassName('emails-data')) {
            if(enable) {
                elem.classList.remove('d-none')
            } else {
                elem.classList.add('d-none')
            }
        }
    }
}
const enable_user_details_history = document.getElementById('enable-user-details-history')
if (enable_user_details_history instanceof HTMLElement) {
    enable_user_details_history.onchange = function () {
        const enable = enable_user_details_history.checked
        for (const elem of document.getElementsByClassName('user-details-entry')) {
            elem.classList.add(enable ? 'd-inline-block' : 'd-none')
            elem.classList.remove(enable ? 'd-none' : 'd-inline-block')
        }
        for (const elem of document.getElementsByClassName('user-details-data')) {
            if(enable) {
                elem.classList.remove('d-none')
            } else {
                elem.classList.add('d-none')
            }
        }
    }
}
