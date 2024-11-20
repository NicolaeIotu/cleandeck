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

$cp_tc_cookie_name = (env('cleandeck.cookie.prefix') ?? '') . 'cp_tc_agreed';
$cookies_agreed = $_COOKIE[$cp_tc_cookie_name] ?? '';
if ($cookies_agreed !== 'true' && !array_key_exists('isLegalPage', $GLOBALS)): ?>
    <div class="modal fade" id="cookies_agreed" tabindex="-1" aria-labelledby="modal-privacy-cookies">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <span id="modal-privacy-cookies" class="modal-title text-larger">Privacy and Cookies</span>
                </div>
                <div class="modal-body">
                    This site uses cookies. By continuing to use this website, you agree to our
                    <?php echo UrlUtils::anchor_clean('privacy-and-cookies', 'Privacy and Cookies policy'); ?> and our
                    <?php echo UrlUtils::anchor_clean('terms-and-conditions', 'Terms and Conditions'); ?>.
                </div>
                <div class="modal-footer">
                    <?php $cookie_details = [
                        'full_name' => (env('cleandeck.cookie.prefix', '')) . 'cp_tc_agreed',
                        'path' => env('cleandeck.cookie.path', '/'),
                        // Important! Lax
                        'samesite' => 'Lax',
                        'domain' => $_ENV['cleandeck']['cookie']['domain'],
                        'secure' => $_ENV['cleandeck']['cookie']['secure'],
                    ]; ?>
                    <button id="privacy_cookies_btn" type="button" class="btn btn-primary btn-primary-contrast"
                            data-bs-dismiss="modal" data-cd="<?= base64_encode(json_encode($cookie_details)) ?>">
                        Understood and Agree
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?= UrlUtils::script(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/pages/footer.js'),
        ['referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous', 'type' => 'module']);?>
<?php endif; ?>
