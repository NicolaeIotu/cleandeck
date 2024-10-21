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

export function fileSizeHuman(size) {
    if (!isFinite(size)) {
        return 'Invalid size'
    }

    const sizeMB = 1024 * 1024
    if (size > sizeMB) {
        return (size / sizeMB).toFixed(2) + ' MB'
    } else if (size > 10240) {
        return (size / 1024).toFixed(2) + ' KB'
    } else {
        return size + ' B'
    }
}

/**
 * @param {HTMLDivElement} target_element
 * @param {FileList} files A list of files provided using i.e. HTMLInputElement.files.
 * @param {string} upload_max_filesize Must be provided by the backend i.e. ini_get('upload_max_filesize').
 * @param {number} max_file_uploads Must be provided by the backend i.e. (int) ini_get('max_file_uploads').
 * @param {number} upload_max_filesize_bytes Must be provided by the backend i.e. ConvertUtils::getByteSize(ini_get('upload_max_filesize')).
 * @returns {boolean} Returns *true* if the operation runs without errors.
 */
export function showFiles(target_element,
                         files,
                         upload_max_filesize = '2M',
                         max_file_uploads = 20,
                         upload_max_filesize_bytes = 2097152) {
    let op_has_errors = false

    let content = ''
    if (files instanceof FileList && files.length > 0) {
        for (let i = 0; i < files.length; i++) {
            let this_attachment_has_errors = false
            const file = files[i]
            let errorMsg = ''
            if (typeof file.name === 'string' && typeof file.size === 'number') {
                if (file.size >= upload_max_filesize_bytes) {
                    op_has_errors = true
                    this_attachment_has_errors = true
                    errorMsg += 'maximum allowed size ' + upload_max_filesize + ' exceeded'
                }
                if ((i + 1) > max_file_uploads) {
                    op_has_errors = true
                    this_attachment_has_errors = true
                    if (errorMsg.length > 0) {
                        errorMsg += '; '
                    }
                    errorMsg += 'maximum number of files ' + max_file_uploads + ' exceeded'
                }

                content += '<li class="p-1 px-2' +
                    (this_attachment_has_errors ? ' text-danger fw-bolder border border-danger rounded">' : '">') +
                    file.name +
                    '  (' + fileSizeHuman(file.size) + ')' +
                    (this_attachment_has_errors ? '  (' + errorMsg + ')' : '') +
                    '</li>'
            }
        }

        if (content.length > 0) {
            content = '<ul class="mt-1">' + content + '</ul>'
        }
    }

    target_element.innerHTML = content

    return op_has_errors
}
