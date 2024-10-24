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

const mainListGroup = document.getElementById('main-list-group')
for (const aElem of mainListGroup.children) {
  aElem.onclick = function () {
    changeActiveStatus(this)
  }
}

function changeActiveStatus (elem) {
  const asColl = elem.parentElement.getElementsByTagName('li')
  for (let i = 0; i < asColl.length; i++) {
    if (asColl[i] !== elem) {
      asColl[i].getElementsByClassName('tt23699')[0].classList.add('d-none')
      asColl[i].setAttribute('aria-current', 'false')
      asColl[i].classList.remove('active')
    }
  }

  if (elem.classList.contains('active')) {
    elem.getElementsByClassName('tt23699')[0].classList.add('d-none')
    elem.setAttribute('aria-current', 'false')
    elem.classList.remove('active')
  } else {
    elem.getElementsByClassName('tt23699')[0].classList.remove('d-none')
    elem.setAttribute('aria-current', 'true')
    elem.classList.add('active')
  }
}

function handleClick (confirmedAction) {
  return function (event) {
    event.stopImmediatePropagation()
    confirmedAction.submit()
  }
}

const confirmedAction1 = document.getElementById('confirmedAction1')
const confirmedAction2 = document.getElementById('confirmedAction2')
const confirmedAction3 = document.getElementById('confirmedAction3')
const confirmedAction4 = document.getElementById('confirmedAction4')

confirmedAction1.addEventListener('click', handleClick(confirmedAction1))
confirmedAction2.addEventListener('click', handleClick(confirmedAction2))
confirmedAction3.addEventListener('click', handleClick(confirmedAction3))
confirmedAction4.addEventListener('click', handleClick(confirmedAction4))
