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
use Framework\Libraries\Utils\UrlUtils;

$site_brand ??= UrlUtils::getSiteBrand();

?>
<nav class="navbar navbar-expand-md border-bottom border-secondary-subtle text-bg-light fixed-top bg-body-tertiary main-color-scheme">
    <div id="main-nav" class="container p-0 mt-0 mb-0">
        <p class="m-0 p-0 ps-3">
            <a class="navbar-brand fs-4" href="<?php echo UrlUtils::baseUrl(); ?>"
               target="_self" title="<?= $site_brand ?>"><?= $site_brand ?></a>
        <?php if (isset($page_name) && strlen((string)$page_name) > 0): ?>
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
            <?php if ($cleandeck_authenticated ?? false): ?>
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
                <?php if (($cleandeck_account_rank ?? 0) >= 50): ?>

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
