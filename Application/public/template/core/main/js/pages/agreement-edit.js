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

/* global HTMLTextAreaElement */

import { atobPlus, btoaPlus } from '../base64-utils.js'
import { idbRead, idbWrite, idbDelete } from '../idb-utils.js'

const elements = {}
const elementsIds = [
  'main_form', 'front_form', 'delete_form',
  'captcha_code', 'cc_suffix', 'delete_agreement_btn',
  'cancel_delete_agreement_btn', 'controlled_submit', 'agreement_title_front',
  'agreement_type_front', 'for_employee_types_front', 'for_email_front',
  'agreement_content_front', 'agreement_content_original'
]
elementsIds.forEach((id) => {
  elements[id] = document.getElementById(id)
})

const agreementId = elements.main_form.getAttribute('data-agreement-id')

elements.main_form.onkeydown = function (event) {
  if (typeof event.key === 'string' && event.key.toLowerCase() === 'enter') {
    // this is the only multiline form element at the moment
    if (!(event.target instanceof HTMLTextAreaElement)) {
      event.preventDefault()
      controlledSubmit()
    }
    event.stopPropagation()
  }
}

if (elements.delete_agreement_btn !== null) {
  elements.delete_agreement_btn.onclick = function () {
    controlledDelete()
  }
}
if (elements.cancel_delete_agreement_btn !== null) {
  elements.cancel_delete_agreement_btn.onclick = function () {
    cancelDelete()
  }
}
elements.controlled_submit.onclick = function () {
  controlledSubmit()
}

function setCaptchaForm (isDeleteForm = false) {
  const targetForm = isDeleteForm ? 'delete_form' : 'main_form'
  elements.cc_suffix.setAttribute('form', targetForm)
  elements.captcha_code.setAttribute('form', targetForm)
}

const deleteText = ['Delete agreement', 'Please confirm deletion', 'DELETE - Final Confirmation']
const countDeleteStages = deleteText.length
let deleteStageIndex = 0

function controlledDelete () {
  deleteStageIndex++
  if (deleteStageIndex >= countDeleteStages) {
    setCaptchaForm(true)
    elements.delete_form.requestSubmit()
  } else {
    if (elements.cancel_delete_agreement_btn) {
      elements.cancel_delete_agreement_btn.classList.remove('d-none')
    }
    elements.delete_agreement_btn.textContent = deleteText[deleteStageIndex]
  }
}

function cancelDelete () {
  deleteStageIndex = 0
  elements.delete_agreement_btn.textContent = deleteText[deleteStageIndex]
  if (elements.cancel_delete_agreement_btn) {
    elements.cancel_delete_agreement_btn.classList.add('d-none')
  }
}

const backbone = {
  agreement_title:
    {
      content: elements.agreement_title_front.getAttribute('data-content'),
      convert: elements.agreement_title_front.getAttribute('data-convert') === 'true'
    },
  agreement_type:
    {
      content: elements.agreement_type_front.getAttribute('data-content'),
      convert: elements.agreement_type_front.getAttribute('data-convert') === 'true'
    },
  for_employee_types:
    {
      content: elements.for_employee_types_front
        .getAttribute('data-content')
        .replace(/(^,)|(,$)/g, ''),
      convert: elements.for_employee_types_front.getAttribute('data-convert') === 'true'
    },
  for_email:
    {
      content: elements.for_email_front.getAttribute('data-content'),
      convert: elements.for_email_front.getAttribute('data-convert') === 'true'
    }
}

idbRead('agreement', ['agreement_id', 'agreement_content'])
  .then((agreementData) => {
    // don't wait for deletion
    idbDelete('agreement', ['agreement_id', 'agreement_content'])
      .catch((reason) => {
        console.error('Failed to remove previous agreement_content: ' + reason.message)
      })

    try {
      // ID verification is critical!
      if (agreementData.agreement_id === agreementId &&
        typeof agreementData.agreement_content === 'string') {
        backbone.agreement_content = {
          // decode content first
          content: atobPlus(agreementData.agreement_content),
          convert: false,
          is_main_content: true
        }
      }
      // eslint-disable-next-line no-empty
    } catch {
    }
  })
  .catch(() => {
    // nothing to do here; just use the entry provided
  })
  .finally(() => {
    if (!Object.prototype.hasOwnProperty.call(backbone, 'agreement_content')) {
      const agreementContentOriginalContent = elements.agreement_content_original.textContent.trim()
      backbone.agreement_content = {
        content: agreementContentOriginalContent
          .replace(/<\/script>/i, '[[end_script]]'),
        convert: false,
        is_main_content: true
      }
    }

    // add final backbone properties
    for (const id in backbone) {
      if (Object.prototype.hasOwnProperty.call(backbone, id)) {
        backbone[id].element = document.getElementById(id)
        backbone[id].element_front = document.getElementById(id + '_front')
      }
    }

    // prevents malformed script when the main content includes 'script' tags
    const endScriptRegexp = /\[\[end_script]]/g
    const closeScriptTag = [60, 47, 115, 99, 114, 105, 112, 116, 62]
      .map(e => String.fromCharCode(e)).join('')

    // REBUILD
    for (const id in backbone) {
      if (Object.prototype.hasOwnProperty.call(backbone, id)) {
        const vert = backbone[id]
        if (vert.is_checkbox) {
          if (vert.convert) {
            vert.element_front.checked = decodeURI(atobPlus(vert.content)) === '1'
          } else {
            vert.element_front.checked = vert.content === '1'
          }
        } else {
          let vertContent = vert.content
          // one additional step for the main content
          if (vert.is_main_content &&
            Object.prototype.hasOwnProperty.call(backbone, 'agreement_content')) {
            vertContent = vertContent.replace(endScriptRegexp, closeScriptTag)
          }

          if (vert.convert) {
            vert.element_front.value = decodeURI(atobPlus(vertContent))
          } else {
            vert.element_front.value = vertContent
          }
        }
      }
    }
    // End REBUILD
  })

function preSubmitEncode () {
  // Use base64 to encode complex data before submitting.
  // function 'btoaPlus' can handle also Unicode text
  for (const id in backbone) {
    if (Object.prototype.hasOwnProperty.call(backbone, id)) {
      const vert = backbone[id]
      let preparedElementFrontValue

      if (vert.is_checkbox) {
        preparedElementFrontValue = encodeURI(vert.element_front.checked ? vert.element_front.value : '0')
      } else if (vert.is_select) {
        preparedElementFrontValue =
          vert.element_front.selectedOptions.length > 0 ? encodeURI(vert.element_front.selectedOptions[0].value) : ''
      } else {
        preparedElementFrontValue = encodeURI(vert.element_front.value)
      }
      vert.element.value = btoaPlus(preparedElementFrontValue)
    }
  }
}

function controlledSubmit () {
  preSubmitEncode()

  if (elements.front_form.reportValidity()) {
    // Save agreement_content in IndexedDB because the content is too large.
    // In case of failures the previous agreement_content will be retrieved
    // safely from the local IndexedDB.
    idbWrite('agreement', {
      agreement_id: agreementId,
      agreement_content: btoaPlus(elements.agreement_content_front.value)
    })
      .catch((reason) => console.error(reason.toString()))
      .finally(() => {
        setCaptchaForm()
        elements.main_form.requestSubmit()
      })
  }
}
