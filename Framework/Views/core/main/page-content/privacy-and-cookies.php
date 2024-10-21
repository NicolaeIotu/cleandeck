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
$clean_contact_url = UrlUtils::baseUrl('/contact');
$clean_contact_anchor = '<a href="' . $clean_contact_url . '" title="Contact">contact us</a>';

$GLOBALS['isLegalPage'] = true; ?>
<div class="container-xxl safe-min-width mb-5 text-justify">
    <h1 class="display-4 pt-4">Privacy and Cookies</h1>
    <p>At <span class="font-weight-bold"><?php echo $clean_base_url; ?></span>, accessible from
        <?php echo $clean_base_anchor; ?>, one of our main priorities is the privacy of our
       visitors.
       This Privacy Policy document contains types of information that is collected and recorded by
        <span class="font-weight-bold"><?php echo $clean_base_url; ?></span> and how we use it.
    </p>
    <p>If you have additional questions or require more information about our Privacy Policy,
       do not hesitate to <?php echo $clean_contact_anchor; ?>.</p>
    <p>This Privacy Policy applies only to our online activities and is valid for visitors to our website in regard
       to the information that they shared and/or collect at
        <span class="font-weight-bold"><?php echo $clean_base_url; ?></span>.
       This policy is not applicable to any information collected offline or via channels other than this website.
    </p>

    <h2>Content</h2>
    <p>Privacy and Cookies content.</p>
</div>
