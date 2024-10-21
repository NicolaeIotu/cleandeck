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

const captcha_holder = document.getElementById('captcha_holder')
const captcha_image = document.getElementById('captcha_image')
const captcha_code = document.getElementById('captcha_code')
const reload_errors = document.getElementById('reload_errors')
const captcha_reload = document.getElementById('captcha_reload')
const reload_spinner = document.getElementById('reload_spinner')
const cc_suffix_element = document.getElementById('cc_suffix')

const cc_suffix = cc_suffix_element.value

const captcha_width = captcha_image.getAttribute('width')
const captcha_height = captcha_image.getAttribute('height')
const captcha_reload_url = captcha_reload.getAttribute('data-url')
captcha_reload.removeAttribute('data-url')

const captcha_reload_interval_raw = parseInt(captcha_reload.getAttribute('data-ri'), 10)
let captcha_reload_interval_ms
if (Number.isFinite(captcha_reload_interval_raw)) {
    captcha_reload_interval_ms = Math.max(55 * 1000,
        Math.min(30 * 60 * 1000, captcha_reload_interval_raw * 1000))
} else {
    captcha_reload_interval_ms = 295 * 1000
}

let reload_in_progress = false

captcha_image.onerror = function () {
    reload_errors.textContent = 'Cannot load captcha image! Please try again.'
}

function reloadActions(start_reload) {
    reload_in_progress = start_reload

    if (start_reload) {
        captcha_code.setAttribute('disabled', 'disabled')
    } else {
        captcha_code.removeAttribute('disabled')
    }

    const target1 = start_reload ? captcha_reload : reload_spinner
    const target2 = start_reload ? reload_spinner : captcha_reload

    target2.classList.remove('d-none')
    target2.classList.add('d-inline-block')
    target1.classList.remove('d-inline-block')
    target1.classList.add('d-none')
}

function getCaptchaURL(captcha_url, query) {
    return captcha_url + '?' +
        'cc_suffix=' + (query.cc_suffix || '') +
        '&width=' + query.width +
        '&height=' + query.height +
        '&' + Date.now()
}

async function reloadCaptcha(width, height, cc_suffix, captcha_url) {
    if (reload_in_progress) return

    captcha_code.value = ''
    reloadActions(true)

    const headers = new Headers()
    headers.append('X-Requested-With', 'XMLHttpRequest')

    const query = {
        cc_suffix: cc_suffix,
        width: width,
        height: height
    }

    // no cache
    const response = await fetch(getCaptchaURL(captcha_url, query),
        {
            method: 'GET',
            headers: headers
        })

    if (response.ok) {
        const response_json = await response.json()
        if (typeof response_json.image_inline === 'string') {
            captcha_image.setAttribute('src', response_json.image_inline)
            if (typeof response_json.image_width === 'string') {
                captcha_image.setAttribute('width', response_json.image_width)
            }
            if (typeof response_json.image_height === 'string') {
                captcha_image.setAttribute('height', response_json.image_height)
            }
            reload_errors.textContent = ''
        } else {
            reload_errors.textContent = 'Invalid Captcha data'
        }
    } else {
        let statusText
        if(!response.bodyUsed) {
            try {
                statusText = await response.text()
            } catch (e) {
                try {
                    statusText = await response.json()
                } catch {
                    // nothing to do here
                }
            }
        }
        reload_errors.textContent = 'Error ' + response.status +
            (statusText ? (': ' + statusText) : '')
    }

    reloadActions(false)
    captcha_holder.classList.remove('d-none')
}

captcha_reload.onclick = function () {
    void reloadCaptcha(captcha_width, captcha_height, cc_suffix, captcha_reload_url)
}

// populate
void reloadCaptcha(captcha_width, captcha_height, cc_suffix, captcha_reload_url)

// Important!
// Periodically refresh captcha:
//   - refresh at short intervals in order to improve security
//   - refresh interval must be slightly lower than the lifetime of captcha cookie
setInterval(() => reloadCaptcha(captcha_width, captcha_height, cc_suffix, captcha_reload_url),
    captcha_reload_interval_ms)
