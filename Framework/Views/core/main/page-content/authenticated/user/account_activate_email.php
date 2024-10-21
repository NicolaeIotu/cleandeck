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
<div class="container w-100 w-sm-75 w-md-50 p-2">
    <h1 class="text-end">Activate Email</h1>
    <form method="post" enctype="application/x-www-form-urlencoded"
          action="<?php echo UrlUtils::baseUrl('/activate-email/request'); ?>">
        <?php echo view_main('components/csrf'); ?>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" required minlength="6"
                   value="<?= CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'email'); ?>"
                   autocomplete="on">
        </div>
        <div class="form-group">
            <label for="activation_hash">Activation Hash</label>
            <input type="text" class="form-control" id="activation_hash" name="activation_hash" required minlength="20"
                   value="<?= CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'activation_hash'); ?>"
                   autocomplete="off">
        </div>
        <?php echo view_main('components/captcha'); ?>
        <div class="form-group text-end">
            <button type="submit" class="btn btn-primary btn-primary-contrast">Activate Email</button>
        </div>
    </form>
</div>
