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
<div class="container w-100 w-sm-50 p-2">
    <h1 class="text-end">Login</h1>
    <form method="post" enctype="application/x-www-form-urlencoded"
          action="<?php echo UrlUtils::baseUrl('/login/request'); ?>">
        <?php echo view_main('components/csrf'); ?>
        <div class="form-group w-100 w-sm-75">
            <label for="email">Email</label>
            <input type="text" class="form-control" id="email" name="email"
                   required autocomplete="on" minlength="6"
                   value="<?= CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'email');?>">
        </div>
        <div class="form-group w-100 w-sm-75">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password"
                   required autocomplete="current-password" minlength="8">
        </div>
        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me" value="1">
            <label class="form-check-label" for="remember_me">Remember Me</label>
        </div>
        <?php echo view_main('components/captcha'); ?>
        <div class="form-group clearfix">
            <button type="submit" class="btn btn-primary btn-primary-contrast float-start">Log In</button>
            <a href="<?php echo UrlUtils::baseUrl('google-oauth?ot=HrFFz6'); ?>"
               target="_self" title="Log in with Google&reg;. Existing accounts only."
               class="btn btn-light border border-secondary float-end px-3 py-0 m-0">Log in with
                <img width="92" height="36" alt="Google"
                     src="<?php echo UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/images/google_92_36.png'); ?>">
            </a>
        </div>
        <?php if (\env('cleandeck.ENVIRONMENT', 'production') !== 'production' &&
            \env('cleandeck.oauth2_google.local_development') === true): ?>
            <!--START-SEO-IGNORE-->
            <div class="alert alert-warning text-wrap text-break">
                <p class="h4 alert-heading">Warning</p>
                <p>
                    Variable <em>cleandeck.oauth2_google.local_development</em> is set to <em>true</em> (file .env.ini).
                </p>
                <hr>
                <p class="m-0 p-0">
                    For this reason the options <span class="underline">Sign up with Google</span> and
                    <span class="underline">Log in with Google</span> simulate successful responses
                    using the local test account<br>
                    <strong><?= \env('cleandeck.oauth2_google.local_development_account',
                            'Undefined cleandeck.oauth2_google.local_development_account'); ?></strong><br>
                    set in file .env.ini by variable <em>cleandeck.oauth2_google.local_development_account</em>
                    (this account must be created if nonexistent).
                </p>
            </div>
            <!--END-SEO-IGNORE-->
        <?php endif; ?>
    </form>
    <hr class="mt-5">
    <small>
        <a href="<?php echo UrlUtils::baseUrl('reset-password'); ?>" title="Reset Password" target="_self">Reset
                                                                                                           Password</a>
    </small>
</div>
