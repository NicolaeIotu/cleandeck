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

// IndexedDB Utils


const IDBUTILS_SETUP = {
    database: 'tmp_form_data',
    tables: [
        'article',
        'faq',
        'agreement'
    ]
}

function onUpgradeNeeded(event) {
    const db = event.target.result

    db.onerror = (event) => {
        console.error('Error loading database: ' + event.target.error)
    }

    // Create objectStores
    IDBUTILS_SETUP.tables.forEach((table_name) => {
        db.createObjectStore(table_name)
    })
}

/**
 * @param {string} table
 * @param {object} data Data to write as key/value pairs.
 * @returns {Promise<unknown>}
 */
export function idbWrite(table, data) {
    return new Promise((resolve, reject) => {
        // open database
        const DBOpenRequest = window.indexedDB.open(IDBUTILS_SETUP.database, 1)

        DBOpenRequest.onsuccess = () => {
            // open database
            const db = DBOpenRequest.result

            // open a read/write db transaction, ready for adding the data
            let transaction
            try {
                transaction = db.transaction([table], 'readwrite')

                // report on the success of the transaction completing, when everything is done
                transaction.oncomplete = () => {
                    // console.log('Transaction completed.')
                }

                transaction.onerror = (event) => {
                    reject('Transaction not opened: ' + event.target.error)
                }

                const keys = Object.keys(data)
                const keys_length = keys.length
                let key_index = 0

                // create an object store on the transaction
                const objectStore = transaction.objectStore(table)
                // objectStore.put(value, key)
                let objectStoreRequest = objectStore.put(data[keys[key_index]], keys[key_index])

                // eslint-disable-next-line no-inner-declarations
                function chainWrite() {
                    key_index++
                    if (key_index === keys_length) {
                        resolve()
                    } else {
                        objectStoreRequest = objectStore.put(data[keys[key_index]], keys[key_index])
                        objectStoreRequest.onsuccess = chainWrite
                    }
                }

                objectStoreRequest.onsuccess = chainWrite
            } catch (e) {
                reject(e.message)
            }
        }

        DBOpenRequest.onupgradeneeded = onUpgradeNeeded
    })
}

/**
 * @param {string} table
 * @param {string[]} keys
 * @returns {Promise<unknown>}
 */
export function idbRead(table, keys) {
    return new Promise((resolve, reject) => {
        const DBOpenRequest = window.indexedDB.open(IDBUTILS_SETUP.database, 1)

        DBOpenRequest.onsuccess = () => {
            // open database
            const db = DBOpenRequest.result

            // open a read/write db transaction, ready for adding the data
            let transaction
            try {
                transaction = db.transaction([table], 'readonly')

                // report on the success of the transaction completing, when everything is done
                transaction.oncomplete = () => {
                    // console.log('Transaction completed.')
                }

                transaction.onerror = (event) => {
                    reject('Transaction not opened due to error: ' + event.target.error)

                }

                const result = {}
                const keys_length = keys.length
                let key_index = 0
                // create an object store on the transaction
                const objectStore = transaction.objectStore(table)
                let objectStoreRequest = objectStore.get(keys[key_index])

                // eslint-disable-next-line no-inner-declarations
                function chainGet() {
                    result[keys[key_index]] = objectStoreRequest.result
                    key_index++
                    if (key_index === keys_length) {
                        resolve(result)
                    } else {
                        objectStoreRequest = objectStore.get(keys[key_index])
                        objectStoreRequest.onsuccess = chainGet
                    }
                }

                objectStoreRequest.onsuccess = chainGet
            } catch (e) {
                reject(e.message)
            }
        }

        DBOpenRequest.onupgradeneeded = onUpgradeNeeded
    })
}

/**
 * @param {string} table
 * @param {string[]} keys
 * @returns {Promise<unknown>}
 */
export function idbDelete(table, keys) {
    return new Promise((resolve, reject) => {
        const DBOpenRequest = window.indexedDB.open(IDBUTILS_SETUP.database, 1)

        DBOpenRequest.onsuccess = () => {
            // open database
            const db = DBOpenRequest.result

            // open a read/write db transaction, ready for adding the data
            let transaction
            try {
                transaction = db.transaction([table], 'readwrite')

                // report on the success of the transaction completing, when everything is done
                transaction.oncomplete = () => {
                    // console.log('Transaction completed.')
                }

                transaction.onerror = (event) => {
                    reject('Transaction not opened due to error: ' + event.target.error)
                }

                const keys_length = keys.length
                let key_index = 0
                // create an object store on the transaction
                const objectStore = transaction.objectStore(table)
                let objectStoreRequest = objectStore.delete(keys[key_index])

                // eslint-disable-next-line no-inner-declarations
                function chainDelete() {
                    key_index++
                    if (key_index === keys_length) {
                        resolve(objectStoreRequest.result)
                    } else {
                        objectStoreRequest = objectStore.delete(keys[key_index])
                        objectStoreRequest.onsuccess = chainDelete
                    }
                }

                objectStoreRequest.onsuccess = chainDelete
            } catch (e) {
                reject(e.message)
            }
        }

        DBOpenRequest.onupgradeneeded = onUpgradeNeeded
    })
}
