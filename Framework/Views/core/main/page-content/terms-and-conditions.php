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

use Framework\Libraries\Utils\UrlUtils;

$clean_base_url = UrlUtils::baseUrl();
$clean_base_anchor = '<a href="' . $clean_base_url . '" title="' . $clean_base_url . '">' . $clean_base_url . '</a>';

$GLOBALS['isLegalPage'] = true;
?>
<div class="container-xxl safe-min-width mb-5 text-justify">
    <h1 class="display-4 pt-4">Terms and Conditions</h1>

    <h2>Introduction</h2>
    <p>These Website Standard Terms and Conditions written on this webpage shall manage your use of the website
        <span class="font-weight-bold"><?php echo $clean_base_url; ?></span> accessible at
        <?php echo $clean_base_anchor; ?>.</p>
    <p>These Terms will be applied fully and affect your use of this Website. By using this Website, you agreed
       to accept all terms and conditions written in here. You must not use this Website if you disagree with any of
       these Website Standard Terms and Conditions. </p>
</div>
