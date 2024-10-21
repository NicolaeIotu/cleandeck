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


const topic_instructions = document.getElementById('topic_instructions')
const case_topic = document.getElementById('case_topic')
const has_attached_product = document.getElementById('product_id') !== null
const has_attached_order = document.getElementById('order_id') !== null


case_topic.onchange = function topicInstructionsHandler(event) {
    const value = event.target.value
    const value_lc = value.toLowerCase()
    if ((value_lc.startsWith('product') && !has_attached_product) ||
        (value_lc.startsWith('order') && !has_attached_order)) {
        topic_instructions.innerText = value + ' related support cases should be opened from the ' + value_lc + ' page.'
        topic_instructions.setAttribute('class', 'd-block bg-warning m-0 px-3 py-1 text-smaller')
    } else {
        topic_instructions.setAttribute('class', 'd-none')
        topic_instructions.innerText = ''
    }
}
