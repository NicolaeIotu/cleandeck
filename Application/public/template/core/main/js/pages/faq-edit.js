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
import { showFiles } from '../files-utils.js'
import { idbRead, idbWrite, idbDelete } from '../idb-utils.js'
import TextEditor from '../TextEditor.js'

const elements = {}
const elementsIds = [
  'main_form', 'front_form', 'delete_form',
  'captcha_code', 'cc_suffix', 'show-files',
  'faq_attachments', 'existing_attachments_list', 'button_remove_attachments',
  'toggle_remove_attachments', 'existing-attachments', 'delete_faq_btn',
  'cancel_delete_faq_btn', 'controlled_submit',
  'answer_summary_front', 'answer_summary_original',
  'answer_front', 'answer_original',
  'lang_code_front', 'question_front',
  'author_name_front', 'tags_front',
  'show_in_sitemap_front', 'sitemap_changefreq_front', 'sitemap_priority_front',
  'show_in_rss_front', 'disable_front'
]
elementsIds.forEach((id) => {
  elements[id] = document.getElementById(id)
})

// multiple text editors
const textEditors = document.getElementsByClassName('cleandeck-text-editor')
const summaryEditor = new TextEditor(textEditors.item(0))
const faqEditor = new TextEditor(textEditors.item(1))

const faqId = elements.main_form.getAttribute('data-faq-id')

let filesErrors = false
const isModifyAction = elements.main_form.getAttribute('data-modify') === 'true'

elements.main_form.onkeydown = function (event) {
  if (typeof event.key === 'string' && event.key.toLowerCase() === 'enter') {
    if (event.target instanceof HTMLTextAreaElement ||
      event.target.hasAttribute('contenteditable')) {
      return
    }

    event.preventDefault()
    controlledSubmit()
  }
}

function toggleRemoveAttachments () {
  const isRemove = elements.toggle_remove_attachments.getAttribute('value') === 'false'

  elements.toggle_remove_attachments.setAttribute('value', isRemove ? 'true' : 'false')
  elements.button_remove_attachments.innerText = (isRemove ? 'Cancel ' : '') +
    'Remove Existing Attachments'

  const htmlColLi = elements.existing_attachments_list.children
  for (let i = 0; i < htmlColLi.length; i++) {
    const liI = htmlColLi.item(i)
    let liIClass = 'ms-5'
    if (isRemove) {
      liIClass += ' line-through text-danger'
    } else {
      liIClass += ' text-success'
    }
    liI.setAttribute('class', liIClass)
  }
}

const uploadMaxFilesize = elements.faq_attachments.getAttribute('data-umf')
let maxFileUploads = parseInt(elements.faq_attachments.getAttribute('data-mfu'), 10)
if (maxFileUploads < 1 || maxFileUploads > 50) {
  // modify these limitations if required
  maxFileUploads = 20
}
let uploadMaxFilesizeBytes = parseInt(elements.faq_attachments.getAttribute('data-umfb'), 10)
if (uploadMaxFilesizeBytes > 10485760) {
  // modify these limitations if required
  uploadMaxFilesizeBytes = 2097152
}

elements.faq_attachments.onchange = function filesOnChange (event) {
  const files = event.target.files
  summaryEditor.attachments = files
  faqEditor.attachments = files

  filesErrors = showFiles(
    elements['show-files'],
    files,
    uploadMaxFilesize,
    maxFileUploads,
    uploadMaxFilesizeBytes
  )

  if (elements['existing-attachments']) {
    elements['existing-attachments'].style.display = (files && files.length > 0) ? 'none' : 'block'
  }
}

