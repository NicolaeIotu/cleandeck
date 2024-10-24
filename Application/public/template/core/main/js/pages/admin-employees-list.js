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

const employeesList = document.getElementById('employees-list')
const employeesListChildren = employeesList.children

function hideAllDetails () {
  for (let i = 0; i < employeesListChildren.length; i++) {
    employeesListChildren.item(i).getElementsByTagName('div')[0].classList.add('d-none')
  }
}

for (let i = 0; i < employeesListChildren.length; i++) {
  employeesListChildren.item(i).onclick = function (event) {
    if (!event.target.hasAttribute('href')) {
      const currentTargetClassList = event.currentTarget.getElementsByTagName('div')[0].classList
      const targetIsVisible = !currentTargetClassList.contains('d-none')
      hideAllDetails()
      if (!targetIsVisible) {
        currentTargetClassList.remove('d-none')
      }
    }
  }
}
