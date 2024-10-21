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
<div class="container  w-100 w-sm-75 w-lg-50 p-2">
    <h1 class="text-end">Search Account History</h1>
    <form method="get" action="<?php echo UrlUtils::baseUrl('/admin/account/history'); ?>">
        <div class="form-group">
            <label for="email">Account Email</label>
            <input type="text" id="email" name="email" required
                   value="<?= CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'email'); ?>"
                   class="form-control w-100 w-md-50 w-sm-75" minlength="2" autocomplete="off">
        </div>
        <div class="form-group text-end">
            <button type="submit" class="btn btn-primary btn-primary-contrast">Search</button>
        </div>
    </form>
</div>
