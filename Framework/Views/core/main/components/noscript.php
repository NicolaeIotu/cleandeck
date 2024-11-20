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

?>
<noscript>
    <div class="alert alert-danger" role="alert" aria-labelledby="javascript-disabled">
        <span id="javascript-disabled" class="alert-heading display-4">JavaScript is Disabled!</span>
        <p><span class="text-larger">Enable</span> now JavaScript to get the most out of our Website.</p>
        <hr>
        <p class="mb-0">
            This site uses cookies. By continuing to use this website, you agree to our
            <a class=alert-link" title="Terms and Conditions"
               href="<?php echo UrlUtils::baseUrl('terms-and-conditions'); ?>">Terms and Conditions</a> and our
            <a class=alert-link" title="Privacy and Cookies"
               href="<?php echo UrlUtils::baseUrl('privacy-and-cookies'); ?>">Privacy and Cookies</a> policy.
        </p>
    </div>
</noscript>
