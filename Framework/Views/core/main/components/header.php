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
$is_seo_page = CleanDeckStatics::isSeoPage() && \env('cleandeck.ENVIRONMENT') !== 'staging';
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
<body>
<nav class="navbar navbar-expand-md border-bottom border-secondary-subtle text-bg-light fixed-top bg-body-tertiary main-color-scheme">
    <div id="main-nav" class="container p-0 mt-0 mb-0">
        <p class="m-0 p-0 ps-3">
            <a class="navbar-brand fs-4" href="<?php echo UrlUtils::baseUrl(); ?>"
               target="_self" title="<?= $site_brand ?>"><?= $site_brand ?></a>
            <?php if (strlen((string)$page_name) > 0): ?>
                <span class="navbar-brand d-none d-sm-inline text-wrap text-break"><?= $page_name ?></span>
            <?php endif; ?>
        </p>

        <button class="navbar-toggler mb-1 me-3" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <div class="me-auto"></div>
            <ul class="navbar-nav px-3">
                <?php if ($cleandeck_authenticated): ?>
                    <?php if (CleanDeckStatics::isEmployee()): ?>
                        <li class="nav-item dropdown">
                        <span class="nav-link dropdown-toggle border badge text-bg-light me-2 mb-1 p-2 cursor-pointer"
                              title="Employment" role="button" data-bs-toggle="dropdown"
                              aria-haspopup="true" aria-expanded="false">Employment</span>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li class="nav-item px-1">
                                    <?= UrlUtils::dropdown_anchor('/agreements/employee', 'Employee Agreements'); ?>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <?php if ($cleandeck_account_rank >= 50): ?>
                        <li class="nav-item dropdown">
                  <span class="nav-link dropdown-toggle border badge text-bg-light me-2 mb-1 p-2 cursor-pointer"
                        title="Admin" role="button" data-bs-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">Admin</span>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li class="nav-item px-1">
                                    <?= UrlUtils::dropdown_anchor('/administration', 'Administration Panel'); ?>
                                </li>
                                <li>
                                    <hr class="dropdown-divider bg-light">
                                </li>
                                <li class="dropdown-header"
                                    title="FAQs Control Center"><strong>FAQs</strong></li>
                                <li class="nav-item px-1">
                                    <?= UrlUtils::dropdown_anchor('/faqs', 'Search FAQs', 'FAQs'); ?>
                                </li>
                                <li class="nav-item px-1">
                                    <?= UrlUtils::dropdown_anchor('/admin/faq/new', 'New FAQ'); ?>
                                </li>
                                <li>
                                    <hr class="dropdown-divider bg-light">
                                </li>
                                <li class="dropdown-header"
                                    title="Articles Control Center"><strong>Articles</strong></li>
                                <li class="nav-item px-1">
                                    <?= UrlUtils::dropdown_anchor('/articles', 'Search Articles', 'Articles'); ?>
                                </li>
                                <li class="nav-item px-1">
                                    <?= UrlUtils::dropdown_anchor('/admin/article/new', 'New Article'); ?>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                    <span class="nav-link dropdown-toggle border badge text-bg-light p-2 cursor-pointer"
                          title="Account" role="button" data-bs-toggle="dropdown"
                          aria-haspopup="true" aria-expanded="false">Account</span>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="nav-item px-1">
                                <?= UrlUtils::dropdown_anchor('/user', 'Account Home'); ?>
                            </li>
                            <li>
                                <hr class="dropdown-divider bg-light">
                            </li>
                            <li class="nav-item px-1">
                                <?= UrlUtils::dropdown_anchor('/change-mfa', 'Change MFA'); ?>
                            </li>
                            <li class="nav-item px-1">
                                <?= UrlUtils::dropdown_anchor('/change-user-details', 'Change Account Details'); ?>
                            <li>
                                <hr class="dropdown-divider bg-light">
                            </li>
                            <li class="nav-item px-1">
                                <?= UrlUtils::dropdown_anchor('/account-details', 'Account Details'); ?>
                            </li>
                            <li class="nav-item px-1">
                                <?= UrlUtils::dropdown_anchor('/active-sessions-details', 'Active Sessions'); ?>
                            </li>
                            <li class="nav-item px-1">
                                <?= UrlUtils::dropdown_anchor('/user-failed-logins', 'Failed Logins Status'); ?>
                            </li>
                            <li>
                                <hr class="dropdown-divider bg-light">
                            </li>
                            <li class="nav-item px-1">
                                <?= UrlUtils::dropdown_anchor('/support-cases', 'My Support Cases'); ?>
                            </li>
                            <li class="nav-item px-1">
                                <?= UrlUtils::dropdown_anchor('/support-cases/new', 'Open Support Case'); ?>
                            </li>
                            <li>
                                <hr class="dropdown-divider bg-light">
                            </li>
                            <li class="nav-item px-1">
                                <?= UrlUtils::dropdown_anchor('/other-options', 'Other Options'); ?>
                            </li>
                            <li>
                                <hr class="dropdown-divider bg-light">
                            </li>
                            <li class="nav-item px-1">
                                <form name="logout_form" id="logout_form" method="post"
                                      action="<?php echo UrlUtils::baseUrl('/logout'); ?>">
                                    <button type="submit" form="logout_form"
                                            class="btn bg-transparent dropdown-item nav-link">Log Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link"
                           href="<?php echo UrlUtils::baseUrl('login'); ?>" title="Log In">Log In</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                           href="<?php echo UrlUtils::baseUrl('signup'); ?>" title="Sign Up">Sign Up</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container-xxl mt-6 p-2" role="main">
    <?php if (isset($cmsg_title) || isset($cmsg_body)): ?>
        <!--START-SEO-IGNORE-->
        <?php $alert_type = isset($cmsg_is_error) && $cmsg_is_error === true ? 'alert-warning' : 'alert-info'; ?>
        <div class="alert <?= $alert_type; ?> alert-dismissible text-center fade show">
            <?php if (isset($cmsg_title)): ?>
                <p class="h4 alert-heading">
                    <?php echo $cmsg_title; ?>
                </p>
                <?php if (isset($cmsg_body)) : ?>
                    <hr>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (isset($cmsg_body)) : ?>
                <p class="mb-0 text-wrap text-break">
                    <?php echo nl2br((string)$cmsg_body); ?>
                </p>
            <?php endif; ?>
            <?php if (!isset($standard_error_page)) : ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                </button>
            <?php endif; ?>
        </div>
        <!--END-SEO-IGNORE-->
    <?php endif; ?>
    <?php if ($is_seo_page && \env('cleandeck.ENVIRONMENT') === 'development'): ?>
        <!--START-SEO-IGNORE-->
        <div class="alert alert-success">
            <p class="h4 alert-heading">SEO Keywords</p>
            <hr>
            <p class="mb-0 text-spacing-1 fw-bold">##DEVELOPMENT_PRINT_SEO_KEYWORDS##</p>
            <hr>
            <p class="m-0 p-0 small">
                This information shows only when <strong>cleandeck.ENVIRONMENT</strong> is set to
                <em>development</em>.<br>
                The keywords shown above can be changed by modifying the contents of this document.
            </p>
        </div>
        <!--END-SEO-IGNORE-->
    <?php endif; ?>
