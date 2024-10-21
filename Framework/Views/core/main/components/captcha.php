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

use Framework\Libraries\Captcha\CustomCaptchaConstants;
use Framework\Libraries\Utils\TimeUtils;
use Framework\Libraries\Utils\UrlUtils;

$refresh_interval = (CustomCaptchaConstants::CAPTCHA_COOKIE_LIFETIME - 5);

?>
<div id="captcha_holder" class="d-none m-0 mb-3 p-2 pb-0 border border-light-subtle rounded">
    <div class="row row-cols-auto text-sm-start m-0 p-0">
        <div class="col m-0 p-0">
            <img id="captcha_image"
                 width="240" height="60"
                 class="border border-info" alt="Captcha Content">
        </div>
        <div class="col m-0 p-0">
            <div class="m-0 mb-1 ms-1 p-0 text-center small fw-bold underline cursor-default">
                    <span title="Captcha refreshes periodically every <?= $refresh_interval; ?> seconds"
                          class="m-0 p-0">i</span>
            </div>
            <div id="reload_spinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status">
                <span class="sr-only visually-hidden">Loading...</span>
            </div>
            <div class="m-0 p-0">
                <img id="captcha_reload"
                     src="<?= UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/icons/refresh.png'); ?>"
                     data-url="<?= UrlUtils::baseUrl('/captcha'); ?>"
                     data-ri="<?= $refresh_interval; ?>"
                     alt="Refresh Captcha" title="Refresh Captcha"
                     class="clearfix ms-1 cursor-pointer" width="24" height="24">
            </div>
        </div>
    </div>
    <div class="text-sm-start m-0 p-0 mb-1 ps-2 pe-2 small text-danger text-wrap text-break">
        <span id="reload_errors"></span>
    </div>
    <div class="m-0 p-0">
        <input type="text" class="form-control m-0" id="captcha_code" name="captcha_code"
               autocomplete="off" minlength="2" maxlength="50" required>
        <span id="captcha_reload_sizing_shadow" class="m-0 p-0"></span>
    </div>
    <?php try {
        $cc_suffix = \md5(\random_bytes(8));
    } catch (\Exception $exception) {
        $cc_suffix = TimeUtils::dateToTimestamp('now');
    }; ?>
    <input type="hidden" id="cc_suffix" name="cc_suffix" value="<?= $cc_suffix; ?>">
    <label class="form-check-label m-0 p-0 small" for="captcha_code">
        Enter the characters shown above<br>
        (case-insensitive)</label>
</div>
<?php if (!defined('CAPTCHA_JS_SCRIPT')): ?>
    <?= UrlUtils::script(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/captcha.js'),
        ['defer' => 'true', 'referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous']); ?>
    <?php define('CAPTCHA_JS_SCRIPT', true); endif; ?>
