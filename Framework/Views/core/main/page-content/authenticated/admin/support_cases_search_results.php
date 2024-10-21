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
use Framework\Libraries\Utils\TextUtils;
use Framework\Libraries\Utils\TimeUtils;
use Framework\Libraries\Utils\UrlUtils;

$has_cases = false;
$pagination = [];

if (isset($support_cases) && is_array($support_cases) &&
    isset($support_cases['stats']) && is_array($support_cases['stats']) &&
    isset($support_cases['stats']['total_cases']) && is_int($support_cases['stats']['total_cases']) &&
    $support_cases['stats']['total_cases'] > 0 &&
    isset($support_cases['result']) && is_array($support_cases['result']) && $support_cases['result'] !== []) {
    $has_cases = true;

    $pagination_base_url = UrlUtils::url_clean() . '?' . UrlUtils::get_query();
    $pagination = Pagination::build(
        $support_cases['stats']['total_cases'],
        $support_cases['stats']['page_number'],
        $support_cases['stats']['page_entries'],
        $pagination_base_url
    );
}

function getCaseStatus(mixed $timestampOpened, mixed $timestampClosed): string
{
    if (is_int($timestampClosed) && $timestampClosed > 0) {
        return 'Closed ' . TimeUtils::timestampToDateString($timestampClosed, 'Y-m-d T');
    }
    if (!is_int($timestampOpened)) {
        return '';
    }
    if ($timestampOpened <= 0) {
        return '';
    }
    return 'Opened ' . TimeUtils::timestampToDateString($timestampOpened, 'Y-m-d T');
}

?>
<div class="container w-100 w-sm-75 w-md-50 p-2">
    <h1 class="text-end">Support Cases Search Results</h1>
    <p class="pb-2">
        <sub class="float-end text-end">
            <a href="<?php echo UrlUtils::baseUrl('support-cases/search'); ?>"
               title="Search Again" target="_self">Search Again</a>
        </sub>
    </p>
    <?php if (isset($search_details) && is_array($search_details) && $search_details !== []): ?>
        <p class="h3">Filters:</p>
        <ul>
            <?php foreach ($search_details as $key => $value): ?>
                <li><?php echo TextUtils::prettify_camelcase_vars($key) . ': ' . $value; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php if ($has_cases): ?>
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
        <div class="list-group">
            <?php foreach ($support_cases['result'] as $result): ?>
                <a href="<?php echo UrlUtils::baseUrl('support-cases/case/details/' . $result['case_id'] . '?page_number=1&page_entries=10'); ?>"
                   class="list-group-item cursor-pointer">
                    <div class="d-flex w-100">
                        <?php if (isset($result['has_unread_messages']) && $result['has_unread_messages'] === true) : ?>
                            <span class="text-danger text-larger" title="Unread Messages">&circledcirc;&nbsp;</span>
                        <?php endif; ?>
                        <p class="h5 mb-1 text-wrap text-break"><?php echo $result['case_title'] ?></p>
                    </div>
                    <div class="d-flex w-100 justify-content-between">
                        <small
                            class="text-wrap text-break">Topic: <?php echo ucwords((string)$result['case_topic']) ?></small>
                        <small><?php echo getCaseStatus($result['case_opened_timestamp'], $result['case_closed_timestamp']); ?></small>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Nothing found.</p>
    <?php endif; ?>
</div>
