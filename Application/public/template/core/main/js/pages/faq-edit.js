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

import {atob_plus, btoa_plus} from '../base64-utils.js'
import {showFiles} from '../files-utils.js'
import {idbRead, idbWrite, idbDelete} from '../idb-utils.js'

const elements = {}
const elements_ids = [
    'main_form', 'front_form', 'delete_form',
    'captcha_code', 'cc_suffix', 'show-files',
    'faq_attachments', 'existing_attachments_list', 'button_remove_attachments',
    'toggle_remove_attachments', 'existing-attachments', 'delete_faq_btn',
    'cancel_delete_faq_btn', 'controlled_submit', 'answer_front',
    'answer_original', 'lang_code_front', 'question_front',
    'author_name_front', 'format_front', 'tags_front',
    'show_in_sitemap_front', 'sitemap_changefreq_front', 'sitemap_priority_front',
    'show_in_rss_front', 'disable_front'
]
elements_ids.forEach((id) => {
    elements[id] = document.getElementById(id)
})

const faq_id = elements['main_form'].getAttribute('data-faq-id')

let files_errors = false
const is_modify_action = elements['main_form'].getAttribute('data-modify') === 'true'
elements['main_form'].onkeydown = function (event) {
    if ('string' === typeof event.key && 'enter' === event.key.toLowerCase()) {
        // this is the only multiline form element at the moment
        if (!(event.target instanceof HTMLTextAreaElement)) {
            void controlledSubmit()
        }
        event.stopPropagation()
    }
}

function toggleRemoveAttachments() {
    const is_remove = elements['toggle_remove_attachments'].getAttribute('value') === 'false'

    elements['toggle_remove_attachments'].setAttribute('value', is_remove ? 'true' : 'false')
    elements['button_remove_attachments'].innerText = (is_remove ? 'Cancel ' : '') +
        'Remove Existing Attachments'

    const html_col_li = elements['existing_attachments_list'].children
    for (let i = 0; i < html_col_li.length; i++) {
        const li_i = html_col_li.item(i)
        let li_i_class = 'ms-5'
        if (is_remove) {
            li_i_class += ' line-through text-danger'
        } else {
            li_i_class += ' text-success'
        }
        li_i.setAttribute('class', li_i_class)
    }
}

const upload_max_filesize = elements['faq_attachments'].getAttribute('data-umf')
let max_file_uploads = parseInt(elements['faq_attachments'].getAttribute('data-mfu'), 10)
if (max_file_uploads < 1 || max_file_uploads > 50) {
    // modify these limitations if required
    max_file_uploads = 20
}
let upload_max_filesize_bytes = parseInt(elements['faq_attachments'].getAttribute('data-umfb'), 10)
if (upload_max_filesize_bytes > 10485760) {
    // modify these limitations if required
    upload_max_filesize_bytes = 2097152
}

elements['faq_attachments'].onchange = function filesOnChange(event) {
    const files = event.target.files
    files_errors = showFiles(
        elements['show-files'],
        files,
        upload_max_filesize,
        max_file_uploads,
        upload_max_filesize_bytes
    )

    if (elements['existing-attachments']) {
        elements['existing-attachments'].style.display = (files && files.length > 0) ? 'none' : 'block'
    }
}

if (elements['button_remove_attachments'] !== null) {
    elements['button_remove_attachments'].onclick = function () {
        void toggleRemoveAttachments()
    }
}
if (elements['delete_faq_btn'] !== null) {
    elements['delete_faq_btn'].onclick = function () {
        void controlledDelete()
    }
}
if (elements['cancel_delete_faq_btn'] !== null) {
    elements['cancel_delete_faq_btn'].onclick = function () {
        void cancelDelete()
    }
}
elements['controlled_submit'].onclick = function () {
    void controlledSubmit()
}

function setCaptchaForm(is_delete_form = false) {
    const target_form = is_delete_form ? 'delete_form' : 'main_form'
    elements['cc_suffix'].setAttribute('form', target_form)
    elements['captcha_code'].setAttribute('form', target_form)
}

const delete_text = ['Delete FAQ', 'Please confirm deletion', 'DELETE - Final Confirmation']
const count_delete_stages = delete_text.length
let delete_stage_index = 0

function controlledDelete() {
    delete_stage_index++
    if (delete_stage_index >= count_delete_stages) {
        setCaptchaForm(true)
        elements['delete_form'].requestSubmit()
    } else {
        if (elements['cancel_delete_faq_btn']) {
            elements['cancel_delete_faq_btn'].classList.remove('d-none')
        }
        elements['delete_faq_btn'].textContent = delete_text[delete_stage_index]
    }
}

function cancelDelete() {
    delete_stage_index = 0
    elements['delete_faq_btn'].textContent = delete_text[delete_stage_index]
    if (elements['cancel_delete_faq_btn']) {
        elements['cancel_delete_faq_btn'].classList.add('d-none')
    }
}

