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

if (($is_seo_page ?? false) && env('cleandeck.ENVIRONMENT') === 'development'): ?>
    <!--START-SEO-IGNORE-->
    <div class="alert alert-success">
        <p class="h4 alert-heading">SEO Keywords</p>
        <hr>
        <p class="mb-0 text-spacing-1 fw-bold">##DEVELOPMENT_PRINT_SEO_KEYWORDS##</p>
        <hr>
        <p class="m-0 p-0 small">
            This information shows only when <strong>cleandeck.ENVIRONMENT</strong> is set to
            <em>development</em>.<br>
            The keywords shown above can be changed by modifying the contents of this document.
        </p>
    </div>
    <!--END-SEO-IGNORE-->
<?php endif; ?>
