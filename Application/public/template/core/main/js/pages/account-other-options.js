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

const main_list_group = document.getElementById('main-list-group')
for (const a_elem of main_list_group.children) {
    a_elem.onclick = function () {
        void changeActiveStatus(this)
    }
}


function changeActiveStatus(elem) {
    const as_coll = elem.parentElement.getElementsByTagName('li')
    for (let i = 0; i < as_coll.length; i++) {
        if (as_coll[i] !== elem) {
            as_coll[i].getElementsByClassName('tt23699')[0].classList.add('d-none')
            as_coll[i].setAttribute('aria-current', 'false')
            as_coll[i].classList.remove('active')
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

function handleClick(confirmedAction) {
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

