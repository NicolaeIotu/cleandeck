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

use Framework\Libraries\Utils\Pagination;
use Framework\Libraries\Utils\TimeUtils;
use Framework\Libraries\Utils\UrlUtils;

$has_unverified_accounts = false;
if (isset($unverified_details) && is_array($unverified_details) &&
    isset($unverified_details['stats']) && is_array($unverified_details['stats']) &&
    isset($unverified_details['stats']['total_users']) && is_int($unverified_details['stats']['total_users']) &&
    $unverified_details['stats']['total_users'] > 0 &&
    isset($unverified_details['result']) && is_array($unverified_details['result']) &&
    $unverified_details['result'] !== []) {
    $has_unverified_accounts = true;

    $pagination_base_url = UrlUtils::url_clean() . '?' . UrlUtils::get_query();
    $pagination = Pagination::build(
        $unverified_details['stats']['total_users'],
        $unverified_details['stats']['page_number'],
        $unverified_details['stats']['page_entries'],
        $pagination_base_url
    );
}

function getUserDetailTitle(string $raw_title): string
{
    $rt_arr = explode('_', $raw_title);
    $filtered_arr = array_filter($rt_arr, static function ($elem): bool {
        return $elem !== 'timestamp';
    });
    $uc_arr = array_map('ucfirst', $filtered_arr);
    return implode(' ', $uc_arr);
}

?>

<div class="container w-100 w-sm-50 p-2">
    <?php if (!isset($is_admin) || $is_admin !== true) : ?>
        <div class="alert alert-warning" role="alert">
            <p>Insufficient permissions</p>
        </div>
    <?php else: ?>
        <h1 class="text-end">Verify Accounts</h1>
        <?php if ($has_unverified_accounts): ?>
            <div>
                <p>Mark accounts as verified after checking the details of each account:</p>
            </div>
            <?php if (count($pagination) > 1): ?>
                <nav>
                    <ul class="pagination pagination-sm justify-content-center">
                        <?php
                        foreach ($pagination as $button_description): ?>
                            <li class="page-item <?php echo $button_description["active"] === true ? "active" :
                                ($button_description["disabled"] === true ? "disabled" : ""); ?>">
                                <?php if ($button_description["active"] === true) : ?>
                                    <span class="page-link"><?php echo $button_description["symbol"]; ?></span>
                                <?php else : ?>
                                    <a class="page-link"
                                       href="<?php echo $button_description["link"]; ?>"><?php echo $button_description["symbol"]; ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            <form method="post" enctype="application/x-www-form-urlencoded"
                  action="<?= UrlUtils::baseUrl('admin/accounts/mark-verified/request'); ?>">
                <?php echo view_main('components/csrf'); ?>
                <div class="list-group text-wrap text-break">
                    <?php $i = 0; ?>
                    <?php foreach ($unverified_details['result'] as $result): ?>
                        <?php
                        ++$i;
                        $base64_email = base64_encode((string)$result['email']);
                        ?>
                        <div class="list-group-item pb-1 mb-3 border border-secondary rounded"
                             title="<?= $result['email']; ?>">
                            <span class="h2 fw-bolder"><?php echo $result['email']; ?></span>
                            <p class="m-0 p-0">
                                <a href="<?php echo UrlUtils::baseUrl('/admin/account/history?email=' . $result['email']); ?>"
                                   title="Account Details History (must be verified)"
                                   target="_blank">Account Details History</a>
                            </p>
                            <div class="d-flex w-100 justify-content-between small m-0">
                                <ul class="m-0 w-100 list-unstyled">
                                    <?php foreach ($result as $key => $value) : ?>
                                        <?php if (isset($value) && $value > 0 && $value !== false): ?>
                                            <li class="p-1 <?php $ri ??= false;
                                            $ri = !$ri;
                                            echo($ri ? 'bg-info-subtle' : 'bg-light');?>">
                                                <div class="row">
                                                    <div class="col-4 fw-bolder bg">
                                                        <?= getUserDetailTitle($key); ?>:
                                                    </div>
                                                    <div class="col-8">
                                                        <?php if (stripos((string)$key, 'timestamp')): ?>
                                                            <?= TimeUtils::timestampToDateString($value); ?>
                                                        <?php else: ?>
                                                            <?= $value; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="form-group my-1 p-2 bg-white border rounded">
                                <div class="form-check ms-1">
                                    <input class="form-check-input text-larger border border-dark-subtle" type="radio" name="<?= $base64_email; ?>"
                                           value="1" id="verify_<?= $i; ?>">
                                    <label class="form-check-label mt-1 text-success text-success-contrast fw-bolder"
                                            for="verify_<?= $i; ?>">Mark as Verified</label>
                                </div>
                                <div class="form-check ms-1 mt-1">
                                    <input class="form-check-input text-larger border border-dark-subtle" type="radio" name="<?= $base64_email; ?>"
                                           value="0" id="later_<?= $i; ?>" checked>
                                    <label class="form-check-label" for="later_<?= $i; ?>">Decide Later</label>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php echo view_main('components/captcha'); ?>
                    <div class="form-group text-end mt-2">
                        <button type="submit" class="btn btn-primary btn-primary-contrast">Submit</button>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <p>No accounts pending verification at the moment.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>
