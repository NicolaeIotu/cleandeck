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
import {idbRead, idbWrite, idbDelete} from '../idb-utils.js'

const elements = {}
const elements_ids = [
    'main_form', 'front_form', 'delete_form',
    'captcha_code', 'cc_suffix', 'delete_agreement_btn',
    'cancel_delete_agreement_btn', 'controlled_submit', 'agreement_title_front',
    'agreement_type_front', 'for_employee_types_front', 'for_email_front',
    'agreement_content_front', 'agreement_content_original'
]
elements_ids.forEach((id) => {
    elements[id] = document.getElementById(id)
})

const agreement_id = elements['main_form'].getAttribute('data-agreement-id')

elements['main_form'].onkeydown = function (event) {
    if ('string' === typeof event.key && 'enter' === event.key.toLowerCase()) {
        // this is the only multiline form element at the moment
        if (!(event.target instanceof HTMLTextAreaElement)) {
            event.preventDefault()
            void controlledSubmit()
        }
        event.stopPropagation()
    }
}

if (elements['delete_agreement_btn'] !== null) {
    elements['delete_agreement_btn'].onclick = function () {
        void controlledDelete()
    }
}
if (elements['cancel_delete_agreement_btn'] !== null) {
    elements['cancel_delete_agreement_btn'].onclick = function () {
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

const delete_text = ['Delete agreement', 'Please confirm deletion', 'DELETE - Final Confirmation']
const count_delete_stages = delete_text.length
let delete_stage_index = 0

function controlledDelete() {
    delete_stage_index++
    if (delete_stage_index >= count_delete_stages) {
        setCaptchaForm(true)
        elements['delete_form'].requestSubmit()
    } else {
        if (elements['cancel_delete_agreement_btn']) {
            elements['cancel_delete_agreement_btn'].classList.remove('d-none')
        }
        elements['delete_agreement_btn'].textContent = delete_text[delete_stage_index]
    }
}

function cancelDelete() {
    delete_stage_index = 0
    elements['delete_agreement_btn'].textContent = delete_text[delete_stage_index]
    if (elements['cancel_delete_agreement_btn']) {
        elements['cancel_delete_agreement_btn'].classList.add('d-none')
    }
}

const backbone = {
    'agreement_title':
        {
            content: elements['agreement_title_front'].getAttribute('data-content'),
            convert: elements['agreement_title_front'].getAttribute('data-convert') === 'true'
        },
    'agreement_type':
        {
            content: elements['agreement_type_front'].getAttribute('data-content'),
            convert: elements['agreement_type_front'].getAttribute('data-convert') === 'true'
        },
    'for_employee_types':
        {
            content: elements['for_employee_types_front']
                .getAttribute('data-content')
                .replace(/(^,)|(,$)/g, ''),
            convert: elements['for_employee_types_front'].getAttribute('data-convert') === 'true'
        },
    'for_email':
        {
            content: elements['for_email_front'].getAttribute('data-content'),
            convert: elements['for_email_front'].getAttribute('data-convert') === 'true'
        }
}

idbRead('agreement', ['agreement_id', 'agreement_content'])
    .then((agreement_data) => {
        // don't wait for deletion
        idbDelete('agreement', ['agreement_id', 'agreement_content'])
            .catch((reason) => {
                console.error('Failed to remove previous agreement_content: ' + reason)
            })

        try {
            // ID verification is critical!
            if (agreement_data.agreement_id === agreement_id &&
                typeof agreement_data.agreement_content === 'string') {
                backbone['agreement_content'] = {
                    // decode content first
                    content: atob_plus(agreement_data.agreement_content),
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
            const agreement_content_original_content = elements['agreement_content_original'].textContent.trim()
            backbone['agreement_content'] = {
                content: agreement_content_original_content
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
                        Object.prototype.hasOwnProperty.call(backbone, 'agreement_content')) {
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

    if (elements['front_form'].reportValidity()) {
        // Save agreement_content in IndexedDB because the content is too large.
        // In case of failures the previous agreement_content will be retrieved
        // safely from the local IndexedDB.
        idbWrite('agreement', {
            'agreement_id': agreement_id,
            'agreement_content': btoa_plus(elements['agreement_content_front'].value)
        })
            .catch((reason) => console.error(reason))
            .finally(() => {
                setCaptchaForm()
                elements['main_form'].requestSubmit()
            })
    }
}
