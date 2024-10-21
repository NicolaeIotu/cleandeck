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

<div class="container w-100 w-sm-75 w-md-50 p-2">
    <h1 class="text-end">Login - MFA Step 2</h1>
    <form name="main" id="enter_mfa" method="post"
          action="<?php echo UrlUtils::baseUrl('/login-mfa-step-2/request'); ?>">
        <?php echo view_main('components/csrf'); ?>
        <div class="form-group w-100 w-sm-75">
            <label for="mfa_code">MFA Code</label>
            <input type="text" class="form-control" id="mfa_code" name="mfa_code"
                   required minlength="2" autocomplete="one-time-code">
        </div>
    </form>
    <form name="cancel" id="cancel_mfa" method="post"
          action="<?php echo UrlUtils::baseUrl('/login-mfa-cancel/request'); ?>">
        <?php echo view_main('components/csrf'); ?>
    </form>
    <div class="form-group clearfix">
        <button form="cancel_mfa" type="submit" class="btn btn-outline-secondary float-start">Cancel MFA</button>
        <button form="enter_mfa" type="submit" class="btn btn-primary btn-primary-contrast float-end">Enter MFA Code</button>
    </div>
</div>
