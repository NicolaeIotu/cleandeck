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

// requires a html element having the id 'scroll_to_top'
const scroll_to_top = document.getElementById('scroll_to_top')
if (scroll_to_top instanceof HTMLElement) {
    scroll_to_top.onclick = function () {
        window.scrollTo({top: 0, left: 0, behavior: 'smooth'})
        void scroll_to_top.blur()
    }
}
