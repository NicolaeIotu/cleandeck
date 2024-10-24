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

/* global DOMStringMap */

// Position this script after the images.
// The tag <img> should have the following syntax:
//   <img width="120" height="120" data-src="./picture.png">

// IMPORTANT! Do not add attribute 'src'

import ImageAutoload from './ImageAutoLoad.js'

// We need a static list of images!
const documentImages = Array.from(document.images)

// Start processing target images (img elements having attribute 'data-src').
for (const img of documentImages) {
  if (img.dataset instanceof DOMStringMap) {
    const dataset = img.dataset

    if ('src' in dataset &&
      typeof dataset.src === 'string' && dataset.src.length > 0) {
      // just in case
      img.removeAttribute('src')
      // start autoload
      // eslint-disable-next-line no-new
      new ImageAutoload(img, dataset.src)
    }
  }
}
