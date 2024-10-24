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
const elementsIds = [
  'rankings', 'rank1_container', 'rank2_container',
  'rank_errors', 'nmdiv', 'close-case',
  'new-message', 'confirm_case_close'
]
elementsIds.forEach((id) => {
  elements[id] = document.getElementById(id)
})

const csrfTokenElementsCollection = document.getElementsByTagName('input')

const rankingsUrl = elements.rankings.getAttribute('data-url')
const starGreyUrl = elements.rankings.getAttribute('data-star-grey')
const starYellowUrl = elements.rankings.getAttribute('data-star-yellow')
const caseId = elements.rankings.getAttribute('data-case-id')
const rankingName1 = elements.rank1_container.getAttribute('data-ranking-name')
const rankingName2 = elements.rank2_container.getAttribute('data-ranking-name')
const r1InitialStars = elements.rank1_container.getAttribute('data-initial-stars')
const r2InitialStars = elements.rank2_container.getAttribute('data-initial-stars')

if (elements['new-message']) {
  elements['new-message'].onclick = function newMessage (event) {
    const tgt = event.target

    if (tgt.getAttribute('data-active') === 'true') {
      elements.nmdiv.style.display = 'none'
      tgt.classList.replace('bg-dark', 'bg-primary')
      tgt.innerText = 'Add Message'
      tgt.setAttribute('data-active', 'false')
    } else {
      elements.nmdiv.style.display = 'block'
      tgt.classList.replace('bg-primary', 'bg-dark')
      tgt.innerText = 'Hide New Message'
      tgt.setAttribute('data-active', 'true')
    }
  }
}

if (elements.confirm_case_close) {
  // Bootstrap js is always loaded in header.php
  // eslint-disable-next-line no-undef
  const confirmCaseCloseModal = new bootstrap.Modal('#confirm_case_close', {
    backdrop: 'static',
    keyboard: true,
    focus: true
  })
  elements['close-case'].onclick = function showConfirmCaseClose () {
    confirmCaseCloseModal.show(elements.confirm_case_close)
  }
}

let ajaxUpdateRankInProgress = false
const caseIsOpened = elements.nmdiv !== null
const maximumAllowedRankings1 = 5
const maximumAllowedRankings2 = 5
let countRankings1 = 0
let countRankings2 = 0
const csrfTokenArray = Array.from(csrfTokenElementsCollection).filter((elem) => {
  // update when required
  return (elem.getAttribute('type') === 'hidden' &&
    elem.getAttribute('name') !== 'case_id')
})

let tokenName
let tokenValue

if (caseIsOpened) {
  if (csrfTokenArray.length === 0) {
    console.error('Something went wrong. Cannot find any csrf_token input elements on this page.')
  } else {
    tokenName = csrfTokenArray[0].getAttribute('name')
    // tokenValue is retrieved by an AJAX request
    csrfTokenArray.forEach((elem) => {
      elem.onchange = function () {
        tokenValue = elem.value
      }
    })
  }
}

function showStarRank (_elem, _rank) {
  const rankInt = parseInt(_rank, 10)
  if (!Number.isFinite(rankInt) || rankInt < 1 || rankInt > 5) {
    console.error('Invalid rank.')
    return
  }

  const imgColl = _elem.getElementsByTagName('img')
  for (let i = 0; i < imgColl.length; i++) {
    if (imgColl[i].hasAttribute('src')) {
      imgColl[i].setAttribute('src',
        i < rankInt ? starYellowUrl : starGreyUrl
      )
    }
  }
}

function showBorderRank (_elem, isError) {
  const classBorder = isError ? 'border-danger' : 'border-success'
  _elem.classList.remove('border-danger', 'border-success')
  _elem.classList.add(classBorder)

  setTimeout(() => {
    _elem.classList.remove('border-danger', 'border-success')
  }, 3000)
}

function updateCSRFHASH (dataObj) {
  try {
    if (Object.prototype.hasOwnProperty.call(dataObj, 'csrf_hash')) {
      tokenValue = dataObj.csrf_hash
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
  if (csrfTokenArray.length > 0) {
    csrfTokenArray.forEach((inputElem) => {
      inputElem.setAttribute('value', dataObj.csrf_hash)
    })
  }
}

/**
 *
 * @param {Response} result
 * @param {string} errorMessage
 * @param {boolean} isRank1Container
 */
function fetchHandleError (result, errorMessage, isRank1Container) {
  const csrfSecurityFail = errorMessage === 'CSRF_SECURITY_FAIL'
  if (csrfSecurityFail) {
    setTimeout(() => document.location.reload(), 2000)
  }

  try {
    updateCSRFHASH(result)

    console.error(errorMessage)
    elements.rank_errors.textContent = errorMessage
    // eslint-disable-next-line no-empty
  } catch {
  }

  showBorderRank(isRank1Container ? elements.rank1_container : elements.rank2_container, true)

  // Important!
  if (!csrfSecurityFail) {
    ajaxUpdateRankInProgress = false
  }
}

async function ajaxUpdateRank (rankingName, rankingValue) {
  if (ajaxUpdateRankInProgress) {
    return
  }
  const isRank1Container = rankingName === rankingName1
  if ((isRank1Container && (countRankings1 >= maximumAllowedRankings1)) ||
    (!isRank1Container && (countRankings2 >= maximumAllowedRankings2))) {
    elements.rank_errors.textContent = 'Maximum allowed number of rankings reached.'
    return
  }

  ajaxUpdateRankInProgress = true
  elements.rank_errors.textContent = ''

  const headers = new Headers()
  headers.append('X-Requested-With', 'XMLHttpRequest')
  headers.append('content-type', 'application/x-www-form-urlencoded; charset=UTF-8')

  // no cache
  const response = await fetch(rankingsUrl + '?' + Date.now(),
    {
      method: 'POST',
      headers,
      body: 'case_id=' + caseId + '&' + rankingName + '=' + rankingValue +
        '&' + tokenName + '=' + tokenValue
    })

  if (response.ok) {
    const responseJson = await response.json()

    isRank1Container ? countRankings1++ : countRankings2++

    // IMPORTANT: update csrf_hash
    updateCSRFHASH(responseJson)

    showStarRank(isRank1Container ? elements.rank1_container : elements.rank2_container, rankingValue)
    showBorderRank(isRank1Container ? elements.rank1_container : elements.rank2_container, false)
    elements.rank_errors.textContent = ''

    // Important!
    ajaxUpdateRankInProgress = false
  } else {
    let statusText
    if (!response.bodyUsed) {
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
    const errorMessage = 'Error ' + response.status +
      (statusText ? (': ' + statusText) : '')
    fetchHandleError(response, errorMessage, isRank1Container)
  }
}

if (caseIsOpened) {
  for (const img of elements.rank1_container.children) {
    img.onclick = function () {
      ajaxUpdateRank(rankingName1, img.getAttribute('data-stars'))
    }
  }
  for (const img of elements.rank2_container.children) {
    img.onclick = function () {
      ajaxUpdateRank(rankingName2, img.getAttribute('data-stars'))
    }
  }
}

// onload adjust the ranking
if (typeof r1InitialStars === 'string' && r1InitialStars !== '') {
  showStarRank(elements.rank1_container, r1InitialStars)
}
if (typeof r2InitialStars === 'string' && r2InitialStars !== '') {
  showStarRank(elements.rank2_container, r2InitialStars)
}
