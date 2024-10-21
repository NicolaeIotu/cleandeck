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

// import AJAX from "./AJAX.js"

let aQZ4vtaS9NixRBDbXHJPsPS1kFzK = false

async function __getCsrf(elements, token_url) {
    if (aQZ4vtaS9NixRBDbXHJPsPS1kFzK) {
        return
    }

    const headers = new Headers()
    headers.append('X-Requested-With', 'XMLHttpRequest')

    // no cache
    const response = await fetch(token_url + '?' + Date.now(),
        {
            method: 'GET',
            headers: headers
        })

    if (response.ok) {
        const response_text = await response.text()

        const t = atob(response_text)
        elements.forEach((element) => {
            element.value = t
            element.dispatchEvent(new Event('change'))
        })
    } else {
        let statusText
        if (!response.bodyUsed) {
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

        void __csrfFailure('CSRF Error: ' + response.status +
            (statusText ? (': ' + statusText) : ''))
    }

    aQZ4vtaS9NixRBDbXHJPsPS1kFzK = true
}

function __initCsrf() {
    const inputs = document.getElementsByTagName('input')

    let token_url
    const csrf_input_elements = []

    for (const input_elem of inputs) {
        if (input_elem.hasAttribute('data-csrf') &&
            input_elem.hasAttribute('data-url') &&
            input_elem.getAttribute('type') === 'hidden') {

            csrf_input_elements.push(input_elem)
            token_url = input_elem.getAttribute('data-url')
            input_elem.removeAttribute('data-url')
            input_elem.removeAttribute('data-csrf')
        }
    }
    if (typeof token_url === 'string' && csrf_input_elements.length > 0) {
        void __getCsrf(csrf_input_elements, token_url)
        return
    }

    void __csrfFailure('CSRF Error - missing CSRF input field')
}

function __csrfFailure(error_msg) {
    console.error(error_msg)
}

void __initCsrf()
