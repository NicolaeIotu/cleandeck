<?php

/*
 * CleanDeck for CMD-Auth (https://link133.com) and other similar applications
 *
 * Copyright (c) 2023-2024 Iotu Nicolae, nicolae.g.iotu@link133.com
 * Licensed under the terms of the MIT License (MIT)
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

/////////////////
// DIRECTORIES //
/////////////////


defined('CLEANDECK_TEMPLATE_PATH') || define('CLEANDECK_TEMPLATE_PATH',
    CLEANDECK_PUBLIC_PATH . '/template/' . env('cleandeck.template', 'core'));
defined('CLEANDECK_TEMPLATE_URI') || define('CLEANDECK_TEMPLATE_URI',
     '/template/' . env('cleandeck.template', 'core'));
