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
use Framework\Libraries\Utils\TextUtils;
use Framework\Libraries\Utils\TimeUtils;
use Framework\Libraries\Utils\UrlUtils;

$h1_text = null;
$cleandeck_user_details = CleanDeckStatics::getUserDetails();

if (isset($cleandeck_user_details['firstname'])) {
    $h1_text = ucwords((string)$cleandeck_user_details['firstname']);
} elseif (isset($cleandeck_user_details['lastname'])) {
    $h1_text = ucwords((string)$cleandeck_user_details['lastname']);
} elseif (isset($cleandeck_user_details['username'])) {
    $h1_text = $cleandeck_user_details['username'];
}

if (isset($articles, $articles['result']) &&
    is_array($articles['result'])) {
    $articles_array = $articles['result'];
}

$account_overview_has_count = false;
if (isset($account_overview) && is_array($account_overview)) {
    if (!isset($h1_text) && array_key_exists('username', $account_overview)) {
        $h1_text = $account_overview['username'];
    } elseif (!isset($h1_text) && array_key_exists('email', $account_overview)) {
        $h1_text = $account_overview['email'];
    }

    if (array_key_exists('count', $account_overview) && !empty($account_overview['count'])) {
        $account_overview_has_count = true;
    }
}

$is_employee = CleanDeckStatics::isEmployee();

?>
<div class="container safe-min-width w-100 p-0">
    <h1 class="display-4 text-start m-0"><?php echo $h1_text; ?></h1>
    <p class="lead text-start"><?php echo gmdate("Y/m/d"); ?></p>
    <div class="row align-items-start text-start mt-4">
        <div class="col-sm-12 col-md-7 p-sm-3 p-md-2 m-0 mb-2">
            <h2>Highlights</h2>
            <?php if (isset($articles_array) && !empty($articles_array)) : ?>
                <ul class="list-group list-group-flush text-justify">
                    <?php foreach ($articles_array as $article_array) : ?>
                        <?php if (isset($article_array['article_id'], $article_array['article_title'], $article_array['creation_timestamp'])) : ?>
                            <li class="list-group-item d-flex justify-content-between align-items-start border-0">
                                <div class="fw-bold">
                                    <span class="badge text-bg-light border border-secondary rounded-pill">Article</span>
                                    <span class="badge text-bg-secondary rounded-pill ms-2 me-2">
                                        <?= TimeUtils::timestampToDateString($article_array['creation_timestamp'], 'Y-m-d T'); ?></span>
                                    <a title="<?= $article_array['article_title']; ?>"
                                       href="<?= UrlUtils::baseUrl('/article/' . $article_array['article_id']); ?>"
                                       class="btn btn-link">
                                        <?= $article_array['article_title']; ?></a>
                                </div>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p>No articles available.</p>
            <?php endif; ?>
            <?php if ($is_employee) : ?>
                <div class="w-100 mt-5">
                    <h2>Applicable Agreements</h2>
                    <p>
                        <a title="Pending Applicable Agreements"
                           href="<?= UrlUtils::baseUrl('/agreements/employee?agreements_category=pending'); ?>"
                           class="btn btn-link">Pending Applicable Agreements</a>
                        <br>
                        <a title="All Applicable Agreements"
                           href="<?= UrlUtils::baseUrl('/agreements/employee'); ?>"
                           class="btn btn-link">All Applicable Agreements</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-sm-12 col-md-5 p-sm-3 p-md-2 m-0 mt-5 mt-sm-0">
            <?php if ($account_overview_has_count) : ?>
                <div class="w-100">
                    <h2 class="ps-4">Account Overview</h2>
                    <div>
                        <ul class="list-group">
                            <?php foreach ($account_overview['count'] as $key => $value) : ?>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div
                                            class="fw-bold"><?php echo TextUtils::prettify_camelcase_vars($key); ?></div>
                                    </div>
                                    <span
                                        class="badge badge-pill bg-success-contrast p-1 ps-3 pe-3"><?php echo $value; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
