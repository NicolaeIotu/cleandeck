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
<div class="container w-100 w-sm-50 p-2 m-auto">
    <h1 class="text-end">Signup</h1>
    <form method="post" enctype="application/x-www-form-urlencoded"
          action="<?php echo UrlUtils::baseUrl('/signup/request'); ?>">
        <?php echo view_main('components/csrf'); ?>
        <div class="form-group w-100 w-sm-75">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" minlength="6"
                   value="<?= CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'email'); ?>"
                   required autocomplete="off">
        </div>
        <div class="form-group w-100 w-sm-75">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required
                   minlength="8" autocomplete="new-password">
        </div>
        <div class="form-group w-100 w-sm-75">
            <label for="password_confirmation">Password Confirmation</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                   minlength="8" autocomplete="new-password" required>
        </div>
        <?php echo view_main('components/captcha'); ?>
        <div class="form-group form-check">
            <?php $agree_tc =
                CookieMessengerReader::getPreviousFormData(
                    $cmsg_form_data,
                    'agree_tc',
                    0
                ); ?>
            <input type="checkbox" class="form-check-input" id="agree_tc" name="agree_tc" value="1"
                <?= $agree_tc === '1' ? 'checked="checked"' : ''; ?> required>
            <label class="form-check-label" for="agree_tc">Agree with
                <a href="<?php echo UrlUtils::baseUrl('terms-and-conditions'); ?>" target="_blank">
                    Terms and Conditions</a></label>
        </div>
        <div class="form-group form-check">
            <?php $agree_cookies =
                CookieMessengerReader::getPreviousFormData(
                    $cmsg_form_data,
                    'agree_cookies',
                    0
                ); ?>
            <input type="checkbox" class="form-check-input" id="agree_cookies" name="agree_cookies" value="1"
                <?= $agree_cookies === '1' ? 'checked="checked"' : ''; ?> required>
            <label class="form-check-label" for="agree_cookies">Agree with
                <a href="<?php echo UrlUtils::baseUrl('privacy-and-cookies'); ?>" target="_blank">
                    Cookies Policy</a></label>
        </div>
        <div class="form-group form-check">
            <?php $agree_privacy =
                CookieMessengerReader::getPreviousFormData(
                    $cmsg_form_data,
                    'agree_privacy',
                    0
                ); ?>
            <input type="checkbox" class="form-check-input" id="agree_privacy" name="agree_privacy" value="1"
                <?= $agree_privacy === '1' ? 'checked="checked"' : ''; ?> required>
            <label class="form-check-label" for="agree_privacy">Agree with
                <a href="<?php echo UrlUtils::baseUrl('privacy-and-cookies'); ?>" target="_blank">
                    Privacy Policy</a></label>
        </div>
        <div class="form-group">
            <button class="btn btn-outline-primary btn-outline-primary-contrast" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseExtrasSignup"
                    aria-expanded="false" aria-controls="collapseExtrasSignup">Extra Details
            </button>
            <div class="collapse border-start border-info p-2 mt-2" id="collapseExtrasSignup">
                <div class="form-group mt-1 w-100 w-sm-75">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" minlength="2"
                           value="<?= CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'username'); ?>"
                           autocomplete="off">
                </div>
                <div class="form-group w-100 w-sm-75">
                    <label for="firstname">First name</label>
                    <input type="text" class="form-control" id="firstname" name="firstname" minlength="2"
                           value="<?= CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'firstname'); ?>"
                           autocomplete="off">
                </div>
                <div class="form-group w-100 w-sm-75">
                    <label for="lastname">Last name</label>
                    <input type="text" class="form-control" id="lastname" name="lastname" minlength="2"
                           value="<?= CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'lastname'); ?>"
                           autocomplete="off">
                </div>
                <div class="form-group form-check">
                    <?php $subscribed_promotions =
                        CookieMessengerReader::getPreviousFormData(
                            $cmsg_form_data,
                            'subscribed_promotions',
                            0
                        ); ?>
                    <input type="checkbox" class="form-check-input"
                           id="subscribed_promotions" name="subscribed_promotions"
                           value="1"<?= $subscribed_promotions === '1' ? ' checked="checked"' : ''; ?>>
                    <label class="form-check-label" for="subscribed_promotions">Subscribe to our promotions</label>
                </div>
                <div class="form-group form-check">
                    <?php $subscribed_newsletter =
                        CookieMessengerReader::getPreviousFormData(
                            $cmsg_form_data,
                            'subscribed_newsletter',
                            0
                        ); ?>
                    <input type="checkbox" class="form-check-input"
                           id="subscribed_newsletter" name="subscribed_newsletter"
                           value="1"<?= $subscribed_newsletter === '1' ? ' checked="checked"' : ''; ?>>
                    <label class="form-check-label" for="subscribed_newsletter">Subscribe to our newsletter</label>
                </div>
            </div>
        </div>
        <div class="form-group clearfix">
            <button type="submit" class="btn btn-primary btn-primary-contrast float-start">Sign Up</button>
            <a href="<?php echo UrlUtils::baseUrl('google-oauth?ot=ObpKPV'); ?>" target="_self"
               title="Your Google&reg; email will be used to create a fresh account"
               class="btn btn-light border border-secondary float-end px-3 py-0 m-0">Sign up with
                <img width="92" height="36" alt="Google"
                     src="<?php echo UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/images/google_92_36.png'); ?>">
            </a>
        </div>
        <?php if (\env('cleandeck.ENVIRONMENT', 'production') !== 'production' &&
            \env('cleandeck.oauth2_google.local_development') === true): ?>
            <!--START-SEO-IGNORE-->
            <div class="alert alert-warning text-wrap text-break" role="alert">
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
</div>
