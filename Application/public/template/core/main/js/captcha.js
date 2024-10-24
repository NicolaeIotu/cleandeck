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

const captchaHolder = document.getElementById('captcha_holder')
const captchaImage = document.getElementById('captcha_image')
const captchaCode = document.getElementById('captcha_code')
const reloadErrors = document.getElementById('reload_errors')
const captchaReload = document.getElementById('captcha_reload')
const reloadSpinner = document.getElementById('reload_spinner')
const ccSuffixElement = document.getElementById('cc_suffix')

const ccSuffix = ccSuffixElement.value

const captchaWidth = captchaImage.getAttribute('width')
const captchaHeight = captchaImage.getAttribute('height')
const captchaReloadUrl = captchaReload.getAttribute('data-url')
captchaReload.removeAttribute('data-url')

const captchaReloadIntervalRaw = parseInt(captchaReload.getAttribute('data-ri'), 10)
let captchaReloadIntervalMs
if (Number.isFinite(captchaReloadIntervalRaw)) {
  captchaReloadIntervalMs = Math.max(55 * 1000,
    Math.min(30 * 60 * 1000, captchaReloadIntervalRaw * 1000))
} else {
  captchaReloadIntervalMs = 295 * 1000
}

let reloadInProgress = false

captchaImage.onerror = function () {
  reloadErrors.textContent = 'Cannot load captcha image! Please try again.'
}

function reloadActions (startReload) {
  reloadInProgress = startReload

  if (startReload) {
    captchaCode.setAttribute('disabled', 'disabled')
  } else {
    captchaCode.removeAttribute('disabled')
  }

  const target1 = startReload ? captchaReload : reloadSpinner
  const target2 = startReload ? reloadSpinner : captchaReload

  target2.classList.remove('d-none')
  target2.classList.add('d-inline-block')
  target1.classList.remove('d-inline-block')
  target1.classList.add('d-none')
}

function getCaptchaURL (captchaUrl, query) {
  return captchaUrl + '?' +
    'cc_suffix=' + (query.cc_suffix || '') +
    '&width=' + query.width +
    '&height=' + query.height +
    '&' + Date.now()
}

async function reloadCaptcha (width, height, ccSuffix, captchaUrl) {
  if (reloadInProgress) return

  captchaCode.value = ''
  reloadActions(true)

  const headers = new Headers()
  headers.append('X-Requested-With', 'XMLHttpRequest')

  const query = {
    cc_suffix: ccSuffix,
    width,
    height
  }

  // no cache
  const response = await fetch(getCaptchaURL(captchaUrl, query),
    {
      method: 'GET',
      headers
    })

  if (response.ok) {
    const responseJson = await response.json()
    if (typeof responseJson.image_inline === 'string') {
      captchaImage.setAttribute('src', responseJson.image_inline)
      if (typeof responseJson.image_width === 'string') {
        captchaImage.setAttribute('width', responseJson.image_width)
      }
      if (typeof responseJson.image_height === 'string') {
        captchaImage.setAttribute('height', responseJson.image_height)
      }
      reloadErrors.textContent = ''
    } else {
      reloadErrors.textContent = 'Invalid Captcha data'
    }
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
    reloadErrors.textContent = 'Error ' + response.status +
      (statusText ? (': ' + statusText) : '')
  }

  reloadActions(false)
  captchaHolder.classList.remove('d-none')
}

captchaReload.onclick = function () {
  reloadCaptcha(captchaWidth, captchaHeight, ccSuffix, captchaReloadUrl)
}

// populate
reloadCaptcha(captchaWidth, captchaHeight, ccSuffix, captchaReloadUrl)

// Important!
// Periodically refresh captcha:
//   - refresh at short intervals in order to improve security
//   - refresh interval must be slightly lower than the lifetime of captcha cookie
setInterval(() => reloadCaptcha(captchaWidth, captchaHeight, ccSuffix, captchaReloadUrl),
  captchaReloadIntervalMs)
