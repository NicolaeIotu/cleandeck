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

let aQZ4vtaS9NixRBDbXHJPsPS1kFzK = false

async function __getCsrf (elements, tokenUrl) {
  if (aQZ4vtaS9NixRBDbXHJPsPS1kFzK) {
    return
  }

  const headers = new Headers()
  headers.append('X-Requested-With', 'XMLHttpRequest')

  // no cache
  const response = await fetch(tokenUrl + '?' + Date.now(),
    {
      method: 'GET',
      headers
    })

  if (response.ok) {
    const responseText = await response.text()

    const t = atob(responseText)
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

    __csrfFailure('CSRF Error: ' + response.status +
      (statusText ? (': ' + statusText) : ''))
  }

  aQZ4vtaS9NixRBDbXHJPsPS1kFzK = true
}

function __initCsrf () {
  const inputs = document.getElementsByTagName('input')

  let tokenUrl
  const csrfInputElements = []

  for (const inputElem of inputs) {
    if (inputElem.hasAttribute('data-csrf') &&
      inputElem.hasAttribute('data-url') &&
      inputElem.getAttribute('type') === 'hidden') {
      csrfInputElements.push(inputElem)
      tokenUrl = inputElem.getAttribute('data-url')
      inputElem.removeAttribute('data-url')
      inputElem.removeAttribute('data-csrf')
    }
  }
  if (typeof tokenUrl === 'string' && csrfInputElements.length > 0) {
    __getCsrf(csrfInputElements, tokenUrl)
    return
  }

  __csrfFailure('CSRF Error - missing CSRF input field')
}

function __csrfFailure (errorMsg) {
  console.error(errorMsg)
}

__initCsrf()