if (elements.button_remove_attachments !== null) {
  elements.button_remove_attachments.onclick = function () {
    toggleRemoveAttachments()
  }
}
if (elements.delete_faq_btn !== null) {
  elements.delete_faq_btn.onclick = function () {
    controlledDelete()
  }
}
if (elements.cancel_delete_faq_btn !== null) {
  elements.cancel_delete_faq_btn.onclick = function () {
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

const deleteText = ['Delete FAQ', 'Please confirm deletion', 'DELETE - Final Confirmation']
const countDeleteStages = deleteText.length
let deleteStageIndex = 0

function controlledDelete () {
  deleteStageIndex++
  if (deleteStageIndex >= countDeleteStages) {
    setCaptchaForm(true)
    elements.delete_form.requestSubmit()
  } else {
    if (elements.cancel_delete_faq_btn) {
      elements.cancel_delete_faq_btn.classList.remove('d-none')
    }
    elements.delete_faq_btn.textContent = deleteText[deleteStageIndex]
  }
}

function cancelDelete () {
  deleteStageIndex = 0
  elements.delete_faq_btn.textContent = deleteText[deleteStageIndex]
  if (elements.cancel_delete_faq_btn) {
    elements.cancel_delete_faq_btn.classList.add('d-none')
  }
}

const backbone = {
  lang_code: {
    content: elements.lang_code_front.getAttribute('data-content'),
    convert: elements.lang_code_front.getAttribute('data-convert') === 'true',
    is_select: true
  },
  question:
    {
      content: elements.question_front.getAttribute('data-content'),
      convert: elements.question_front.getAttribute('data-convert') === 'true'
    },
  author_name:
    {
      content: elements.author_name_front.getAttribute('data-content'),
      convert: elements.author_name_front.getAttribute('data-convert') === 'true'
    },
  tags:
    {
      content: elements.tags_front.getAttribute('data-content'),
      convert: elements.tags_front.getAttribute('data-convert') === 'true'
    },
  show_in_sitemap:
    {
      content: elements.show_in_sitemap_front.getAttribute('data-content'),
      convert: elements.show_in_sitemap_front.getAttribute('data-convert') === 'true',
      is_checkbox: true
    },
  sitemap_changefreq:
    {
      content: elements.sitemap_changefreq_front.getAttribute('data-content'),
      convert: elements.sitemap_changefreq_front.getAttribute('data-convert') === 'true',
      is_select: true
    },
  sitemap_priority:
    {
      content: elements.sitemap_priority_front.getAttribute('data-content'),
      convert: elements.sitemap_priority_front.getAttribute('data-convert') === 'true'
    },
  show_in_rss:
    {
      content: elements.show_in_rss_front.getAttribute('data-content'),
      convert: elements.show_in_rss_front.getAttribute('data-convert') === 'true',
      is_checkbox: true
    }
}

if (isModifyAction) {
  backbone.disable = {
    content: elements.disable_front.getAttribute('data-content'),
    convert: elements.disable_front.getAttribute('data-convert') === 'true',
    is_select: true
  }
}

idbRead('faq', ['faq_id', 'answer', 'answer_summary'])
  .then((faqData) => {
    // don't wait for deletion
    idbDelete('faq', ['faq_id', 'answer', 'answer_summary'])
      .catch((reason) => {
        console.error('Failed to remove previous FAQ details: ' + reason.message)
      })

    try {
      // ID verification is critical!
      if (faqData.faq_id === faqId) {
        if (typeof faqData.answer === 'string') {
          backbone.answer = {
            // decode content first
            content: atobPlus(faqData.answer),
            convert: false,
            is_main_content: true
          }
        }
        if (typeof faqData.answer_summary === 'string') {
          backbone.answer_summary = {
            // decode content first
            content: atobPlus(faqData.answer_summary),
            convert: false,
            is_main_content: true
          }
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
    if (!Object.prototype.hasOwnProperty.call(backbone, 'answer')) {
      const answerOriginalContent = elements.answer_original.innerHTML.trim()
      backbone.answer = {
        content: answerOriginalContent
          .replace(/<\/script>/i, '[[end_script]]'),
        convert: false,
        is_main_content: true
      }
    }
    if (!Object.prototype.hasOwnProperty.call(backbone, 'answer_summary')) {
      const answerSummaryOriginalContent = elements.answer_summary_original.textContent.trim()
      backbone.answer_summary = {
        content: answerSummaryOriginalContent
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
          if (vert.is_main_content) {
            if (Object.prototype.hasOwnProperty.call(backbone, 'answer') ||
              Object.prototype.hasOwnProperty.call(backbone, 'answer_summary')) {
              vertContent = vertContent.replace(endScriptRegexp, closeScriptTag)
            }
          }

          const actualContent = vert.convert ? decodeURI(atobPlus(vertContent)) : vertContent
          switch (id) {
            case 'answer':
              faqEditor.text = actualContent
              break
            case 'answer_summary':
              summaryEditor.text = actualContent
              break
            default:
              vert.element_front.value = actualContent
          }
        }
      }
    }
    // End REBUILD
  })

function getPreparedElementFrontValue (vert) {
  if (vert.is_checkbox) {
    return encodeURI(vert.element_front.checked ? vert.element_front.value : '0')
  }
  if (vert.is_select) {
    return vert.element_front.selectedOptions.length > 0 ? encodeURI(vert.element_front.selectedOptions[0].value) : ''
  }
  if (vert.element.id === 'answer') {
    return faqEditor.getProductionText()
  }
  if (vert.element.id === 'answer_summary') {
    return summaryEditor.getProductionText()
  }
  return encodeURI(vert.element_front.value)
}

function preSubmitEncode () {
  // Use base64 to encode complex data before submitting.
  // function 'btoaPlus' can handle also Unicode text
  for (const id in backbone) {
    if (Object.prototype.hasOwnProperty.call(backbone, id)) {
      const vert = backbone[id]
      const preparedElementFrontValue = getPreparedElementFrontValue(vert)
      vert.element.value = btoaPlus(preparedElementFrontValue)
    }
  }
}

function controlledSubmit () {
  preSubmitEncode()

  if (typeof filesErrors === 'boolean' && filesErrors) {
    elements.faq_attachments.focus()
    return
  }

  if (elements.front_form.reportValidity()) {
    // Save answer and answer_summary in IndexedDB because the content is too large.
    // In case of failures the previous answer and answer_summary will be retrieved
    // safely from the local IndexedDB.
    idbWrite('faq', {
      faq_id: faqId,
      answer_summary: btoaPlus(summaryEditor.getProductionText()),
      answer: btoaPlus(faqEditor.getProductionText())
    })
      .catch((reason) => console.error(reason.toString()))
      .finally(() => {
        setCaptchaForm()
        elements.main_form.requestSubmit()
      })
  }
}
