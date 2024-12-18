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
    <h1 class="text-end text-danger">Delete Account</h1>
    <form method="post" enctype="application/x-www-form-urlencoded"
          action="<?php echo UrlUtils::baseUrl('/confirm-delete-account'); ?>">
        <?php echo view_main('components/csrf'); ?>
        <div class="form-group w-100 w-sm-75 w-md-50">
            <label for="email">Email</label>
            <input type="text" class="form-control" id="email" name="email" required minlength="6"
                   autocomplete="off">
        </div>
        <div class="form-group w-100 w-sm-75 w-md-50">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required
                   minlength="8" autocomplete="off">
        </div>
        <?php echo view_main('components/captcha'); ?>
        <div class="form-group text-end">
            <button type="submit" class="btn btn-danger">Permanently Delete Account</button>
        </div>
    </form>
</div>