const backbone = {
    'lang_code': {
        content: elements['lang_code_front'].getAttribute('data-content'),
        convert: elements['lang_code_front'].getAttribute('data-convert') === 'true',
        is_select: true
    },
    'question':
        {
            content: elements['question_front'].getAttribute('data-content'),
            convert: elements['question_front'].getAttribute('data-convert') === 'true'
        },
    'author_name':
        {
            content: elements['author_name_front'].getAttribute('data-content'),
            convert: elements['author_name_front'].getAttribute('data-convert') === 'true'
        },
    'format':
        {
            content: elements['format_front'].getAttribute('data-content'),
            convert: elements['format_front'].getAttribute('data-convert') === 'true',
            is_select: true
        },
    'tags':
        {
            content: elements['tags_front'].getAttribute('data-content'),
            convert: elements['tags_front'].getAttribute('data-convert') === 'true'
        }
    ,
    'show_in_sitemap':
        {
            content: elements['show_in_sitemap_front'].getAttribute('data-content'),
            convert: elements['show_in_sitemap_front'].getAttribute('data-convert') === 'true',
            is_checkbox: true
        },
    'sitemap_changefreq':
        {
            content: elements['sitemap_changefreq_front'].getAttribute('data-content'),
            convert: elements['sitemap_changefreq_front'].getAttribute('data-convert') === 'true',
            is_select: true
        },
    'sitemap_priority':
        {
            content: elements['sitemap_priority_front'].getAttribute('data-content'),
            convert: elements['sitemap_priority_front'].getAttribute('data-convert') === 'true'
        },
    'show_in_rss':
        {
            content: elements['show_in_rss_front'].getAttribute('data-content'),
            convert: elements['show_in_rss_front'].getAttribute('data-convert') === 'true',
            is_checkbox: true
        }
}

if (is_modify_action) {
    backbone['disable'] = {
        content: elements['disable_front'].getAttribute('data-content'),
        convert: elements['disable_front'].getAttribute('data-convert') === 'true',
        is_select: true
    }
}

idbRead('faq', ['faq_id', 'answer'])
    .then((faq_data) => {
        // don't wait for deletion
        idbDelete('faq', ['faq_id', 'answer'])
            .catch((reason) => {
                console.error('Failed to remove previous answer: ' + reason)
            })

        try {
            // ID verification is critical!
            if (faq_data.faq_id === faq_id &&
                typeof faq_data.answer === 'string') {
                backbone['answer'] = {
                    // decode content first
                    content: atob_plus(faq_data.answer),
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
        if (!Object.prototype.hasOwnProperty.call(backbone, 'answer')) {
            const answer_original_content = elements['answer_original'].textContent.trim()
            backbone['answer'] = {
                content: answer_original_content
                    .replace(new RegExp('</script>', 'i'), '[[end_script]]'),
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
        const end_script_regexp = /\[\[end_script]]/g
        const close_script_tag = [60, 47, 115, 99, 114, 105, 112, 116, 62]
            .map(e => String.fromCharCode(e)).join('')

        // REBUILD
        for (const id in backbone) {
            if (Object.prototype.hasOwnProperty.call(backbone, id)) {
                const vert = backbone[id]
                if (vert.is_checkbox) {
                    if (vert.convert) {
                        vert.element_front.checked = decodeURI(atob_plus(vert.content)) === '1'
                    } else {
                        vert.element_front.checked = vert.content === '1'
                    }
                } else {
                    let vert_content = vert.content
                    // one additional step for the main content
                    if (vert.is_main_content &&
                        Object.prototype.hasOwnProperty.call(backbone, 'answer')) {
                        vert_content = vert_content.replace(end_script_regexp, close_script_tag)
                    }

                    if (vert.convert) {
                        vert.element_front.value = decodeURI(atob_plus(vert_content))
                    } else {
                        vert.element_front.value = vert_content
                    }
                }
            }
        }
        // End REBUILD
    })

function preSubmitEncode() {
    // Use base64 to encode complex data before submitting.
    // function 'btoa_plus' can handle also Unicode text
    for (const id in backbone) {
        if (Object.prototype.hasOwnProperty.call(backbone, id)) {
            const vert = backbone[id]
            let prepared_element_front_value

            if (vert.is_checkbox) {
                prepared_element_front_value = encodeURI(vert.element_front.checked ? vert.element_front.value : '0')
            } else if (vert.is_select) {
                prepared_element_front_value = vert.element_front.selectedOptions.length > 0 ?
                    encodeURI(vert.element_front.selectedOptions[0].value) : ''
            } else {
                prepared_element_front_value = encodeURI(vert.element_front.value)
            }
            vert.element.value = btoa_plus(prepared_element_front_value)
        }
    }
}

function controlledSubmit() {
    preSubmitEncode()

    if (typeof files_errors === 'boolean' && files_errors) {
        elements['faq_attachments'].focus()
        return
    }

    if (elements['front_form'].reportValidity()) {
        // Save answer in IndexedDB because the content is too large.
        // In case of failures the previous answer will be retrieved
        // safely from the local IndexedDB.
        idbWrite('faq', {
            'faq_id': faq_id,
            'answer': btoa_plus(elements['answer_front'].value)
        })
            .catch((reason) => console.error(reason))
            .finally(() => {
                setCaptchaForm()
                elements['main_form'].requestSubmit()
            })
    }
}
