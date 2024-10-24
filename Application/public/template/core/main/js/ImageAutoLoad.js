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

/* global Image */

const documentURL = new URL(document.URL)
const hasOwnProperty = Object.prototype.hasOwnProperty

// the images below are only cloned when required
// prepare error state image
const errorImg = new Image()
errorImg.src = documentURL.origin + '/template/core/main/images/missing_image.png'
errorImg.setAttribute('class', 'm-auto')
errorImg.alt = 'Error. Click to retry.'
errorImg.title = 'Error. Click to retry.'
// prepare loading state image
const loadingIndicator = document.createElement('div')
loadingIndicator.setAttribute('class', 'spinner-border position-relative m-auto loading-40-40')
loadingIndicator.setAttribute('role', 'status')
loadingIndicator.setAttribute('alt', 'Loading image ...')
loadingIndicator.setAttribute('title', 'Loading image ...')

// eslint-disable-next-line no-unused-vars
export default class ImageAutoload {
  /**
   *
   * @param {HTMLImageElement} image
   * @param {string} src
   */
  constructor (image, src) {
    this.interval_ms = 2500
    this.max_retries = 6
    this.timer_ref = null
    this.retries_count = 0

    this.image = image
    this.src = src

    // build loading state image
    this.loading_img = loadingIndicator.cloneNode()

    // show the loader
    image.replaceWith(this.loading_img)

    // setup event listeners
    image.addEventListener('load', this.onAutoloadSuccess.bind(this))

    errorImg.addEventListener('load', this.onLoadImage)

    // start loading the image
    this.startAutoload()
  }

  onTimeoutAutoload () {
    this.retries_count++

    if (this.retries_count >= this.max_retries) {
      this.stopAutoload()
      this.showError()
    } else {
      this.timer_ref = setTimeout(this.onTimeoutAutoload.bind(this), this.interval_ms)
      this.image.src = this.src
    }
  }

  onLoadImage (evt) {
    if (typeof evt === 'object' && hasOwnProperty.call(evt, 'target')) {
      // evt.target === this
      const _this = evt.target
      const rWidth = _this.getAttribute('width') || _this.naturalWidth.toString()
      const rHeight = _this.getAttribute('height') || _this.naturalHeight.toString()
      _this.setAttribute('width', rWidth)
      _this.setAttribute('height', rHeight)
    }
  }

  startAutoload () {
    this.timer_ref = setTimeout(this.onTimeoutAutoload.bind(this), this.interval_ms)
    this.image.src = this.src
  }

  restartAutoload () {
    this.errorImg.replaceWith(this.loading_img)
    this.startAutoload()
  }

  stopAutoload () {
    this.image.removeEventListener('load', this.onAutoloadSuccess.bind(this))
    clearTimeout(this.timer_ref)
    this.retries_count = 0
  }

  showError () {
    if (!this.errorImg) {
      // initial setup of error state image
      if (hasOwnProperty.call(this.image.dataset, 'imgError') &&
        typeof this.image.dataset.imgError === 'string' && this.image.dataset.imgError.length > 0) {
        // requires attribute 'data-img-error'
        this.errorImg = document.getElementById(this.image.dataset.imgError)
      } else {
        // all other cases must have the attribute 'data-src-error'
        this.errorImg = errorImg.cloneNode()
      }

      this.errorImg.onclick = this.restartAutoload.bind(this)
    }

    this.loading_img.replaceWith(this.errorImg)
  }

  onAutoloadSuccess () {
    this.stopAutoload()

    this.onLoadImage.bind(this.image)()

    this.loading_img.replaceWith(this.image)
  }
}
