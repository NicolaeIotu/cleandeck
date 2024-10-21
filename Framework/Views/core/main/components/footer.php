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
</main>
<noscript>
    <div class="alert alert-danger" role="alert">
        <span class="alert-heading display-4">JavaScript is Disabled!</span>
        <p><span class="text-larger">Enable</span> now JavaScript to get the most out of our Website.</p>
        <hr>
        <p class="mb-0">
            This site uses cookies. By continuing to use this website, you agree to our
            <a class=alert-link" title="Terms and Conditions"
               href="<?php echo UrlUtils::baseUrl('terms-and-conditions'); ?>">Terms and Conditions</a> and our
            <a class=alert-link" title="Privacy and Cookies"
               href="<?php echo UrlUtils::baseUrl('privacy-and-cookies'); ?>">Privacy and Cookies</a> policy.
        </p>
    </div>
</noscript>
<footer id="main-footer" class="main-color-scheme text-bg-light p-3">
    <div class="container clearfix">
        <p class="float-end">Link133.com is powered by
            <a href="https://link133.com"
               title="The Original Compact Database Commander"
               target="_blank"
               class="badge badge-pill bg-success-contrast p-2 px-3 mx-1 mb-1 text-larger text-decoration-none">CMD-Auth
            </a> and
            <a href="https://link133.com/products/cmd-auth/implementations/cleandeck"
               target="_blank"
               class="badge badge-pill bg-success-contrast p-2 px-3 mx-1 mb-1 text-larger text-decoration-none">CleanDeck</a>
        </p>
    </div>
    <div class="container text-center">
        <div class="row row-cols-lg-3 mt-3">
            <div class="col m-0 p-0">
                <a class="m-0 p-0" title="Articles"
                   href="<?php echo UrlUtils::baseUrl('articles'); ?>">Articles</a>
            </div>
            <div class="col m-0 p-0">
                <a class="m-0 p-0" title="Privacy and Cookies"
                   href="<?php echo UrlUtils::baseUrl('privacy-and-cookies'); ?>">Privacy and Cookies</a>
            </div>
            <div class="col m-0 p-0">
                <a class="m-0 p-0" title="Terms and Conditions"
                   href="<?php echo UrlUtils::baseUrl('terms-and-conditions'); ?>">Terms and Conditions</a>
            </div>
        </div>
        <div class="row row-cols-lg-3 mt-3">
            <div class="col m-0 p-0">
                <a class="m-0 p-0" title="FAQs"
                   href="<?php echo UrlUtils::baseUrl('faqs'); ?>">FAQs</a>
            </div>
            <div class="col m-0 p-0">
                <a class="m-0 p-0" title="Contact"
                   href="<?php echo UrlUtils::baseUrl('contact'); ?>">Contact</a>
            </div>
        </div>
        <div class="mt-5">
            <a class="border-0" target="_blank"
               href="<?php echo UrlUtils::baseUrl('rss.xml'); ?>">
                <img class="border-0 bg-warning" src="<?php echo UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/icons/rss.png'); ?>"
                     width="36" height="36" title="RSS Feed" alt="RSS Feed"/>
            </a>
        </div>
        <div class="row mt-3">
            <div class="col-sm">
                <small class="clearfix">
                    All trademarks, service marks, service or trade names, logos and product names used
                    are property of their respective owners</small>
                <small>© 2024 Link133.com</small>
            </div>
        </div>
    </div>
</footer>
<?php
$cp_tc_cookie_name = (\env('cleandeck.cookie.prefix') ?? '') . 'cp_tc_agreed';
$cookies_agreed = $_COOKIE[$cp_tc_cookie_name] ?? '';
if ($cookies_agreed !== 'true' && !array_key_exists('isLegalPage', $GLOBALS)):?>
    <div class="modal fade" id="cookies_agreed" tabindex="-1" aria-labelledby="cookies_agreedLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title text-larger">Privacy and Cookies</span>
                </div>
                <div class="modal-body">
                    This site uses cookies. By continuing to use this website, you agree to our
                    <?php echo UrlUtils::anchor_clean('privacy-and-cookies', 'Privacy and Cookies policy'); ?> and our
                    <?php echo UrlUtils::anchor_clean('terms-and-conditions', 'Terms and Conditions'); ?>.
                </div>
                <div class="modal-footer">
                    <?php $cookie_details = [
                        'full_name' => (\env('cleandeck.cookie.prefix', '')) . 'cp_tc_agreed',
                        'path' => \env('cleandeck.cookie.path', '/'),
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
</body>
</html>
