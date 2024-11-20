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
use Framework\Libraries\Utils\TimeUtils;
use Framework\Libraries\Utils\UrlUtils;

$cleandeck_authenticated = CleanDeckStatics::isAuthenticated();
$cleandeck_account_rank = CleanDeckStatics::getAccountRank();
$is_seo_page = CleanDeckStatics::isSeoPage() && env('cleandeck.ENVIRONMENT') !== 'staging';
$current_url = UrlUtils::current_url();

if (isset($custom_page_name) && strlen((string)$custom_page_name) > 2) {
    $page_name = $custom_page_name;
} else {
    $page_name = UrlUtils::urlToPageTitle($current_url);
}

$page_title = $page_name;

if (strlen((string)$page_name) > 100) {
    $page_name = substr((string)$page_name, 0, 80) . '...';
}

$site_brand = UrlUtils::getSiteBrand();

$cmsg = CleanDeckStatics::getCookieMessage();
$cmsg_title = $cmsg['cmsg_title'] ?? null;
$cmsg_body = $cmsg['cmsg_body'] ?? null;
$cmsg_is_error = $cmsg['cmsg_is_error'] ?? null;
$cmsg_form_data = $cmsg['cmsg_form_data'] ?? null;

if(isset($seo_description)) {
    $seo_description = str_replace(PHP_EOL, ' ', strip_tags((string)$seo_description));
}

// Do not delete these entries:
//  - tag <meta name="keywords" content="##SEO_KEYWORDS##">
//  - comment <!--START-SEO-IGNORE-->
//  - comment <!--END-SEO-IGNORE-->

// SEO description if any, must be provided by Controller through variable $seo_description

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<?php /* Do not modify below block 'if...' + '<meta name="keywords" ... ' */ ?>
    <?php if ($is_seo_page): ?>
<meta name="keywords" content="##SEO_KEYWORDS##">
    <?php endif; ?>
<?php /* SEO description if any, must be provided by Controller. */ ?>
<meta name="description" content="<?= $seo_description ?? $page_title; ?>">
    <meta name="robots" content="<?php echo $is_seo_page ? 'index,follow' : 'noindex,nofollow'; ?>"/>
    <meta name="rating" content="general">
    <meta name="classification" content="software, website">
    <meta name="copyright" content="© <?php echo TimeUtils::getYearNow() . ' ' . $site_brand; ?>">
    <meta name="owner" content="<?= $site_brand; ?>">
    <link rel="shortcut icon" href="<?php echo UrlUtils::baseUrl('favicon.ico'); ?>"
          referrerpolicy="no-referrer" crossorigin="anonymous" />
    <title><?php echo ucfirst((string)($page_title === '' ? parse_url(UrlUtils::baseUrl(), PHP_URL_HOST) : $page_title)); ?></title>
    <?= UrlUtils::link(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/css/lib/bootstrap.min.css'),
        ['type' => 'text/css', 'rel' => 'stylesheet', 'referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous']);?>
    <?= UrlUtils::link(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/css/main.css'),
        ['type' => 'text/css', 'rel' => 'stylesheet', 'referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous']);?>
    <?= UrlUtils::script(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/lib/bootstrap.bundle.min.js'),
        ['referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous']);?>
<?php if (CleanDeckStatics::isCaptcha()): ?>
    <?= UrlUtils::link(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/css/captcha.css'),
        ['type' => 'text/css', 'rel' => 'stylesheet']);?>
<?php endif; ?>
</head>
