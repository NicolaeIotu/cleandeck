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

export default class HttpError extends Error {
  constructor (message = 'Http Error', code = 400, ...params) {
    // Pass remaining arguments (including vendor specific ones) to parent constructor
    super(...params)

    this.name = 'HttpError'
    this.code = code
  }
}
