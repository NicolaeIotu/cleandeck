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

$count_total_cases = null;
$topics = null;
$count_opened_cases = null;
$count_closed_cases = null;
$count_no_support_cases = null;
$count_owner_ranked_support = null;
$count_support_ranked_owner = null;
$average_support_rank_owner_concise = null;
$average_support_rank_owner_polite = null;
$average_owner_rank_support_pro = null;
$average_owner_rank_support_pleasant = null;
$max_messages_per_case = null;
$avg_messages_per_case = null;
$min_messages_per_case = null;


if (isset($cases_overview) && is_array($cases_overview)) {
    $count_total_cases = $cases_overview['count_total_cases'] ?? '';
    $topics = $cases_overview['topics'] ?? '';
    $count_opened_cases = $cases_overview['count_opened_cases'] ?? '';
    $count_closed_cases = $cases_overview['count_closed_cases'] ?? '';
    $count_no_support_cases = $cases_overview['count_no_support_cases'] ?? '';
    $count_owner_ranked_support = $cases_overview['count_owner_ranked_support'] ?? '';
    $count_support_ranked_owner = $cases_overview['count_support_ranked_owner'] ?? '';
    $average_support_rank_owner_concise = $cases_overview['average_support_rank_owner_concise'] ?? '';
    $average_support_rank_owner_polite = $cases_overview['average_support_rank_owner_polite'] ?? '';
    $average_owner_rank_support_pro = $cases_overview['average_owner_rank_support_pro'] ?? '';
    $average_owner_rank_support_pleasant = $cases_overview['average_owner_rank_support_pleasant'] ?? '';
    $max_messages_per_case = $cases_overview['max_messages_per_case'] ?? '';
    $avg_messages_per_case = $cases_overview['avg_messages_per_case'] ?? '';
    $min_messages_per_case = $cases_overview['min_messages_per_case'] ?? '';
}

?>
<div class="container w-100 w-sm-75 w-md-50 p-0">
    <h1 class="text-end">Support Cases Overview</h1>
    <p class="pb-2">
        <sub class="float-end text-end">
            <a href="<?php echo UrlUtils::baseUrl('support-cases/search'); ?>"
               title="Search Support Cases" target="_self">Search Support Cases</a>
        </sub>
    </p>
    <div class="m-0 p-2 pe-1 border rounded text-break">
        <div class="row p-2 m-0">
            <div class="col-5 border-end fw-bold">Total Support Cases</div>
            <div class="col-7"><?php echo $count_total_cases; ?></div>
        </div>
        <div class="row border-top p-2 pe-0 m-0">
            <div class="col-5 border-end fw-bold">Support Topics</div>
            <div class="col-7 m-0 p-1 overflow-auto max-height-40vh">
                <?php if (is_array($topics)): ?>
                    <div class="list-group m-0 p-0">
                        <?php $i = 1; ?>
                        <?php foreach ($topics as $topic): ?>
                            <form name="form_topic_<?= $i; ?>" method="get"
                                  action="<?php echo UrlUtils::baseUrl('/support-cases/search/results'); ?>">
                                <?php echo view_main('components/csrf'); ?>
                                <input type="hidden" name="topic" value="<?= $topic; ?>">
                                <button class="btn btn-outline-dark m-0 p-2 mb-1 w-100"><?php echo $topic; ?></button>
                            </form>
                            <?php ++$i; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="row border-top p-2 m-0">
            <div class="col-5 border-end fw-bold">Opened Support Cases</div>
            <div class="col-7"><?php echo $count_opened_cases; ?></div>
        </div>
        <div class="row border-top p-2 m-0">
            <div class="col-5 border-end fw-bold">Closed Support Cases</div>
            <div class="col-7"><?php echo $count_closed_cases; ?></div>
        </div>
        <div class="row border-top p-2 m-0">
            <div class="col-5 border-end fw-bold">Unattended Support Cases</div>
            <div class="col-7"><?php echo $count_no_support_cases; ?></div>
        </div>
        <div class="row border-top p-2 m-0">
            <div class="col-5 border-end fw-bold">Total Cases - Support is Ranked</div>
            <div class="col-7"><?php echo $count_owner_ranked_support; ?></div>
        </div>
        <div class="row border-top p-2 m-0">
            <div class="col-5 border-end fw-bold">Total Cases - Owner is Ranked</div>
            <div class="col-7"><?php echo $count_support_ranked_owner; ?></div>
        </div>
        <div class="row border-top p-2 m-0">
            <div class="col-5 border-end fw-bold">Average Rank - Owner Concise</div>
            <div class="col-7"><?php echo $average_support_rank_owner_concise; ?></div>
        </div>
        <div class="row border-top p-2 m-0">
            <div class="col-5 border-end fw-bold">Average Rank - Owner Polite</div>
            <div class="col-7"><?php echo $average_support_rank_owner_polite; ?></div>
        </div>
        <div class="row border-top p-2 m-0">
            <div class="col-5 border-end fw-bold">Average Rank - Support Pro</div>
            <div class="col-7"><?php echo $average_owner_rank_support_pro; ?></div>
        </div>
        <div class="row border-top p-2 m-0">
            <div class="col-5 border-end fw-bold">Average Rank - Support Pleasant</div>
            <div class="col-7"><?php echo $average_owner_rank_support_pleasant; ?></div>
        </div>
        <div class="row border-top p-2 m-0">
            <div class="col-5 border-end fw-bold">Stats - Maximum Messages per Case</div>
            <div class="col-7"><?php echo $max_messages_per_case; ?></div>
        </div>
        <div class="row border-top p-2 m-0">
            <div class="col-5 border-end fw-bold">Stats - Average Messages per Case</div>
            <div class="col-7"><?php echo $avg_messages_per_case; ?></div>
        </div>
        <div class="row border-top p-2 m-0">
            <div class="col-5 border-end fw-bold">Stats - Minimum Messages per Case</div>
            <div class="col-7"><?php echo $min_messages_per_case; ?></div>
        </div>
    </div>
</div>
