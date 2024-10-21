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

$primary_email = null;
$password_reset_hash = null;
if (isset($custom_data, $custom_data['primary_email'], $custom_data['password_reset_hash'])) {
    $primary_email = $custom_data['primary_email'];
    $password_reset_hash = $custom_data['password_reset_hash'];
}

?>
<div class="container w-100 w-sm-75 w-md-50 p-2">
    <?php if (isset($primary_email, $password_reset_hash)): ?>
        <h1 class="text-end">Choose a new password</h1>
        <small class="text-end">Account: <?= $primary_email; ?></small>
        <form method="post" enctype="application/x-www-form-urlencoded"
              action="<?php echo UrlUtils::baseUrl('/change-password-on-reset/request'); ?>">
            <?php echo view_main('components/csrf'); ?>
            <input type="hidden" name="primary_email"
                   value="<?= $primary_email; ?>">
            <input type="hidden" name="password_reset_hash"
                   value="<?= $password_reset_hash; ?>">
            <div class="form-group mt-3 w-100 w-sm-75">
                <label for="disable_mfa">Disable MFA</label>
                <select class="form-select w-50" id="disable_mfa" name="disable_mfa">
                    <option value="true">true</option>
                    <option value="false" selected>false</option>
                </select>
            </div>
            <div class="form-group w-100 w-sm-75">
                <label for="new_password">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required
                       minlength="8" autocomplete="new-password">
            </div>
            <div class="form-group w-100 w-sm-75">
                <label for="new_password_confirmation">New Password Confirmation</label>
                <input type="password" class="form-control" required minlength="8"
                       id="new_password_confirmation" name="new_password_confirmation"
                       autocomplete="new-password">
            </div>
            <?php echo view_main('components/captcha'); ?>
            <div class="form-group text-end">
                <button type="submit" class="btn btn-primary btn-primary-contrast">Change Password</button>
            </div>
        </form>
    <?php else: ?>
        <p class="card-text">Generic Error</p>
    <?php endif; ?>
</div>
