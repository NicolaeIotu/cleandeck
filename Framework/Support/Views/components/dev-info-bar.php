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

use Framework\Libraries\Cookie\CookieUtils;
use Framework\Libraries\Utils\UrlUtils;

$devInfoBarStatusCookieName = env('cleandeck.cookie.prefix', '') . 'dev-info-bar';
$hasDevInfoBarCookie = CookieUtils::hasCookie($devInfoBarStatusCookieName);

$dev_info_bar_visible = true;
if ($hasDevInfoBarCookie) {
    $dev_info_bar_visible = $_COOKIE[$devInfoBarStatusCookieName] === '1';
}

$show_hide_text = $dev_info_bar_visible ? 'Hide' : 'Show';
$show_hide_class = $dev_info_bar_visible ? '' : 'd-none ';

$dev_info_bar_cookie_details = [
    'full_name' => $devInfoBarStatusCookieName,
    'path' => env('cleandeck.cookie.path', '/'),
    // Important! Lax
    'samesite' => 'Lax',
    'domain' => $_ENV['cleandeck']['cookie']['domain'],
    'secure' => $_ENV['cleandeck']['cookie']['secure'],
];

?>
<div id="dev-info-bar" class="w-100 m-0 p-0 fixed-bottom">
    <div class="position-relative m-0 p-0 m-1">
        <button id="show-hide-btn" type="button" class="btn btn-sm btn-primary"
                data-cd="<?= base64_encode(json_encode($dev_info_bar_cookie_details)) ?>">
            <?= $show_hide_text; ?> Development Info
        </button>
    </div>
    <div id="dev-info-content"
         class="<?= $show_hide_class; ?>bg-light border border-secondary rounded">
        <div class="row row-cols-1 row-cols-sm-3 m-1">
            <div class="col border-end">
                <p class="m-0 p-0 py-1">
                    <span class="fw-bolder float-start me-2">PHP time</span>
                    <span class="badge text-bg-secondary float-end text-wrap text-break">
                        <?= round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 2); ?>
                        seconds
                    </span>
                </p>
            </div>
            <div class="col border-end">
                <p class="m-0 p-0 py-1">
                    <span class="fw-bolder float-start me-2">Controller</span>
                    <span class="badge text-bg-secondary float-end text-wrap text-break">
                        <?= isset($_ENV['dev_info_bar_data'],$_ENV['dev_info_bar_data']['controller']) ?
                            $_ENV['dev_info_bar_data']['controller'] : 'Missing data'; ?>
                    </span>
                </p>
            </div>
            <div class="col border-end">
                <p class="m-0 p-0 py-1">
                    <span class="fw-bolder float-start me-2">Method</span>
                    <span class="badge text-bg-secondary float-end text-wrap text-break">
                        <?= isset($_ENV['dev_info_bar_data'],$_ENV['dev_info_bar_data']['method']) ?
                            $_ENV['dev_info_bar_data']['method'] : 'Missing data'; ?>
                    </span>
                </p>
            </div>
        </div>
        <hr class="m-0 p-0">
        <div class="row row-cols-1 row-cols-sm-3 m-1">
            <div class="col border-end">
                <p class="m-0 p-0 py-1">
                    <span class="fw-bolder float-start me-2">Template</span>
                    <span class="badge text-bg-secondary float-end text-wrap text-break">
                        <?= env('cleandeck.template'); ?>
                    </span>
                </p>
            </div>
            <div class="col border-end">
                <p class="m-0 p-0 py-1">
                    <span class="fw-bolder float-start me-2">Base URL</span>
                    <span class="badge text-bg-secondary float-end text-wrap text-break">
                        <?= env('cleandeck.baseURL'); ?>
                    </span>
                </p>
            </div>
            <div class="col border-end">
                <p class="m-0 p-0 py-1">
                    <span class="fw-bolder float-start me-2">Auth URL</span>
                    <span class="badge text-bg-secondary float-end text-wrap text-break">
                        <?= env('cleandeck.authURL'); ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>
<?= UrlUtils::script(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/dev-info-bar.js'),
    ['referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous', 'type' => 'module']);?>
