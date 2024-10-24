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

/* global FileList */

export function fileSizeHuman (size) {
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
 * @param {HTMLDivElement} targetElement
 * @param {FileList} files A list of files provided using i.e. HTMLInputElement.files.
 * @param {string} uploadMaxFilesize Must be provided by the backend i.e. ini_get('uploadMaxFilesize').
 * @param {number} maxFileUploads Must be provided by the backend i.e. (int) ini_get('maxFileUploads').
 * @param {number} uploadMaxFilesizeBytes Must be provided by the backend i.e. ConvertUtils::getByteSize(ini_get('uploadMaxFilesize')).
 * @returns {boolean} Returns *true* if the operation runs without errors.
 */
export function showFiles (targetElement,
  files,
  uploadMaxFilesize = '2M',
  maxFileUploads = 20,
  uploadMaxFilesizeBytes = 2097152) {
  let opHasErrors = false

  let content = ''
  if (files instanceof FileList && files.length > 0) {
    for (let i = 0; i < files.length; i++) {
      let thisAttachmentHasErrors = false
      const file = files[i]
      let errorMsg = ''
      if (typeof file.name === 'string' && typeof file.size === 'number') {
        if (file.size >= uploadMaxFilesizeBytes) {
          opHasErrors = true
          thisAttachmentHasErrors = true
          errorMsg += 'maximum allowed size ' + uploadMaxFilesize + ' exceeded'
        }
        if ((i + 1) > maxFileUploads) {
          opHasErrors = true
          thisAttachmentHasErrors = true
          if (errorMsg.length > 0) {
            errorMsg += '; '
          }
          errorMsg += 'maximum number of files ' + maxFileUploads + ' exceeded'
        }

        content += '<li class="p-1 px-2' +
          (thisAttachmentHasErrors ? ' text-danger fw-bolder border border-danger rounded">' : '">') +
          file.name +
          '  (' + fileSizeHuman(file.size) + ')' +
          (thisAttachmentHasErrors ? '  (' + errorMsg + ')' : '') +
          '</li>'
      }
    }

    if (content.length > 0) {
      content = '<ul class="mt-1">' + content + '</ul>'
    }
  }

  targetElement.innerHTML = content

  return opHasErrors
}
