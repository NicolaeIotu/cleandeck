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
<div class="container w-100 w-sm-75 w-lg-50 p-2">
    <h1 class="text-end">Change Primary Email</h1>
    <form method="post" enctype="application/x-www-form-urlencoded"
          action="<?php echo UrlUtils::baseUrl('/change-primary-email/request'); ?>">
        <?php echo view_main('components/csrf'); ?>
        <div class="form-group">
            <label for="new_primary_email">New Primary Email</label>
            <input type="email" id="new_primary_email" name="new_primary_email" required
                   class="form-control w-100 w-md-50 w-sm-75" minlength="6" autocomplete="off">
        </div>
        <?php echo view_main('components/captcha'); ?>
        <div class="form-group text-end">
            <button type="submit" class="btn btn-primary btn-primary-contrast">Change Primary Email</button>
        </div>
    </form>
</div>

