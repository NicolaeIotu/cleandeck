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
use Framework\Libraries\Cookie\CookieMessengerReader;
use Framework\Libraries\CSRF\CSRF;
use Framework\Libraries\CSRF\CSRFConstants;
use Framework\Libraries\Utils\Pagination;
use Framework\Libraries\Utils\TimeUtils;
use Framework\Libraries\Utils\UrlUtils;

/*
 * This page is a bit more difficult than the others.
 * Standard form submission is carried out in order to post a message and to close the support case.
 * At the same time AJAX calls are used for rankings.
 * The challenge here is to keep the CSRF hash and the corresponding cookie updated
 * for all the elements on the page at all times.
 * (even in case of AJAX errors) by using both SupportCasesRankController and the scripts on this page.
 */

$cleandeck_account_rank = CleanDeckStatics::getAccountRank();


$case_id = null;
$case_title = null;
$case_topic = null;
$case_status = null;
$case_opened_timestamp = null;
$case_closed_timestamp = null;
$case_references = null;
$case_owner_user_id = null;
$user_id = null;

$has_messages = false;
$valid_case = false;
$case_is_opened = false;
$user_is_case_owner = false;
$ranking_name_1 = null;
$ranking_name_2 = null;
$pagination = [];
if (isset($case_details) && is_array($case_details) &&
    isset($case_details['stats']) && is_array($case_details['stats']) &&
    isset($case_details['stats']['total_messages']) && is_int($case_details['stats']['total_messages']) &&
    isset($case_details['case_entries']) && is_array($case_details['case_entries'])) {
    $valid_case = true;
    if ($case_details['stats']['total_messages'] > 0 && $case_details['case_entries'] !== []) {
        $has_messages = true;
    }

    $pagination_base_url = UrlUtils::url_clean() . '?' . UrlUtils::get_query();
    if (isset($case_details['stats']['page_entries']) && is_int($case_details['stats']['page_entries']) &&
        $case_details['stats']['total_messages'] > $case_details['stats']['page_entries']) {
        $pagination = Pagination::build(
            $case_details['stats']['total_messages'],
            $case_details['stats']['page_number'],
            $case_details['stats']['page_entries'],
            $pagination_base_url
        );
    }


    $case_id = $case_details['stats']['case_id'] ?? null;
    $case_title = $case_details['stats']['case_title'] ?? null;
    $case_topic = $case_details['stats']['case_topic'] ?? null;
    $case_status = $case_details['stats']['case_status'] ?? null;
    $case_opened_timestamp = $case_details['stats']['case_opened_timestamp'] ?? null;
    $case_closed_timestamp = $case_details['stats']['case_closed_timestamp'] ?? null;
    $case_references = $case_details['stats']['case_references'] ?? null;
    $case_owner_user_id = $case_details['stats']['case_owner_user_id'] ?? null;
    $user_id = $case_details['stats']['user_id'] ?? null;

    $case_is_opened = stripos((string)$case_status, "open") === 0;

    if (isset($case_owner_user_id, $user_id)) {
        $user_is_case_owner = $case_details['stats']['case_owner_user_id'] === $case_details['stats']['user_id'];
        $ranking_name_1 = $user_is_case_owner ? 'owner_rank_support_pleasant' : 'support_rank_owner_concise';
        $ranking_name_2 = $user_is_case_owner ? 'owner_rank_support_pro' : 'support_rank_owner_polite';
    }
}

$can_close_case = !$user_is_case_owner && isset($case_id) && $cleandeck_account_rank >= 50 && $case_is_opened;

$cmsg = CleanDeckStatics::getCookieMessage();
$cmsg_form_data = $cmsg['cmsg_form_data'] ?? [];

