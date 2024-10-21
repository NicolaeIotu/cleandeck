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

const elements = {}
const elements_ids = [
    'rankings', 'rank1_container', 'rank2_container',
    'rank_errors', 'nmdiv', 'close-case',
    'new-message', 'confirm_case_close'
]
elements_ids.forEach((id) => {
    elements[id] = document.getElementById(id)
})

const csrf_token_elements_collection = document.getElementsByTagName('input')

const rankings_url = elements['rankings'].getAttribute('data-url')
const star_grey_url = elements['rankings'].getAttribute('data-star-grey')
const star_yellow_url = elements['rankings'].getAttribute('data-star-yellow')
const case_id = elements['rankings'].getAttribute('data-case-id')
const ranking_name_1 = elements['rank1_container'].getAttribute('data-ranking-name')
const ranking_name_2 = elements['rank2_container'].getAttribute('data-ranking-name')
const r1_initial_stars = elements['rank1_container'].getAttribute('data-initial-stars')
const r2_initial_stars = elements['rank2_container'].getAttribute('data-initial-stars')


if (elements['new-message']) {
    elements['new-message'].onclick = function newMessage(event) {
        const tgt = event.target

        if (tgt.getAttribute("data-active") === "true") {
            elements['nmdiv'].style.display = 'none'
            tgt.classList.replace('bg-dark', 'bg-primary')
            tgt.innerText = "Add Message"
            tgt.setAttribute("data-active", "false")
        } else {
            elements['nmdiv'].style.display = 'block'
            tgt.classList.replace('bg-primary', 'bg-dark')
            tgt.innerText = "Hide New Message"
            tgt.setAttribute("data-active", "true")
        }
    }
}


if (elements['confirm_case_close']) {
    // Bootstrap js is always loaded in header.php
    // eslint-disable-next-line no-undef
    const confirm_case_close_modal = new bootstrap.Modal('#confirm_case_close', {
        backdrop: 'static',
        keyboard: true,
        focus: true
    })
    elements['close-case'].onclick = function showConfirmCaseClose() {
        void confirm_case_close_modal.show(elements['confirm_case_close'])
    }
}


let ajaxUpdateRank_in_progress = false
const case_is_opened = elements['nmdiv'] !== null
const maximum_allowed_rankings_1 = 5
const maximum_allowed_rankings_2 = 5
let count_rankings_1 = 0
let count_rankings_2 = 0
const csrf_token_array = Array.from(csrf_token_elements_collection).filter((elem) => {
    // update when required
    return (elem.getAttribute('type') === 'hidden' &&
        elem.getAttribute('name') !== 'case_id')
})

let token_name
let token_value

if (case_is_opened) {
    if (csrf_token_array.length === 0) {
        console.error('Something went wrong. Cannot find any csrf_token input elements on this page.')
    } else {
        token_name = csrf_token_array[0].getAttribute('name')
        // token_value is retrieved by an AJAX request
        csrf_token_array.forEach((elem) => {
            elem.onchange = function () {
                token_value = elem.value
            }
        })
    }
}


function showStarRank(_elem, _rank) {
    const rank_int = parseInt(_rank, 10)
    if (!Number.isFinite(rank_int) || rank_int < 1 || rank_int > 5) {
        console.error('Invalid rank.')
        return
    }

    const img_coll = _elem.getElementsByTagName('img')
    for (let i = 0; i < img_coll.length; i++) {
        if (img_coll[i].hasAttribute('src')) {
            img_coll[i].setAttribute('src',
                i < rank_int ? star_yellow_url : star_grey_url
            )
        }
    }
}

function showBorderRank(_elem, is_error) {
    const class_border = is_error ? 'border-danger' : 'border-success'
    _elem.classList.remove('border-danger', 'border-success')
    _elem.classList.add(class_border)

    setTimeout(() => {
        _elem.classList.remove('border-danger', 'border-success')
    }, 3000)
}

