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

// Adapted after: https://developer.mozilla.org/en-US/docs/Web/API/WindowOrWorkerGlobalScope

function toBinaryString (data) {
  const codeUnits = new Uint16Array(data.length)
  for (let i = 0; i < codeUnits.length; i++) {
    codeUnits[i] = data.charCodeAt(i)
  }
  return String.fromCharCode(...new Uint8Array(codeUnits.buffer))
}

export function btoaPlus (data) {
  const blockSize = 512
  let counter = 0
  const dataLength = data.length

  let converted = ''
  while (counter < dataLength) {
    const slice = data.slice(counter, counter += blockSize)
    converted += toBinaryString(slice)
  }
  // base64 encoded
  return btoa(converted)
}

export function atobPlus (data) {
  const decoded = atob(data)

  const bytes = new Uint8Array(decoded.length)
  for (let i = 0; i < bytes.length; i++) {
    bytes[i] = decoded.charCodeAt(i)
  }
  // no errors at the moment
  return String.fromCharCode(...new Uint16Array(bytes.buffer))
}