?>
<div class="container w-100 w-sm-50 p-2">
    <?php if ($valid_case) : ?>
        <h1 class="text-end text-break"><?php echo ucwords($case_title ?? 'Support Case') ?></h1>
        <div class="p-2">
            <?php if (isset($case_topic)): ?>
                <span class="d-table mb-1 badge text-bg-secondary text-start text-wrap text-break">
                    <?php echo 'Topic: ' . ucwords((string)$case_topic) ?></span>
            <?php endif; ?>
            <?php if (isset($case_opened_timestamp)): ?>
                <span class="d-table mb-1 badge text-bg-secondary text-wrap text-break text-start">
                    Opened: <?php echo TimeUtils::timestampToDateString($case_opened_timestamp) ?>
                </span>
            <?php endif; ?>
            <?php if (isset($case_closed_timestamp)) : ?>
                <span class="d-table mb-1 badge text-bg-secondary text-wrap text-break text-start">
                    Closed: <?php echo TimeUtils::timestampToDateString($case_closed_timestamp) ?>
                </span>
            <?php endif; ?>
            <?php if (isset($case_references) && is_string($case_references)) : ?>
                <?php $ref_array = explode('|', $case_references); ?>
                <?php if ($ref_array !== []) : ?>
                    <?php foreach ($ref_array as $value) : ?>
                        <span class="badge text-bg-light border border-secondary text-wrap text-break text-start">
                            <?php echo ucwords($value) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php if ($case_is_opened): ?>
            <div class="d-flex w-100">
                <p class="w-100 m-0 mb-1 mt-2">
                    <?php if ($can_close_case): ?>
                        <span id="close-case"
                              class="badge text-bg-warning text-dark float-start me-2 cursor-pointer m-0 mt-1 p-2">
                            CLOSE Support Case</span>
                    <?php endif; ?>
                    <span id="new-message" data-active="true"
                          class="badge text-bg-dark text-light float-end cursor-pointer m-0 mt-1 p-2">
                        Hide New Message</span>
                </p>
            </div>
        <?php endif; ?>
        <?php if ($case_is_opened && isset($case_id)): ?>
            <div id="nmdiv" class="w-100">
                <form method="post" enctype="application/x-www-form-urlencoded"
                      action="<?= UrlUtils::baseUrl('/support-cases/case/reply/request'); ?>">
                    <?php echo view_main('components/csrf'); ?>
                    <input type="hidden" name="case_id" value="<?= $case_id; ?>">
                    <div class="form-group mb-1">
                    <textarea class="form-control" id="message_content" name="message_content" required
                              minlength="10" maxlength="2000" rows="5" autocomplete="on"
                              aria-required="true"><?= CookieMessengerReader::getPreviousFormData($cmsg_form_data, 'message_content'); ?></textarea>
                    </div>
                    <div class="form-group text-end pb-2 mb-2">
                        <button type="submit" class="btn btn-primary btn-primary-contrast p-1 px-2">Add Message</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        <?php if ($has_messages): ?>
            <?php if ($pagination !== []): ?>
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
            <?php foreach ($case_details['case_entries'] as $case): ?>
                <div class="border rounded mb-2 pt-1 pb-1 ps-2 pe-2 bg-light">
                    <?php $float_pos =
                        $case['message_owner_user_id'] === $case_details['stats']['case_owner_user_id'] ?
                            'float-end' : 'float-start'; ?>
                    <div class="mb-1 <?= $float_pos; ?>">
                        <small class="badge text-bg-success">
                            <?php echo $case['message_owner_username'] ??
                                'User ID: ' . $case_details['stats']['case_owner_user_id'] ?>
                        </small>
                    </div>
                    <div class="d-flex w-100">
                        <p class="mb-0 pb-0 w-100 text-break text-justify indent-1">
                            <?php echo nl2br((string)$case['message_content']) ?>
                        </p>
                    </div>
                    <div>
                        <small><?php echo TimeUtils::timestampToDateString($case['message_timestamp']) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No messages for this support case.</p>
        <?php endif; ?>
        <div class="container clearfix m-0 p-0">
            <?php if (isset($case_id)): ?>
                <?php
                $r1_initial_stars = (isset($case_details['stats'][$ranking_name_1]) &&
                    strlen((string)$case_details['stats'][$ranking_name_1]) > 0) ?
                    $case_details['stats'][$ranking_name_1] : '';
                $r2_initial_stars = (isset($case_details['stats'][$ranking_name_2]) &&
                    strlen((string)$case_details['stats'][$ranking_name_2]) > 0) ?
                    $case_details['stats'][$ranking_name_2] : '';
                $ranking_url = UrlUtils::baseUrl(
                    $user_is_case_owner ? '/support-cases/case/rank-support' : '/support-cases/case/rank-client');
                ?>
                <div id="rankings" class="float-start"
                     data-url="<?= $ranking_url; ?>" data-case-id="<?= $case_id; ?>"
                     data-t-name="<?= CSRFConstants::CSRF_TOKEN ?>" data-t-value="<?= CSRF::get_random_sequence() ?>"
                     data-star-grey="<?php echo UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/icons/star_grey.png'); ?>"
                     data-star-yellow="<?php echo UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/icons/star_yellow.png'); ?>">
                    <div class="col p-0 mt-1">
                        <div id="rank1_container" class="d-inline-block p-1 pt-0 border rounded<?= $case_is_opened ? ' cursor-pointer' : '';?>"
                             data-ranking-name="<?= $ranking_name_1; ?>" data-initial-stars="<?= $r1_initial_stars; ?>">
                            <img width="20" height="20" data-stars="1"
                                 src="<?php echo UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/icons/star_grey.png'); ?>"
                                 alt="Rank 1" title="Rank 1 (Worst)">
                            <img width="20" height="20" data-stars="2"
                                 src="<?php echo UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/icons/star_grey.png'); ?>"
                                 alt="Rank 2" title="Rank 2">
                            <img width="20" height="20" data-stars="3"
                                 src="<?php echo UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/icons/star_grey.png'); ?>"
                                 alt="Rank 3" title="Rank 3">
                            <img width="20" height="20" data-stars="4"
                                 src="<?php echo UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/icons/star_grey.png'); ?>"
                                 alt="Rank 4" title="Rank 4">
                            <img width="20" height="20" data-stars="5"
                                 src="<?php echo UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/icons/star_grey.png'); ?>"
                                 alt="Rank 5" title="Rank 5 (Best)">
                        </div>
                        <?php if ($user_is_case_owner): ?>
                            <small title="Rank Support Operator">Kindness</small>
                        <?php else: ?>
                            <small title="Rank Support Case Owner">Concise</small>
                        <?php endif; ?>
                    </div>
                    <div class="col p-0 mt-1">
                        <div id="rank2_container" class="d-inline-block p-1 pt-0 border rounded<?= $case_is_opened ? ' cursor-pointer' : '';?>"
                             data-ranking-name="<?= $ranking_name_2; ?>" data-initial-stars="<?= $r2_initial_stars; ?>">
                            <img width="20" height="20" data-stars="1"
                                 src="<?php echo UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/icons/star_grey.png'); ?>"
                                 alt="Rank 1" title="Rank 1 (Worst)">
                            <img width="20" height="20" data-stars="2"
                                 src="<?php echo UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/icons/star_grey.png'); ?>"
                                 alt="Rank 2" title="Rank 2">
                            <img width="20" height="20" data-stars="3"
                                 src="<?php echo UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/icons/star_grey.png'); ?>"
                                 alt="Rank 3" title="Rank 3">
                            <img width="20" height="20" data-stars="4"
                                 src="<?php echo UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/icons/star_grey.png'); ?>"
                                 alt="Rank 4" title="Rank 4">
                            <img width="20" height="20" data-stars="5"
                                 src="<?php echo UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/icons/star_grey.png'); ?>"
                                 alt="Rank 5" title="Rank 5 (Best)">
                        </div>
                        <?php if ($user_is_case_owner): ?>
                            <small title="Rank Support Operator">Professionalism</small>
                        <?php else: ?>
                            <small title="Rank Support Case Owner">Polite</small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col d-inline-block p-0 px-2 small mt-2 text-danger">
                    <span id="rank_errors"></span>
                </div>
            <?php endif; ?>
        </div>
        <div class="float-end">
            <small>
                <a href="<?php echo UrlUtils::baseUrl('faqs'); ?>" title="FAQs" target="_self">FAQs</a>
            </small>
        </div>

        <?php if ($can_close_case): ?>
            <div class="modal fade" id="confirm_case_close" tabindex="-1" aria-labelledby="confirm_case_closeLabel"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <span class="modal-title text-larger" id="confirm_case_closeLabel">Close Support Case</span>
                        </div>
                        <div class="modal-body">
                            A support case can be closed usually only after the user was guided and found the solution
                            to the
                            problem raised. Ranking should also be performed by both the user and the support operator
                            before
                            closing a case.
                            <br>
                            Bear in mind that no messages and no rankings can be added to a closed support case.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary btn-primary-contrast" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <form method="post" enctype="application/x-www-form-urlencoded"
                                  action="<?php echo UrlUtils::baseUrl('/support-cases/case/close'); ?>">
                                <?php echo view_main('components/csrf'); ?>
                                <input type="hidden" name="case_id" value="<?= $case_id; ?>">
                                <button type="submit" class="btn btn-warning">
                                    CLOSE Support Case
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?= UrlUtils::script(UrlUtils::baseUrl(CLEANDECK_TEMPLATE_URI . '/main/js/pages/support-case-details.js'),
            ['referrerpolicy' => 'no-referrer', 'crossorigin' => 'anonymous']);?>
    <?php else : ?>
        <div class="alert alert-warning" role="alert">
            Invalid support case.
        </div>
    <?php endif; ?>
</div>
