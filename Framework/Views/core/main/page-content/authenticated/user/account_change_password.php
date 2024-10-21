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
<div class="container w-100 w-sm-50 p-2">
    <h1 class="text-end">Change Password</h1>
    <form method="post" enctype="application/x-www-form-urlencoded"
          action="<?php echo UrlUtils::baseUrl('/change-password/request'); ?>">
        <?php echo view_main('components/csrf'); ?>
        <div class="form-group">
            <label for="password_existing">Existing Password</label>
            <input type="password" id="password_existing" name="password_existing" required
                   class="form-control w-100 w-md-50 w-sm-75"
                   minlength="8" autocomplete="current-password">
        </div>
        <div class="form-group">
            <label for="new_password">New password</label>
            <input type="password" id="new_password" name="new_password" required
                   class="form-control w-100 w-md-50 w-sm-75"
                   minlength="8" autocomplete="new-password">
        </div>
        <div class="form-group">
            <label for="new_password_confirmation">New password confirmation</label>
            <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                   class="form-control w-100 w-md-50 w-sm-75"
                   required minlength="8" autocomplete="new-password">
        </div>
        <?php echo view_main('components/captcha'); ?>
        <div class="form-group text-end">
            <button type="submit" class="btn btn-primary btn-primary-contrast">Change Password</button>
        </div>
    </form>
</div>
