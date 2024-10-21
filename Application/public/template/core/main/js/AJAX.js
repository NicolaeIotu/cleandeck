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

/**
 * @typedef {object} AjaxSettings
 * @property {string} method
 * @property {string} url
 * @property {boolean} async - Default true
 * @property {string|null} user - Default null
 * @property {string|null} password - Default null
 * @property {Document|XMLHttpRequestBodyInit} body - Default null
 * @property {object|null} query - Default null
 * @property {object} headers - Default null
 * @property {number} timeout - Default 30000
 * @property {boolean} cache - Default true
 * @property {XMLHttpRequestResponseType} responseType
 */

/**
 * @typedef {object} AjaxPromiseResolve
 * @property {AJAX} ajax
 * @property {XMLHttpRequestResponseType} data
 */

/**
 * @typedef {object} AjaxPromiseReject
 * @property {AJAX} ajax
 * @property {string} message
 * @property {number} code
 */

/**
 * @constructor
 * @param {AjaxSettings|object} settings
 * @returns {Promise<AjaxPromiseResolve, AjaxPromiseReject>}
 */
export default class AJAX extends XMLHttpRequest {
    /**
     * @member {string}
     */
    responseTypeSync;

    /**
     * @constructor
     * @param {AjaxSettings|object} settings
     * @returns {Promise<AjaxPromiseResolve|AjaxPromiseReject>}
     */
    constructor(settings) {
        super()

        return new Promise(
            (resolve, reject) => {
                if (settings.cache === false) {
                    if (this._xtypeof(settings.query) !== 'object') {
                        settings.query = {}
                    }
                    settings.query[Date.now()] = null
                }

                const sq = this._stringify(settings.query)

                this.open(settings.method,
                    settings.url + (sq === '' ? '' : '?') + sq,
                    settings.async !== false,
                    settings?.user || null,
                    settings?.password || null)

                if (settings.async !== false) {
                    if (isNaN(settings.timeout) || settings.timeout < 0 || settings.timeout > 600000) {
                        this.timeout = 30000
                    } else {
                        this.timeout = settings.timeout
                    }

                    this.responseType = settings?.responseType || 'text'
                } else {
                    this.responseTypeSync = settings?.responseType || 'text'
                }

                let header_content_type
                if (this._xtypeof(settings.headers) === 'object') {
                    for (const header_name in settings.headers) {
                        if (Object.prototype.hasOwnProperty.call(settings.headers, header_name)) {
                            this.setRequestHeader(header_name, settings.headers[header_name])

                            if (header_name.toLowerCase() === 'content-type') {
                                header_content_type = settings.headers[header_name]
                            }
                        }
                    }
                }
                this.setRequestHeader('X-Requested-With', 'XMLHttpRequest')

                this.addEventListener('error', () => {
                    // for uploads mainly
                    reject({
                        ajax: this,
                        message: 'Request error',
                        code: 400
                    })
                })
                this.addEventListener('abort', () => {
                    reject({
                        ajax: this,
                        message: 'Request aborted',
                        code: 400
                    })
                })
                this.addEventListener('timeout', () => {
                    reject({
                        ajax: this,
                        message: 'Request timed out',
                        code: 408
                    })
                })

                this.addEventListener('load', () => {
                    if (this.status >= 200 && this.status < 400) {
                        const result = {
                            ajax: this
                        }

                        switch (this.responseTypeSync) {
                            case 'json':
                                result.data = JSON.parse(this.response)
                                break
                            case 'document':
                                result.data = this.responseXML
                                break
                            default:
                                // for async requests and all other types at the moment
                                result.data = this.response
                        }
                        resolve(result)
                    } else {
                        reject({
                            ajax: this,
                            message: this.response || this.statusText || 'HTTP Error ' + this.status,
                            code: this.status
                        })
                    }
                })

                const body_raw = settings?.body || null
                // improve
                if (header_content_type) {
                    if (header_content_type.toLowerCase().includes('x-www-form-urlencoded')) {
                        this.send(this._stringify(body_raw))
                        return
                    }
                }
                this.send(body_raw)
            })
    }

    _stringify(o) {
        if (this._xtypeof(o) !== 'object') {
            return ''
        }

        let begin = true
        let result = ''
        for (const key in o) {
            if (Object.prototype.hasOwnProperty.call(o, key)) {
                if (begin) {
                    begin = false
                } else {
                    result += '&'
                }

                const value = o[key]
                const xtypeof_value = this._xtypeof(value)
                if (xtypeof_value === 'number' ||
                    xtypeof_value === 'string' ||
                    xtypeof_value === 'boolean') {
                    result += key + '=' + o[key]
                } else if (xtypeof_value === 'array') {
                    let begin = true
                    value.forEach((elem) => {
                        if (begin) {
                            begin = false
                        } else {
                            result += '&'
                        }
                        result += key + '=' + elem
                    })
                }
            }
        }

        return result
    }

    _xtypeof(o) {
        return Object.prototype.toString.call(o).slice(8, -1).toLowerCase()
    }
}
