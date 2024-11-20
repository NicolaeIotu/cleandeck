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

if (!defined('CLEANDECK_APP_PATH')) {
    return exit('No direct script access allowed');
}

if (isset($cmsg_title) || isset($cmsg_body)): ?>
    <!--START-SEO-IGNORE-->
    <?php $alert_type = isset($cmsg_is_error) && $cmsg_is_error === true ? 'alert-warning' : 'alert-info'; ?>
    <div class="alert <?= $alert_type; ?> alert-dismissible text-center fade show">
        <?php if (isset($cmsg_title)): ?>
            <p class="h4 alert-heading">
                <?php echo $cmsg_title; ?>
            </p>
            <?php if (isset($cmsg_body)) : ?>
                <hr>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (isset($cmsg_body)) : ?>
            <p class="mb-0 text-wrap text-break">
                <?php echo nl2br((string)$cmsg_body); ?>
            </p>
        <?php endif; ?>
        <?php if (!isset($standard_error_page)) : ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            </button>
        <?php endif; ?>
    </div>
    <!--END-SEO-IGNORE-->
<?php endif; ?>