function updateCSRF_HASH(data_obj) {
    try {
        if (Object.prototype.hasOwnProperty.call(data_obj, 'csrf_hash')) {
            token_value = data_obj['csrf_hash']
        } else {
            console.error('Could not obtain an updated csrf_hash. The next ranking operation may fail.')
            return
        }
    } catch (e) {
        console.error('Could not update csrf_hash. The next ranking operation may fail.')
        console.error(e.message)
        return
    }

    // Important
    if (csrf_token_array.length > 0) {
        csrf_token_array.forEach((input_elem) => {
            input_elem.setAttribute('value', data_obj['csrf_hash'])
        })
    }
}

/**
 *
 * @param {Response} result
 * @param {string} error_message
 * @param {boolean} is_rank1_container
 */
function fetchHandleError(result, error_message, is_rank1_container) {
    const csrf_security_fail = error_message === 'CSRF_SECURITY_FAIL'
    if (csrf_security_fail) {
        setTimeout(() => document.location.reload(), 2000)
    }

    try {
        updateCSRF_HASH(result)

        console.error(error_message)
        elements['rank_errors'].textContent = error_message
        // eslint-disable-next-line no-empty
    } catch {
    }

    showBorderRank(is_rank1_container ? elements['rank1_container'] : elements['rank2_container'], true)

    // Important!
    if (!csrf_security_fail) {
        ajaxUpdateRank_in_progress = false
    }
}

async function ajaxUpdateRank(ranking_name, ranking_value) {
    if (ajaxUpdateRank_in_progress) {
        return
    }
    const is_rank1_container = ranking_name === ranking_name_1
    if ((is_rank1_container && (count_rankings_1 >= maximum_allowed_rankings_1)) ||
        (!is_rank1_container && (count_rankings_2 >= maximum_allowed_rankings_2))) {
        elements['rank_errors'].textContent = 'Maximum allowed number of rankings reached.'
        return
    }

    ajaxUpdateRank_in_progress = true
    elements['rank_errors'].textContent = ''

    const headers = new Headers()
    headers.append('X-Requested-With', 'XMLHttpRequest')
    headers.append('content-type', 'application/x-www-form-urlencoded; charset=UTF-8')

    // no cache
    const response = await fetch(rankings_url + '?' + Date.now(),
        {
            method: 'POST',
            headers: headers,
            body: 'case_id=' + case_id + '&' + ranking_name + '=' + ranking_value +
                '&' + token_name + '=' + token_value
        })

    if (response.ok) {
        const response_json = await response.json()

        is_rank1_container ? count_rankings_1++ : count_rankings_2++

        // IMPORTANT: update csrf_hash
        updateCSRF_HASH(response_json)

        showStarRank(is_rank1_container ? elements['rank1_container'] : elements['rank2_container'], ranking_value)
        showBorderRank(is_rank1_container ? elements['rank1_container'] : elements['rank2_container'], false)
        elements['rank_errors'].textContent = ''

        // Important!
        ajaxUpdateRank_in_progress = false
    } else {
        let statusText
        if(!response.bodyUsed) {
            try {
                statusText = await response.text()
            } catch (e) {
                try {
                    statusText = await response.json()
                } catch {
                    // nothing to do here
                }
            }
        }
        const error_message = 'Error ' + response.status +
            (statusText ? (': ' + statusText) : '')
        fetchHandleError(response, error_message, is_rank1_container)
    }
}

if (case_is_opened) {
    for (const img of elements['rank1_container'].children) {
        img.onclick = function () {
            void ajaxUpdateRank(ranking_name_1, img.getAttribute('data-stars'))
        }
    }
    for (const img of elements['rank2_container'].children) {
        img.onclick = function () {
            void ajaxUpdateRank(ranking_name_2, img.getAttribute('data-stars'))
        }
    }
}

// onload adjust the ranking
if (typeof r1_initial_stars === 'string' && r1_initial_stars !== '') {
    showStarRank(elements['rank1_container'], r1_initial_stars)
}
if (typeof r2_initial_stars === 'string' && r2_initial_stars !== '') {
    showStarRank(elements['rank2_container'], r2_initial_stars)
}
