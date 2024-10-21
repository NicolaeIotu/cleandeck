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

use Framework\Libraries\CleanDeckStatics;
use Framework\Libraries\Cookie\CookieMessengerReader;
use Framework\Libraries\Utils\UrlUtils;

$cmsg = CleanDeckStatics::getCookieMessage();
$cmsg_form_data = $cmsg['cmsg_form_data'] ?? [];

?>
<div class="container w-100 w-md-50 p-2">
    <h1 class="text-end">Contact</h1>
    <form method="post" enctype="application/x-www-form-urlencoded"
          action="<?php echo UrlUtils::baseUrl('/contact/request'); ?>">
        <?php echo view_main('components/csrf'); ?>
        <div class="form-group">
            <label for="email">Your Email</label>
            <input type="email" class="form-control" id="email" name="email" minlength="6"
                   value="<?= CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'email'); ?>"
                   required autocomplete="off">
        </div>
        <div class="form-group">
            <label for="message">Your Message</label>
            <textarea class="form-control" aria-label="Your Message" id="message" name="message" required
                      minlength="100" maxlength="2000" rows="5" autocomplete="on"
                      aria-required="true"><?= CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'message'); ?></textarea>
        </div>
        <?php echo view_main('components/captcha'); ?>
        <div class="form-group text-end">
            <button type="submit" class="btn btn-primary btn-primary-contrast">Contact</button>
        </div>
    </form>
    <hr class="mt-5">
    <small>
        <a href="<?php echo UrlUtils::baseUrl('faqs'); ?>" title="FAQs" target="_self">FAQs</a>
    </small>
</div>
