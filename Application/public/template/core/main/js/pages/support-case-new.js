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

const topicInstructions = document.getElementById('topic_instructions')
const caseTopic = document.getElementById('case_topic')
const hasAttachedProduct = document.getElementById('product_id') !== null
const hasAttachedOrder = document.getElementById('order_id') !== null

caseTopic.onchange = function topicInstructionsHandler (event) {
  const value = event.target.value
  const valueLc = value.toLowerCase()
  if ((valueLc.startsWith('product') && !hasAttachedProduct) ||
    (valueLc.startsWith('order') && !hasAttachedOrder)) {
    topicInstructions.innerText = value + ' related support cases should be opened from the ' + valueLc + ' page.'
    topicInstructions.setAttribute('class', 'd-block bg-warning m-0 px-3 py-1 text-smaller')
  } else {
    topicInstructions.setAttribute('class', 'd-none')
    topicInstructions.innerText = ''
  }
}
