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
<div class="container w-100 w-lg-25 w-sm-50 p-2">
    <h1 class="text-end">Change MFA</h1>
    <form method="post" enctype="application/x-www-form-urlencoded"
          action="<?php echo UrlUtils::baseUrl('/change-mfa/request'); ?>">
        <?php echo view_main('components/csrf'); ?>
        <?php $mfa_email = false; ?>
        <?php if (isset($mfa_option) && $mfa_option === 'email'): ?>
            <?php $mfa_email = true; ?>
            <p class="badge text-bg-success-contrast text-white text-larger p-2">Email MFA is active</p>
        <?php else: ?>
            <p class="badge text-bg-warning text-dark text-larger p-2">Email MFA is inactive</p>
        <?php endif; ?>
        <div class="form-group">
            <label for="mfa_option">New MFA Option</label>
            <select class="form-select p-2" id="mfa_option" name="mfa_option">
                <option value="email"<?= $mfa_email ? ' selected' : '';?>>
                    EMAIL
                </option>
                <option value="disabled"<?= $mfa_email ? '' : ' selected';?>>
                    No MFA
                </option>
            </select>
        </div>
        <?php echo view_main('components/captcha'); ?>
        <div class="form-group text-start">
            <button type="submit" class="btn btn-primary btn-primary-contrast">Change MFA</button>
        </div>
    </form>
</div>
