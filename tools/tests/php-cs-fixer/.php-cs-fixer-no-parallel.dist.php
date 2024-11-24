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

$project_root_dir = dirname(__DIR__, 3);

include __DIR__ . '/.php-cs-fixer-basic-setup.php';

if (!isset($setup)) {
    echo 'Invalid setup of php-cs-fixer. See ' . __FILE__;
    exit(1);
}

return $setup;
